<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Bahan;
use App\Models\VoidLog;
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use App\Models\{Pesanan, DetailPesanan, Pembayaran, Menu, Meja, Promo, Setting};
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\PrintService;
use App\Services\PosService;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Pos\{StoreManualOrderRequest, VoidOrderRequest, SplitOrderRequest, UpdateOrderStatusRequest, PayOrderRequest};

class PosController extends Controller
{
    public function __construct(
        protected PosService $posService,
        protected OrderService $orderService,
        protected PaymentService $paymentService,
        protected PrintService $printService
    ) {}

    /**
     * Menampilkan Halaman POS untuk Kasir
     */
    public function index()
    {
        Meja::syncAllAvailability();
        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        $mejas = Meja::orderBy('id')->get();
        $promos = Promo::active()->get();

        return view('waitress.pos', compact('menus', 'mejas', 'promos'));
    }

    /**
     * Menampilkan Halaman Pesanan Aktif Konsumen (Strict Pay-First Policy untuk Takeaway)
     */
    public function pesananAktif(Request $request)
    {
        // 1. Proaktif cek status Midtrans untuk semua pesanan pending/unpaid sebelum memfilter
        $pendingCheckOrders = Pesanan::with(['pembayaran'])
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotIn('status', ['cancelled', 'void'])
            ->get();

        foreach ($pendingCheckOrders as $order) {
            if ($order->pembayaran && $order->pembayaran->status !== 'paid') {
                $this->posService->checkAndUpdateMidtransStatus($order);
            }
        }

        // 2. Ambil pesanan aktif via PosService:
        // - Termasuk pesanan pending, processing, dan pesanan selesai dimasak namun belum lunas
        $orders = $this->posService->getActiveOrdersQuery()
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedOrders = $this->groupActiveOrders($orders);

        // 3. Ambil pesanan selesai terbaru (History pesanan selesai untuk Tablet Waitress / Kasir)
        // Hanya pesanan yang sudah selesai dimasak DAN lunas yang masuk ke history selesai
        $completedOrders = $this->posService->getCompletedOrdersQuery(50)->get();

        $groupedCompletedOrders = $this->groupCompletedOrders($completedOrders);

        // 4. Ambil riwayat pesanan yang dibatalkan / dihapus (Void / Cancelled)
        $voidedOrders = Pesanan::withTrashed()
            ->where(function ($q) {
                $q->whereNotNull('deleted_at')
                  ->orWhereIn('status', ['cancelled', 'void']);
            })
            ->with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen', 'kasir', 'voidLog.kasir'])
            ->orderByRaw('COALESCE(deleted_at, updated_at) DESC')
            ->take(50)
            ->get();

        if ($request->query('history_only')) {
            return view('components.waitress.completed-order-card', compact('completedOrders', 'groupedCompletedOrders'))->render();
        }

        if ($request->query('voided_only')) {
            return view('components.waitress.voided-order-card', compact('voidedOrders'))->render();
        }

        if ($request->ajax() || $request->wantsJson() || $request->query('cards_only')) {
            return view('components.waitress.active-order-card', compact('groupedOrders', 'orders'))->render();
        }

        return view('waitress.pesanan_aktif', compact('groupedOrders', 'orders', 'completedOrders', 'groupedCompletedOrders', 'voidedOrders'));
    }

    /**
     * Mengelompokkan pesanan aktif per Meja (Dine-In) atau per Tamu (Takeaway)
     * Delegasi ke PosService untuk arsitektur yang bersih.
     */
    public function groupActiveOrders($orders)
    {
        return $this->posService->groupActiveOrders(
            $orders instanceof \Illuminate\Support\Collection ? $orders : collect($orders)
        );
    }

    /**
     * Mengelompokkan riwayat pesanan selesai per Sesi Meja / Tamu
     */
    public function groupCompletedOrders($orders)
    {
        return $this->posService->groupCompletedOrders(
            $orders instanceof \Illuminate\Support\Collection ? $orders : collect($orders)
        );
    }

