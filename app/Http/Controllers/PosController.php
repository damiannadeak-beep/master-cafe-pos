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
    /**
     * Menampilkan Halaman POS untuk Kasir
     */
    public function index()
    {
        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        $mejas = Meja::all();
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
                app(PosService::class)->checkAndUpdateMidtransStatus($order);
            }
        }

        // 2. Ambil pesanan aktif:
        // - Dine-In: Tampil agar Waitress bisa mengantar & menagih
        // - Takeaway: WAJIB LUNAS (pembayaran.status = 'paid') atau dibuat langsung oleh Kasir (id_kasir != null)
        $orders = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen'])
            ->whereIn('status', ['pending', 'processing'])
            ->whereNotIn('status', ['cancelled', 'void'])
            ->where(function ($sub) {
                $sub->where('tipe_pesanan', '!=', 'takeaway')
                    ->orWhereNotNull('id_kasir')
                    ->orWhereHas('pembayaran', function ($p) {
                        $p->where('status', 'paid');
                    });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedOrders = $this->groupActiveOrders($orders);

        // 3. Ambil pesanan selesai terbaru (History pesanan selesai untuk Tablet Waitress)
        $completedOrders = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran', 'konsumen', 'kasir'])
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->take(50)
            ->get();

        if ($request->query('history_only')) {
            return view('components.waitress.completed-order-card', compact('completedOrders'))->render();
        }

        if ($request->ajax() || $request->wantsJson() || $request->query('cards_only')) {
            return view('components.waitress.active-order-card', compact('groupedOrders', 'orders'))->render();
        }

        return view('waitress.pesanan_aktif', compact('groupedOrders', 'orders', 'completedOrders'));
    }

    /**
     * Mengelompokkan pesanan aktif per Meja (Dine-In) atau per Tamu (Takeaway)
     * Delegasi ke PosService untuk arsitektur yang bersih.
     */
    public function groupActiveOrders($orders)
    {
        return app(PosService::class)->groupActiveOrders(
            $orders instanceof \Illuminate\Support\Collection ? $orders : collect($orders)
        );
    }

    /**
     * Mengambil jumlah pesanan aktif untuk badge notifikasi
     */
    public function activeOrdersCount(PosService $posService)
    {
        return response()->json($posService->getActiveOrdersCountData());
    }

    /**
     * Memproses pesanan manual dari Kasir
     */
    public function storeManualOrder(StoreManualOrderRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

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
            $result = $orderService->processOrderItems($pesanan, $validated['items']);
            $totalSemua = $result['total'];
            $total_hpp = $result['total_hpp'];

            // 4. Hitung diskon via OrderService
            $discountAmount = $orderService->calculateDiscount(
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
            $metodeBayar = $validated['pembayaran_langsung'] ? ($validated['metode_pembayaran'] ?? 'cash') : null;

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

            DB::commit();
            return response()->json([
                'message' => 'Pesanan manual berhasil diproses.',
                'id_pesanan' => $pesanan->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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

    public function payOrder(PayOrderRequest $request, $id_pesanan, PaymentService $paymentService)
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

            $lastPesanan = null;
            foreach ($ids as $singleId) {
                $pesanan = Pesanan::with('pembayaran')->find($singleId);
                if (!$pesanan) continue;
                if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid') {
                    $lastPesanan = $pesanan;
                    continue;
                }

                $lastPesanan = $paymentService->processPayment(
                    $singleId,
                    $validated['metode'],
                    $validated['email_pelanggan'] ?? null,
                    auth()->id(),
                    $validated['nominal_tunai'] ?? null,
                    !empty($validated['is_uang_pas'])
                );
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
    public function printReceipt($id, PrintService $printService)
    {
        $order = $printService->prepareReceiptOrder($id);
        return view('waitress.receipt', compact('order'));
    }

    /**
     * Cetak struk langsung ke Printer Thermal (Raw ESC/POS Network)
     */
    public function printThermalReceipt($id, PrintService $printService)
    {
        try {
            $printService->printReceipt($id);
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

            if ($pesanan->pembayaran && $pesanan->pembayaran->status === 'paid') {
                throw new \Exception('Pesanan sudah dibayar lunas oleh konsumen dan tidak dapat dihapus/divoid sembarangan oleh kasir tanpa persetujuan konsumen.');
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
            return response()->json(['message' => 'Pesanan berhasil divoid. Stok telah dikembalikan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Cetak struk dapur (tanpa harga).
     */
    public function printKitchenReceipt($id, PrintService $printService)
    {
        if (request()->ajax() || request()->wantsJson()) {
            try {
                $printService->printKitchenReceipt($id);
                return response()->json(['message' => 'Tiket dapur berhasil dikirim ke printer thermal.']);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $order = $printService->prepareKitchenReceiptOrder($id);
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

            app(PosService::class)->splitOrder($pesananAsli, $validated['split_items']);

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