<?php

namespace App\Services;

use App\Models\{Menu, Pesanan, DetailPesanan, Promo};

class OrderService
{
    /**
     * Proses item pesanan: lock menu, validasi stok menu, kurangi stok menu, buat detail pesanan.
     *
     * @param  Pesanan  $pesanan
     * @param  array    $items  Array of ['id_menu', 'jumlah', 'catatan'?, 'variants'?]
     * @return array    ['total' => int, 'total_hpp' => int]
     *
     * @throws \Exception  Jika stok tidak mencukupi atau menu tidak tersedia.
     */
    public function processOrderItems(Pesanan $pesanan, array $items): array
    {
        // 1. Kumpulkan semua menu IDs, sort ascending untuk konsistensi lock order
        $menuIds = collect($items)->pluck('id_menu')->unique()->sort()->values()->all();

        // 2. Lock & load semua menu sekaligus beserta resep bahannya (1 query, bukan N query)
        $menus = Menu::with('bahans')
            ->whereIn('id', $menuIds)
            ->where('is_available', true)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($menuIds as $menuId) {
            if (!$menus->has($menuId)) {
                throw new \Exception("Gagal: Menu ID {$menuId} tidak tersedia.");
            }
        }

        // 3. Proses setiap item
        $totalHarga = 0;
        $totalHpp = 0;

        foreach ($items as $item) {
            $menu = $menus->get($item['id_menu']);

            // 3a. Validasi ketersediaan menu (Tersedia vs Habis)
            if (!$menu->is_available) {
                throw new \Exception("Gagal: Menu {$menu->nama_menu} saat ini sedang habis.");
            }

            // 3b. Hitung harga varian
            [$hargaVarian, $selectedVariants] = $this->resolveVariants($menu, $item['variants'] ?? []);

            // 3c. Hitung base price (dukung menu timbangan / dynamic price)
            $basePrice = ($menu->is_dynamic_price && isset($item['harga']) && $item['harga'] > 0)
                ? (float) $item['harga']
                : (float) $menu->harga;

            $hargaTotalPerItem = $basePrice + $hargaVarian;
            $subtotal = $hargaTotalPerItem * $item['jumlah'];
            $totalHarga += $subtotal;

            // 3d. Hitung HPP (Harga Pokok Penjualan) berdasarkan bahan baku resep
            $hppPerItem = 0;
            if ($menu->bahans && $menu->bahans->isNotEmpty()) {
                foreach ($menu->bahans as $bahan) {
                    $jumlahDibutuhkan = (float) ($bahan->pivot->jumlah_dibutuhkan ?? 0);
                    $hargaBeli = (float) ($bahan->harga_beli ?? 0);
                    $hppPerItem += ($jumlahDibutuhkan * $hargaBeli);
                }
            }
            $totalHpp += ($hppPerItem * $item['jumlah']);

            // 3e. Kurangi stok menu & bahan baku
            $menu->decrement('stok', $item['jumlah']);
            if ($menu->bahans && $menu->bahans->isNotEmpty()) {
                foreach ($menu->bahans as $bahan) {
                    $jumlahDibutuhkan = (float) ($bahan->pivot->jumlah_dibutuhkan ?? 0);
                    if ($jumlahDibutuhkan > 0) {
                        $bahan->decrement('stok', $jumlahDibutuhkan * $item['jumlah']);
                    }
                }
            }

            // 3f. Buat detail pesanan
            DetailPesanan::create([
                'id_pesanan' => $pesanan->id,
                'id_menu' => $menu->id,
                'jumlah' => $item['jumlah'],
                'subtotal' => $subtotal,
                'catatan' => $item['catatan'] ?? null,
                'selected_variants' => !empty($selectedVariants) ? json_encode($selectedVariants) : null,
            ]);
        }

        return ['total' => $totalHarga, 'total_hpp' => $totalHpp];
    }