    /**
     * Mengambil jumlah pesanan aktif untuk badge notifikasi
     */
    public function activeOrdersCount()
    {
        return response()->json($this->posService->getActiveOrdersCountData());
    }

    /**
     * Memproses pesanan manual dari Kasir
     */
    public function storeManualOrder(StoreManualOrderRequest $request)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1b. Jika meja ini masih memiliki pesanan lama yang SUDAH LUNAS (paid),
            // otomatis selesaikan pesanan lama tersebut agar sesi lama tertutup rapi dan tidak tercampur dengan tamu baru ini.
            if (!empty($idMeja) && $validated['tipe_pesanan'] === 'dine_in') {
                $oldPaidOrders = Pesanan::where('id_meja', $idMeja)
                    ->whereIn('status', ['pending', 'processing'])
                    ->whereHas('pembayaran', function ($p) {
                        $p->where('status', 'paid');
                    })
                    ->get();

                foreach ($oldPaidOrders as $oldOrd) {
                    $oldOrd->update([
                        'status' => 'completed',
                        'id_kasir' => auth()->id()
                    ]);
                }
            }

            // 2. Buat Data Pesanan Baru
            $idMeja = ($validated['tipe_pesanan'] === 'takeaway') ? null : ($validated['id_meja'] ?? null);
            $pesanan = Pesanan::create([
                'id_konsumen' => null,
                'id_meja' => $idMeja,
                'id_kasir' => auth()->id(),
                'tipe_pesanan' => $validated['tipe_pesanan'],
                'tanggal' => now(),
                'status' => 'pending',
                'promo_id' => $validated['promo_id'] ?? null
            ]);

            // Otomatis matikan ketersediaan meja jika ini pesanan dine-in
            if (!empty($idMeja) && $validated['tipe_pesanan'] === 'dine_in') {
                Meja::where('id', $idMeja)->update(['is_available' => false]);
            }

            // 3. Proses item pesanan via OrderService (lock stok, kurangi bahan, buat detail)
            $result = $this->orderService->processOrderItems($pesanan, $validated['items']);
            $totalSemua = $result['total'];
            $total_hpp = $result['total_hpp'];

            // 4. Hitung diskon via OrderService
            $discountAmount = $this->orderService->calculateDiscount(
                $totalSemua,
                $validated['promo_id'] ?? null,
                $validated['items']
            );

            $totalBayar = $totalSemua - $discountAmount;

            // Update total harga dan diskon di tabel pesanan
            $pesanan->update([
                'total' => $totalSemua,
                'discount_amount' => $discountAmount,
                'total_hpp' => $total_hpp
            ]);

            // 5. Proses Status Pembayaran
            $statusBayar = $validated['pembayaran_langsung'] ? 'paid' : 'unpaid';
            $metodeBayar = !empty($validated['metode_pembayaran']) ? $validated['metode_pembayaran'] : ($validated['pembayaran_langsung'] ? 'cash' : null);

            $uangDiterima = null;
            $uangKembalian = 0;
            $catatanKembalian = null;

