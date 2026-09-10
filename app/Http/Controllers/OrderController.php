<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use App\Http\Requests\Konsumen\{TambahPesananRequest, CallBellRequest};
use App\Services\OrderService;

class OrderController extends Controller
{
    /**
     * Menampilkan Menu berdasarkan scan QR Meja
     */
    public function showMenu(Request $request, $id_meja = null)
    {
        if (empty($id_meja)) {
            return redirect()->route('pilih_tipe')->with('info', 'Pemesanan Makan di Tempat (Dine-In) hanya dapat dilakukan dengan memindai (scan) stiker QR di atas meja Master Cafe.');
        }

        $meja = Meja::find($id_meja);
        if (!$meja) {
            return redirect()->route('pilih_tipe')->with('error', 'Meja dengan ID #' . $id_meja . ' tidak ditemukan. Silakan pindai ulang stiker QR di atas meja Anda.');
        }
        
        // Cek apakah ada pesanan 'unpaid' aktif di meja ini (Konsep Open Bill)
        $pesananAktif = Pesanan::where('id_meja', $id_meja)
            ->where('status', '!=', 'completed')
            ->whereHas('pembayaran', function($q) {
                $q->where('status', 'unpaid');
            })->first();

        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();

        return view('konsumen.menu', compact('meja', 'menus', 'pesananAktif', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan pilihan tipe pesanan (dine-in vs takeaway).
     */
    public function pilihTipePesanan()
    {
        $mejas = Meja::where('is_active', true)->orderBy('nomor_meja', 'asc')->get();
        return view('konsumen.pilih_tipe_pesanan', compact('mejas'));
    }

    /**
     * Menampilkan daftar meja untuk konsumen sebelum memesan.
     * Dialihkan ke pilih_tipe karena meja hanya dapat dibuka via scan QR fisik di meja.
     */
    public function pilihMeja()
    {
        return redirect()->route('pilih_tipe')->with('info', 'Pemesanan Makan di Tempat (Dine-In) hanya dapat dilakukan dengan memindai (scan) stiker QR di atas meja Master Cafe.');
    }

    /**
     * Menampilkan menu untuk pesanan takeaway.
     */
    public function menuTakeaway()
    {
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_takeaway', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan menu untuk pesanan Dine-In dari jarak jauh (tanpa meja).
     */
    public function menuNanti()
    {
        $menus = Menu::where('is_available', true)->where('stok', '>', 0)->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_nanti', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menambahkan item ke pesanan aktif atau membuat pesanan baru (Open Bill)
     */
    public function tambahPesanan(TambahPesananRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $tipe_pesanan = $validated['tipe_pesanan'] ?? 'dine_in';
            $id_meja = $validated['id_meja'] ?? null;

            if ($tipe_pesanan === 'takeaway') {
                $id_meja = null;
            }

            // Tentukan Nama Tamu / Konsumen
            $mejaModel = $id_meja ? Meja::find($id_meja) : null;
            $guestName = !empty($validated['guest_name']) ? trim($validated['guest_name']) : null;
            if (empty($guestName)) {
                $guestName = auth()->check() ? auth()->user()->name : ($mejaModel ? ('Tamu ' . $mejaModel->nama_meja_atau_nomor) : 'Tamu');
            }

            $orderToken = (string) \Illuminate\Support\Str::uuid();

            // Buat Pesanan & Pembayaran Baru
            $pesanan = Pesanan::create([
                'id_konsumen' => auth()->id(),
                'guest_name' => $guestName,
                'order_token' => $orderToken,
                'id_meja' => $id_meja,
                'tipe_pesanan' => $tipe_pesanan,
                'tanggal' => now(),
                'status' => 'pending',
            ]);

            Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'status' => 'unpaid'
            ]);

            if ($id_meja && $tipe_pesanan === 'dine_in' && $mejaModel) {
                $mejaModel->update(['is_available' => false]);
            }

            // Proses item pesanan via OrderService
            $result = $orderService->processOrderItems($pesanan, $validated['items']);

            $pesanan->total = $result['total'];
            $pesanan->total_hpp = $result['total_hpp'];

            // Handle Promo via OrderService
            $discountAmount = 0;
            if (!empty($validated['promo_id'])) {
                $pesanan->promo_id = $validated['promo_id'];
                $discountAmount = $orderService->calculateDiscount(
                    $result['total'],
                    $validated['promo_id'],
                    $validated['items']
                );
            }
            
            $pesanan->discount_amount = $discountAmount;
            $pesanan->save();

            $pesanan->pembayaran()->update([
                'total_bayar' => $pesanan->total - $pesanan->discount_amount
            ]);

            DB::commit();

            // Simpan token ke session perangkat konsumen
            session(['order_token' => $orderToken, 'active_order_id' => $pesanan->id]);

            // Trigger WebSocket Event (Reverb) - Fail-Safe Non-Blocking
            try {
                broadcast(new \App\Events\PesananBaru($pesanan));

                if ($id_meja && $tipe_pesanan === 'dine_in' && $mejaModel) {
                    broadcast(new \App\Events\MejaStatusUpdated($mejaModel));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[OrderController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            // Trigger Push Notification to Admin and Kasir - Fail-Safe Non-Blocking
            try {
                $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->with('pushSubscriptions')->get();
                if ($adminsAndKasirs->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                        'Pesanan Baru Masuk!',
                        'Order #' . $pesanan->id . ' (' . $guestName . ') baru saja dibuat.',
                        '/kasir/pesanan-aktif'
                    ));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[OrderController] Gagal kirim Web Push: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Pesanan berhasil ditambahkan',
                'id_pesanan' => $pesanan->id,
                'order_token' => $orderToken,
                'guest_name' => $guestName,
                'tracking_url' => url('/tracking/' . $orderToken),
                'checkout_url' => url('/konsumen/checkout/' . $pesanan->id . '?token=' . $orderToken),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Membatalkan pesanan dari sisi konsumen (sebelum dibayar/diproses).
     */
    public function cancelOrder(Request $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();

            $pesanan = Pesanan::with(['pembayaran'])->findOrFail($id_pesanan);

            $token = $request->input('token') ?? $request->header('X-Order-Token') ?? session('order_token');
            $isOwner = false;
            if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
                $isOwner = true;
            } elseif ($pesanan->order_token && $token && $pesanan->order_token === $token) {
                $isOwner = true;
            }

            if (!$isOwner) {
                throw new \Exception('Anda tidak berhak membatalkan pesanan ini.');
            }

            if ($pesanan->status !== 'pending' || ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid')) {
                throw new \Exception('Pesanan sudah diproses atau dibayar, tidak dapat dibatalkan.');
            }

            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil dibatalkan.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Memanggil Pelayan (Call Bell) dari meja.
     */
    public function callBell(CallBellRequest $request)
    {
        $validated = $request->validated();

        $meja = Meja::findOrFail($validated['id_meja']);

        // Mencegah spam (misal: cek apakah ada notifikasi call_bell untuk meja ini dalam 2 menit terakhir)
        $recentCall = \App\Models\Notification::where('type', 'call_bell')
            ->where('id_meja', $meja->id)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($recentCall) {
            return response()->json(['error' => 'Pelayan sudah dipanggil. Mohon tunggu sebentar.'], 429);
        }

        \App\Models\Notification::create([
            'type' => 'call_bell',
            'message' => 'Panggilan Meja ' . $meja->nomor_meja,
            'id_meja' => $meja->id,
            'is_read' => false
        ]);

        // Trigger Push Notification to Admin and Kasir
        $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->with('pushSubscriptions')->get();
        \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
            'Panggilan Meja!',
            'Konsumen di Meja ' . $meja->nomor_meja . ' memanggil pelayan.',
            '/kasir/pos'
        ));

        return response()->json(['message' => 'Pelayan segera datang ke meja Anda.']);
    }

    /**
     * Helper: ambil promo aktif + kumpulkan menu IDs dari promo paket.
     *
     * @return array [$promos, $promoMenuIds]
     */
    private function getActivePromosWithMenuIds(): array
    {
        $promos = Promo::with('menus')->active()->get();

        $promoMenuIds = $promos
            ->where('type', 'package')
            ->flatMap(fn($p) => $p->menus->pluck('id'))
            ->all();

        return [$promos, $promoMenuIds];
    }

    /**
     * Halaman Live Tracking Pesanan Tamu / Konsumen via order_token
     */
    public function tracking(Request $request, $order_token)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'meja', 'rating'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        // Otomatis tandai lunas jika dialihkan dari sukses pembayaran Midtrans atau konfirmasi bayar
        if (($request->query('paid') == '1' || $request->query('auto_settle') == '1') && $pesanan->pembayaran && $pesanan->pembayaran->status !== 'paid') {
            $pesanan->pembayaran->update([
                'status' => 'paid',
                'metode' => $pesanan->pembayaran->metode ?: 'qris',
                'tanggal' => now(),
            ]);
            $pesanan->update(['status' => 'processing']);

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
                if ($pesanan->id_meja && $pesanan->meja) {
                    broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                }
            } catch (\Throwable $e) {
                Log::warning('[OrderController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            $namaKonsumen = $pesanan->guest_name ?: ($pesanan->konsumen?->name ?? 'Tamu');
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => 'Pesanan Lunas QRIS: Order #' . $pesanan->id . ' (' . $namaKonsumen . ') telah lunas.',
                'is_read' => false
            ]);
        }

        $pembayaran = $pesanan->pembayaran;
        $meja = $pesanan->meja;

        return view('konsumen.tracking', compact('pesanan', 'pembayaran', 'meja'));
    }

    /**
     * Unduh / Cetak Struk Digital untuk Tamu
     */
    public function downloadReceipt($order_token)
    {
        $order = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'meja', 'kasir'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        return view('waitress.receipt', compact('order'));
    }

    /**
     * API Status Pesanan Real-Time Polling Fallback (JSON)
     */
    public function getOrderStatus($order_token)
    {
        $pesanan = Pesanan::with(['pembayaran'])
            ->where('order_token', $order_token)
            ->firstOrFail();

        return response()->json([
            'id' => $pesanan->id,
            'status' => $pesanan->status,
            'payment_status' => $pesanan->pembayaran?->status ?? 'unpaid',
            'payment_method' => $pesanan->pembayaran?->metode ?? '-',
            'total' => $pesanan->total,
            'updated_at' => $pesanan->updated_at->toIso8601String(),
        ]);
    }
}