    /**
     * Resolve selected variants terhadap variants_json menu (validasi harga dari backend).
     *
     * @return array [int $hargaVarian, array $selectedVariants]
     */
    private function resolveVariants(Menu $menu, array $clientVariants): array
    {
        $hargaVarian = 0;
        $selectedVariants = [];

        if (empty($clientVariants) || !$menu->variants_json) {
            return [$hargaVarian, $selectedVariants];
        }

        $menuVariants = json_decode($menu->variants_json, true);
        if (!is_array($menuVariants)) {
            return [$hargaVarian, $selectedVariants];
        }

        foreach ($clientVariants as $selVar) {
            foreach ($menuVariants as $group) {
                if (!isset($selVar['group']) || $group['group_name'] !== $selVar['group']) {
                    continue;
                }
                foreach ($group['options'] as $opt) {
                    if (!isset($selVar['name']) || $opt['name'] !== $selVar['name']) {
                        continue;
                    }

                    $qty = max(1, (int) ($selVar['qty'] ?? 1));
                    $hargaVarian += ($opt['price'] * $qty);
                    $selectedVariants[] = [
                        'group' => $group['group_name'],
                        'name' => $opt['name'],
                        'price' => $opt['price'],
                        'qty' => $qty,
                    ];
                    break 2;
                }
            }
        }

        return [$hargaVarian, $selectedVariants];
    }

    /**
     * Hitung diskon berdasarkan promo.
     *
     * @param  int       $totalHarga  Total harga sebelum diskon
     * @param  int|null  $promoId     ID promo (nullable)
     * @param  array     $items       Items dari request (untuk validasi paket)
     * @return int       Jumlah diskon
     *
     * @throws \Exception  Jika promo tidak berlaku hari ini atau syarat paket tidak terpenuhi.
     */
    public function calculateDiscount(int $totalHarga, ?int $promoId, array $items): int
    {
        if (!$promoId) {
            return 0;
        }

        $promo = Promo::with('menus')->find($promoId);
        if (!$promo || !$promo->is_active) {
            return 0;
        }

        $this->validatePromoDays($promo);

        return match ($promo->type) {
            'discount' => $this->calcDiscountType($promo, $totalHarga),
            'package' => $this->calcPackageType($promo, $totalHarga, $items),
            default => 0,
        };
    }

    /**
     * Validasi apakah promo berlaku untuk hari ini.
     */
    private function validatePromoDays(Promo $promo): void
    {
        $promoDays = is_string($promo->days) ? json_decode($promo->days, true) : $promo->days;

        if (!is_array($promoDays) || count($promoDays) === 0) {
            return;
        }

        $todayName = now()->format('l');
        if (!in_array($todayName, $promoDays)) {
            throw new \Exception("Promo '{$promo->title}' tidak berlaku untuk hari ini (" . now()->translatedFormat('l') . ").");
        }
    }

    /**
     * Hitung diskon tipe 'discount' (persentase / nominal).
     */
    private function calcDiscountType(Promo $promo, int $totalHarga): int
    {
        if ($promo->discount_type === 'percentage') {
            $discount = $totalHarga * ($promo->value / 100);
        } else {
            $discount = $promo->value;
        }

        return (int) min($discount, $totalHarga);
    }

    /**
     * Hitung diskon tipe 'package' (paket menu).
     */
    private function calcPackageType(Promo $promo, int $totalHarga, array $items): int
    {
        $packageItems = $promo->menus;
        $packageNormalPrice = 0;

        $cartMap = [];
        foreach ($items as $item) {
            if (!isset($cartMap[$item['id_menu']])) {
                $cartMap[$item['id_menu']] = 0;
            }
            $cartMap[$item['id_menu']] += $item['jumlah'];
        }

        // Hitung berapa kali paket bisa dipenuhi
        $maxPackageCount = PHP_INT_MAX;
        foreach ($packageItems as $pkgMenu) {
            $requiredQty = $pkgMenu->pivot->jumlah;
            $availableQty = $cartMap[$pkgMenu->id] ?? 0;

            if ($availableQty < $requiredQty) {
                $maxPackageCount = 0;
                break;
            }
            $maxPackageCount = min($maxPackageCount, intdiv($availableQty, $requiredQty));
            $packageNormalPrice += ($pkgMenu->harga * $requiredQty);
        }

        if ($maxPackageCount === 0 || $maxPackageCount === PHP_INT_MAX) {
            throw new \Exception("Pesanan tidak memenuhi syarat menu untuk Promo Paket '{$promo->title}'.");
        }

        $discountPerPackage = max(0, $packageNormalPrice - $promo->value);

        return $discountPerPackage * $maxPackageCount;
    }