            if ($validated['pembayaran_langsung'] && $metodeBayar === 'cash') {
                $isUangPas = !empty($validated['is_uang_pas']);
                $nominalTunaiInput = $validated['nominal_tunai'] ?? null;

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

            Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'metode' => $metodeBayar,
                'status' => $statusBayar,
                'total_bayar' => $totalBayar,
                'tanggal' => $validated['pembayaran_langsung'] ? now() : null,
                'uang_diterima' => $uangDiterima,
                'uang_kembalian' => $uangKembalian,
                'catatan_kembalian' => $catatanKembalian,
            ]);

            $snapData = null;
            if ($metodeBayar === 'qris' && !$validated['pembayaran_langsung']) {
                try {
                    $snapData = $this->paymentService->createSnapToken($pesanan);
                } catch (\Throwable $snapErr) {
                    \Illuminate\Support\Facades\Log::warning('[PosController] Gagal generate Snap token manual order: ' . $snapErr->getMessage());
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Pesanan manual berhasil diproses.',
                'id_pesanan' => $pesanan->id,
                'snap_token' => $snapData['snap_token'] ?? null,
                'client_key' => $snapData['client_key'] ?? null,
                'is_production' => $snapData['is_production'] ?? false,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Dapatkan Snap Token Midtrans untuk pesanan aktif kasir
     */
    public function getKasirSnapToken($id)
    {
        try {
            $pesanan = Pesanan::findOrFail($id);
            $snapData = $this->paymentService->createSnapToken($pesanan);
            return response()->json($snapData);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Konfirmasi pelunasan QRIS Midtrans dari kasir
     */
    public function markQrisPaid(Request $request, $id)
    {
        try {
            $pesanan = $this->paymentService->processPayment(
                $id,
                'qris',
                $request->input('email_pelanggan'),
                auth()->id()
            );

            if ($pesanan->status === 'pending') {
                $pesanan->update(['status' => 'processing']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran QRIS Midtrans berhasil diverifikasi (LUNAS).',
                'id_pesanan' => $pesanan->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pesanan dari kasir
     */
    public function updateOrderStatus(UpdateOrderStatusRequest $request, $id_pesanan)
    {
        try {
            $ids = $request->input('order_ids');
            if (empty($ids)) {
                if (is_string($id_pesanan) && str_contains($id_pesanan, ',')) {
                    $ids = array_filter(array_map('trim', explode(',', $id_pesanan)));
                } else {
                    $ids = [$id_pesanan];
                }
            }

            $orders = Pesanan::with(['konsumen', 'meja', 'detail_pesanan.menu'])->whereIn('id', $ids)->get();
            if ($orders->isEmpty()) {
                return response()->json(['error' => 'Pesanan tidak ditemukan.'], 404);
            }

            $targetStatus = $request->validated('status');

            foreach ($orders as $pesanan) {
                // Update status
                $pesanan->update([
                    'status' => $targetStatus,
                    'id_kasir' => auth()->id() // Kasir yang memproses pesanan
                ]);

                // Jika pesanan selesai dan meja tidak lagi memiliki pesanan aktif / belum lunas,
                // set meja menjadi tersedia kembali (is_available = true)
                if ($targetStatus === 'completed' && $pesanan->id_meja) {
                    $hasRemaining = Pesanan::where('id_meja', $pesanan->id_meja)
                        ->whereNotIn('status', ['cancelled', 'void'])
                        ->where(function ($q) {
                            $q->whereIn('status', ['pending', 'processing'])
                              ->orWhere(function ($sub) {
                                  $sub->where('status', 'completed')
                                      ->where(function ($pSub) {
                                          $pSub->whereDoesntHave('pembayaran')
                                               ->orWhereHas('pembayaran', function ($p) {
                                                   $p->where('status', '!=', 'paid');
                                               });
                                      });
                              });
                        })
                        ->exists();

                    if (!$hasRemaining && $pesanan->meja && !$pesanan->meja->is_available) {
                        $pesanan->meja->update(['is_available' => true]);
                    }
                }

                // Broadcast status update ke listener real-time (WebSocket / Polling)
                try {
                    broadcast(new \App\Events\PesananBaru($pesanan));
                    if ($pesanan->id_meja && $pesanan->meja) {
                        broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[PosController] Gagal broadcast WebSocket status update: ' . $e->getMessage());
                }

                // Notify Customer via Web Push (non-blocking)
                if ($pesanan->id_konsumen && $pesanan->konsumen) {
                    try {
                        $statusText = $pesanan->status === 'completed' ? 'Selesai' : 'Diproses';
                        $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                            'Pesanan ' . $statusText,
                            'Pesanan Anda (Order #' . $pesanan->id . ') saat ini ' . strtolower($statusText) . '.',
                            '/konsumen/profil'
                        ));
                    } catch (\Throwable $notifyErr) {
                        \Illuminate\Support\Facades\Log::warning('WebPush gagal dikirim: ' . $notifyErr->getMessage());
                    }
                }

                // Kirim Notifikasi WhatsApp Otomatis jika Pesanan Takeaway Selesai
                if ($pesanan->status === 'completed' && $pesanan->tipe_pesanan === 'takeaway' && !empty($pesanan->guest_phone)) {
                    try {
                        $pesanan->loadMissing(['detail_pesanan.menu']);
                        $waMessage = \App\Services\WhatsAppService::formatTakeawayReadyMessage($pesanan);
                        \App\Services\WhatsAppService::sendMessage($pesanan->guest_phone, $waMessage);
                    } catch (\Throwable $waErr) {
                        \Illuminate\Support\Facades\Log::warning('[PosController] Gagal kirim notifikasi WA Takeaway: ' . $waErr->getMessage());
                    }
                }
            }

            return response()->json([
                'message' => 'Status pesanan berhasil diupdate',
                'status' => $targetStatus,
                'order_ids' => $ids
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update status pembayaran dari kasir
     */
    
    public function verifyPayment($id_pesanan, PaymentService $paymentService)
    {
        try {
            DB::beginTransaction();
            
            $pesanan = $paymentService->processPayment(
                $id_pesanan,
                'qris', // Default to QRIS since it was uploaded
                null,
                auth()->id()
            );

            // Set status pesanan menjadi processing (dimasak) jika dine_in dan belum processing
            if ($pesanan->status === 'pending') {
                $pesanan->status = 'processing';
                $pesanan->save();
            }

            DB::commit();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pembayaran pesanan #'.$pesanan->id.' berhasil diverifikasi.'
                ]);
            }
            return redirect()->back()->with('success', 'Pembayaran pesanan #'.$pesanan->id.' berhasil diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal verifikasi pembayaran: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Gagal memverifikasi pembayaran.'], 422);
            }
            return redirect()->back()->with('error', 'Gagal memverifikasi pembayaran.');
        }
    }

    public function rejectPayment($id_pesanan)
    {
        try {
            $pesanan = Pesanan::with('pembayaran')->findOrFail($id_pesanan);
            if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'pending_verification') {
                $pesanan->pembayaran->status = 'unpaid';
                $pesanan->pembayaran->bukti_bayar = null;
                $pesanan->pembayaran->save();
                if (request()->ajax() || request()->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Bukti pembayaran ditolak. Pesanan dikembalikan ke status belum bayar.'
                    ]);
                }
                return redirect()->back()->with('success', 'Bukti pembayaran ditolak. Pesanan dikembalikan ke status belum bayar.');
            }
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Pesanan tidak dalam status verifikasi.'], 422);
            }
            return redirect()->back()->with('error', 'Pesanan tidak dalam status verifikasi.');
        } catch (\Exception $e) {
            Log::error('Gagal tolak pembayaran: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['error' => 'Gagal menolak pembayaran.'], 422);
            }
            return redirect()->back()->with('error', 'Gagal menolak pembayaran.');
        }
    }

    public function payOrder(PayOrderRequest $request, $id_pesanan)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();
            
            $ids = $request->input('order_ids');
            if (empty($ids)) {
                if (is_string($id_pesanan) && str_contains($id_pesanan, ',')) {
                    $ids = array_filter(array_map('trim', explode(',', $id_pesanan)));
                } else {
                    $ids = [$id_pesanan];
                }
            }

            $unpaidList = [];
            $lastPesanan = null;
            foreach ($ids as $singleId) {
                $pesanan = Pesanan::with('pembayaran')->find($singleId);
                if (!$pesanan) continue;
                if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid') {
                    $lastPesanan = $pesanan;
                    continue;
                }
                $unpaidList[] = $pesanan;
            }

            if (empty($unpaidList)) {
                DB::commit();
                return response()->json([
                    'message' => 'Semua pesanan yang dipilih sudah lunas.',
                    'id_pesanan' => $lastPesanan ? $lastPesanan->id : $id_pesanan
                ]);
            }

            $metode = $validated['metode'] ?? 'cash';
            $isUangPas = !empty($validated['is_uang_pas']);
            $nominalTunai = isset($validated['nominal_tunai']) && is_numeric($validated['nominal_tunai'])
                ? (float) $validated['nominal_tunai']
                : null;

            if (count($unpaidList) === 1) {
                $lastPesanan = $this->paymentService->processPayment(
                    $unpaidList[0]->id,
                    $metode,
                    $validated['email_pelanggan'] ?? null,
                    auth()->id(),
                    $nominalTunai,
                    $isUangPas
                );
            } else {
                // Multi-order: Hitung total tagihan seluruh pesanan yang belum lunas
                $totalGroupTagihan = 0;
                $bills = [];
                foreach ($unpaidList as $p) {
                    $bill = (float) (($p->pembayaran && (float)$p->pembayaran->total_bayar > 0)
                        ? $p->pembayaran->total_bayar
                        : ($p->total - ($p->discount_amount ?? 0)));
                    $bills[$p->id] = $bill;
                    $totalGroupTagihan += $bill;
                }

                if ($metode === 'cash' && !$isUangPas && $nominalTunai !== null) {
                    if ($nominalTunai < $totalGroupTagihan) {
                        throw new \Exception('Nominal uang tunai yang diterima (Rp ' . number_format($nominalTunai, 0, ',', '.') . ') kurang dari total tagihan gabungan (Rp ' . number_format($totalGroupTagihan, 0, ',', '.') . ').');
                    }
                    $totalKembalian = max(0, $nominalTunai - $totalGroupTagihan);

                    // Distribusi uang tunai dan kembalian secara konsisten:
                    // Pesanan ke-2 dan seterusnya dicatat lunas dengan uang pas (uang_diterima = total_bayar, kembalian = 0)
                    // Pesanan pertama menampung sisa uang tunai dan seluruh uang kembalian gabungan
                    // Sehingga total uang diterima = nominalTunai dan total kembalian = totalKembalian
                    $subordersTotal = 0;
                    for ($i = 1; $i < count($unpaidList); $i++) {
                        $subordersTotal += $bills[$unpaidList[$i]->id];
                    }
                    $firstOrderCash = $nominalTunai - $subordersTotal;

                    // Bayar pesanan pertama
                    $lastPesanan = $this->paymentService->processPayment(
                        $unpaidList[0]->id,
                        'cash',
                        $validated['email_pelanggan'] ?? null,
                        auth()->id(),
                        $firstOrderCash,
                        false
                    );

                    // Bayar pesanan lainnya sebagai uang pas
                    for ($i = 1; $i < count($unpaidList); $i++) {
                        $this->paymentService->processPayment(
                            $unpaidList[$i]->id,
                            $metode,
                            $validated['email_pelanggan'] ?? null,
                            auth()->id(),
                            $bills[$unpaidList[$i]->id],
                            true
                        );
                    }
                } else {
                    // Non-tunai atau Uang Pas: setiap pesanan dibayar sesuai tagihannya masing-masing
                    foreach ($unpaidList as $p) {
                        $lastPesanan = $this->paymentService->processPayment(
                            $p->id,
                            $metode,
                            $validated['email_pelanggan'] ?? null,
                            auth()->id(),
                            $bills[$p->id] ?? null,
                            true
                        );
                    }
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'id_pesanan' => $lastPesanan ? $lastPesanan->id : $id_pesanan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk pesanan (Thermal 58mm / HTML View)
     */
    public function printReceipt($id)
    {
        $order = $this->printService->prepareReceiptOrder($id);
        return view('waitress.receipt', compact('order'));
    }

    /**
     * Cetak struk langsung ke Printer Thermal (Raw ESC/POS Network)
     */
    public function printThermalReceipt($id)
    {
        try {
            $this->printService->printThermalReceipt($id);
            return response()->json(['message' => 'Struk berhasil dikirim ke printer thermal.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Membatalkan pesanan dari kasir (Void).
     */
    public function voidOrder(VoidOrderRequest $request, $id_pesanan)
    {
        try {
            DB::beginTransaction();
            $pesanan = Pesanan::findOrFail($id_pesanan);

            if (!Hash::check($request->input('password'), auth()->user()->password)) {
                throw new \Exception('Password yang dimasukkan salah.');
            }

            // Pesanan yang SUDAH LUNAS tidak dapat sembarangan divoid oleh kasir
            if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid') {
                throw new \Exception('Pesanan sudah dibayar lunas dan tidak dapat dihapus/divoid.');
            }

            if ($pesanan->status === 'completed') {
                throw new \Exception('Pesanan sudah selesai dan tidak dapat divoid.');
            }

            if ($pesanan->status === 'cancelled') {
                throw new \Exception('Pesanan sudah dibatalkan sebelumnya.');
            }

            // Simpan log void
            DB::table('void_logs')->insert([
                'pesanan_id' => $pesanan->id,
                'kasir_id' => auth()->id(),
                'alasan' => $request->input('alasan') ?? 'Batal',
                'total_nilai' => $pesanan->total,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Batalkan pesanan dan restore stok
            $pesanan->cancelOrder();

            DB::commit();
            return response()->json(['message' => 'Pesanan berhasil divoid.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk dapur (tanpa harga).
     */
    public function printKitchenReceipt($id)
    {
        if (request()->ajax() || request()->wantsJson()) {
            try {
                $this->printService->printKitchenReceipt($id);
                return response()->json(['message' => 'Tiket dapur berhasil dikirim ke printer thermal.']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $order = $this->printService->prepareKitchenReceiptOrder($id);
        return view('waitress.kitchen_receipt', compact('order'));
    }

    /**
     * Menampilkan laporan tutup shift kasir (Delegasi ke ShiftController)
     */
    public function shiftReport(ShiftController $shiftController)
    {
        return $shiftController->shiftReport();
    }

    /**
     * Ekspor Laporan Tutup Shift Kasir ke PDF (Delegasi ke ShiftController)
     */
    public function exportShiftReportPdf(ShiftController $shiftController)
    {
        return $shiftController->exportShiftReportPdf();
    }

    /**
     * Ekspor Laporan Tutup Shift Kasir ke Microsoft Excel (Delegasi ke ShiftController)
     */
    public function exportShiftReportExcel(ShiftController $shiftController)
    {
        return $shiftController->exportShiftReportExcel();
    }

    /**
     * Memisahkan pesanan (Split Bill)
     */
    public function splitOrder(SplitOrderRequest $request, $id_pesanan)
    {
        $validated = $request->validate([
            'split_items' => 'required|array',
            'split_items.*.id_detail' => 'required|exists:detail_pesanan,id',
            'split_items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            $pesananAsli = Pesanan::with('detail_pesanan')->findOrFail($id_pesanan);

            if ($pesananAsli->pembayaran && $pesananAsli->pembayaran->status === 'paid') {
                throw new \Exception('Pesanan sudah dibayar, tidak bisa dipisah.');
            }

            $this->posService->splitOrder($pesananAsli, $validated['split_items']);

            return response()->json(['message' => 'Pesanan berhasil dipisah.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil daftar notifikasi terbaru (misal untuk Panggil Pelayan)
     */
    public function getNotifications()
    {
        $notifications = \App\Models\Notification::where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($notifications);
    }

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function readNotification($id)
    {
        $notif = \App\Models\Notification::find($id);
        if ($notif) {
            $notif->update(['is_read' => true]);

            if ($notif->id_meja) {
                $meja = \App\Models\Meja::find($notif->id_meja);
                if ($meja) {
                    try {
                        broadcast(new \App\Events\MejaStatusUpdated($meja));
                    } catch (\Throwable $e) {}
                }
            }

            return response()->json(['message' => 'Notifikasi ditandai dibaca']);
        }
        return response()->json(['error' => 'Notifikasi tidak ditemukan'], 404);
    }
}