    /**
     * Memproses pesanan manual lengkap dari Kasir (Pesanan, Detail, Pembayaran & Snap Token).
     *
     * @param  array  $validatedData
     * @param  int|null  $kasirId
     * @param  PaymentService|null  $paymentService
     * @return array  ['pesanan' => Pesanan, 'snap_data' => ?array]
     *
     * @throws \Exception
     */
    public function createManualOrder(array $validatedData, ?int $kasirId = null, ?PaymentService $paymentService = null): array
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($validatedData, $kasirId, $paymentService) {
            $idMeja = ($validatedData['tipe_pesanan'] === 'takeaway') ? null : ($validatedData['id_meja'] ?? null);

            // Selesaikan sesi meja lama yang sudah berstatus lunas
            if (!empty($idMeja) && $validatedData['tipe_pesanan'] === 'dine_in') {
                $oldPaidOrders = Pesanan::where('id_meja', $idMeja)
                    ->whereIn('status', ['pending', 'processing'])
                    ->whereHas('pembayaran', function ($p) {
                        $p->where('status', 'paid');
                    })
                    ->get();

                foreach ($oldPaidOrders as $oldOrd) {
                    $oldOrd->update([
                        'status' => 'completed',
                        'id_kasir' => $kasirId
                    ]);
                }
            }

            // Buat record Pesanan baru
            $pesanan = Pesanan::create([
                'id_konsumen' => null,
                'id_meja' => $idMeja,
                'id_kasir' => $kasirId,
                'tipe_pesanan' => $validatedData['tipe_pesanan'],
                'tanggal' => now(),
                'status' => 'pending',
                'promo_id' => $validatedData['promo_id'] ?? null
            ]);

            // Kunci ketersediaan meja untuk dine-in
            if (!empty($idMeja) && $validatedData['tipe_pesanan'] === 'dine_in') {
                \App\Models\Meja::where('id', $idMeja)->update(['is_available' => false]);
            }

            // Proses item menu & bahan baku
            $result = $this->processOrderItems($pesanan, $validatedData['items']);
            $totalSemua = $result['total'];
            $totalHpp = $result['total_hpp'];

            // Kalkulasi diskon promo
            $discountAmount = $this->calculateDiscount(
                $totalSemua,
                $validatedData['promo_id'] ?? null,
                $validatedData['items']
            );

            $totalBayar = $totalSemua - $discountAmount;

            $pesanan->update([
                'total' => $totalSemua,
                'discount_amount' => $discountAmount,
                'total_hpp' => $totalHpp
            ]);

            // Status dan metode pembayaran
            $isPaidDirectly = !empty($validatedData['pembayaran_langsung']);
            $statusBayar = $isPaidDirectly ? 'paid' : 'unpaid';
            $metodeBayar = !empty($validatedData['metode_pembayaran']) 
                ? $validatedData['metode_pembayaran'] 
                : ($isPaidDirectly ? 'cash' : null);

            $uangDiterima = null;
            $uangKembalian = 0;
            $catatanKembalian = null;

            if ($isPaidDirectly && $metodeBayar === 'cash') {
                $isUangPas = !empty($validatedData['is_uang_pas']);
                $nominalTunaiInput = $validatedData['nominal_tunai'] ?? null;

                if ($isUangPas) {
                    $uangDiterima = $totalBayar;
                    $uangKembalian = 0;
                    $catatanKembalian = 'Uang Pas (Tanpa Kembalian)';
                } elseif (!empty($nominalTunaiInput) && is_numeric($nominalTunaiInput)) {
                    $uangDiterima = (float) $nominalTunaiInput;
                    $uangKembalian = max(0, $uangDiterima - $totalBayar);
                    if ($uangKembalian > 0) {
                        $catatanKembalian = 'Uang Rp ' . number_format($uangDiterima, 0, ',', '.') . ' (Kembalian Rp ' . number_format($uangKembalian, 0, ',', '.') . ')';
                    } else {
                        $catatanKembalian = 'Uang Pas (Tanpa Kembalian)';
                    }
                }
            }

            \App\Models\Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'metode' => $metodeBayar,
                'status' => $statusBayar,
                'total_bayar' => $totalBayar,
                'tanggal' => $isPaidDirectly ? now() : null,
                'uang_diterima' => $uangDiterima,
                'uang_kembalian' => $uangKembalian,
                'catatan_kembalian' => $catatanKembalian,
            ]);

            // Generate Snap token jika QRIS bayar nanti
            $snapData = null;
            if ($metodeBayar === 'qris' && !$isPaidDirectly && $paymentService) {
                try {
                    $snapData = $paymentService->createSnapToken($pesanan);
                } catch (\Throwable $snapErr) {
                    \Illuminate\Support\Facades\Log::warning('[OrderService] Gagal generate Snap token manual order: ' . $snapErr->getMessage());
                }
            }

            return [
                'pesanan' => $pesanan,
                'snap_data' => $snapData,
            ];
        });
    }
}

