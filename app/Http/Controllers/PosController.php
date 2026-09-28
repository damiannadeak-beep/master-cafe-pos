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
use Illuminate\Support\Facades\Log;

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
        // 1. Proaktif cek status Midtrans hanya jika bukan request AJAX card polling
        //    dan dibatasi maksimal 1 kali per 15 detik agar tidak memblokir latensi kasir
        if (!$request->ajax() && !$request->query('cards_only') && !$request->query('history_only') && !$request->query('voided_only')) {
            $lastCheck = cache()->get('last_pos_midtrans_sync_time');
            if (!$lastCheck || now()->diffInSeconds($lastCheck) >= 15) {
                cache()->put('last_pos_midtrans_sync_time', now(), 60);
                $pendingCheckOrders = Pesanan::with(['pembayaran'])
                    ->whereIn('status', ['pending', 'processing'])
                    ->where('created_at', '>=', now()->subMinutes(30))
                    ->whereHas('pembayaran', function ($p) {
                        $p->where('status', '!=', 'paid')
                          ->where('metode', '!=', 'cash')
                          ->whereNotNull('midtrans_order_id');
                    })
                    ->latest()
                    ->take(3)
                    ->get();

                foreach ($pendingCheckOrders as $order) {
                    try {
                        $this->posService->checkAndUpdateMidtransStatus($order);
                    } catch (\Throwable $e) {
                        Log::warning('[PosController] Gagal sync Midtrans pesananAktif: ' . $e->getMessage());
                    }
                }
            }
        }

        // 2. Ambil pesanan aktif via PosService:
        // - Termasuk pesanan pending, processing, dan pesanan selesai dimasak namun belum lunas
        $orders = $this->posService->getActiveOrdersQuery()
            ->orderBy('created_at', 'asc')
            ->get();

        $groupedOrders = $this->groupActiveOrders($orders);

        $historyScope = $request->query('scope', 'today'); // 'today' (default) atau 'all'
        $todayOnly = ($historyScope !== 'all');

        // 3. Ambil pesanan selesai terbaru (History pesanan selesai untuk Tablet Waitress / Kasir)
        // Hanya pesanan yang sudah selesai dimasak DAN lunas yang masuk ke history selesai
        $completedOrders = $this->posService->getCompletedOrdersQuery(50, $todayOnly)->get();
        $groupedCompletedOrders = $this->groupCompletedOrders($completedOrders);

        // 4. Ambil riwayat pesanan yang dibatalkan / dihapus (Void / Cancelled)
        $voidedOrders = $this->posService->getVoidedOrdersQuery(50, $todayOnly)->get();

        if ($request->query('history_only')) {
            return view('components.waitress.completed-order-card', compact('completedOrders', 'groupedCompletedOrders'))->render();
        }

        if ($request->query('voided_only')) {
            return view('components.waitress.voided-order-card', compact('voidedOrders'))->render();
        }

        if ($request->ajax() || $request->wantsJson() || $request->query('cards_only')) {
            return view('components.waitress.active-order-card', compact('groupedOrders', 'orders'))->render();
        }

        return view('waitress.pesanan_aktif', compact('groupedOrders', 'orders', 'completedOrders', 'groupedCompletedOrders', 'voidedOrders', 'historyScope'));
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
     * Mengambil jumlah pesanan aktif untuk badge notifikasi (dengan optimasi HTTP ETag)
     */
    public function activeOrdersCount(Request $request)
    {
        $data = $this->posService->getActiveOrdersCountData();
        $etag = '"' . ($data['hash'] ?? md5(json_encode($data))) . '"';

        if ($request->header('If-None-Match') === $etag) {
            return response()->noContent(304, ['ETag' => $etag]);
        }

        return response()->json($data)->header('ETag', $etag);
    }


    /**
     * Memproses pesanan manual dari Kasir
     */
    public function storeManualOrder(StoreManualOrderRequest $request)
    {
        try {
            $result = $this->orderService->createManualOrder(
                $request->validated(),
                auth()->id(),
                $this->paymentService
            );

            $pesanan = $result['pesanan'];
            $snapData = $result['snap_data'] ?? null;

            return response()->json([
                'message' => 'Pesanan manual berhasil diproses.',
                'id_pesanan' => $pesanan->id,
                'snap_token' => $snapData['snap_token'] ?? null,
                'client_key' => $snapData['client_key'] ?? null,
                'is_production' => $snapData['is_production'] ?? false,
            ]);

        } catch (\Exception $e) {
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

                if ($targetStatus === 'completed') {
                    $pesanan->detail_pesanan()->update(['is_served' => true]);
                }

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
     * Toggle status centang per-item menu (is_served) oleh waitress
     */
    public function toggleItemServed(Request $request, $id_detail)
    {
        try {
            $item = DetailPesanan::with('pesanan.detail_pesanan')->findOrFail($id_detail);
            $item->is_served = !$item->is_served;
            $item->save();

            $pesanan = $item->pesanan;
            $allServed = false;
            $servedCount = 0;
            $totalCount = 0;

            if ($pesanan) {
                $totalCount = $pesanan->detail_pesanan->count();
                $servedCount = $pesanan->detail_pesanan->where('is_served', true)->count();
                $allServed = ($servedCount === $totalCount);

                try {
                    broadcast(new \App\Events\PesananBaru($pesanan));
                } catch (\Throwable $e) {}
            }

            return response()->json([
                'success' => true,
                'id' => $item->id,
                'is_served' => (bool)$item->is_served,
                'served_count' => $servedCount,
                'total_count' => $totalCount,
                'all_served' => $allServed,
                'order_id' => $pesanan ? $pesanan->id : null,
                'message' => $item->is_served ? 'Menu ditandai siap / disajikan.' : 'Menu ditandai belum siap.'
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

            $lastPesanan = $this->paymentService->payOrderGroup(
                $request->input('order_ids'),
                $id_pesanan,
                $validated,
                auth()->id()
            );

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
            $this->posService->voidOrder(
                (int) $id_pesanan,
                (string) $request->input('password'),
                $request->input('alasan'),
                auth()->user()
            );
            return response()->json(['message' => 'Pesanan berhasil divoid.']);
        } catch (\Exception $e) {
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
     * Memisahkan pesanan (Split Bill)
     */
    public function splitOrder(SplitOrderRequest $request, $id_pesanan)
    {
        $validated = $request->validated();

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
        $notifications = \Illuminate\Support\Facades\Cache::remember('unread_notifications_data', 2, function () {
            return \App\Models\Notification::where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->get();
        });
            
        return response()->json($notifications);
    }

    /**
     * Tandai notifikasi sudah dibaca
     */
    public function readNotification($id)
    {
        \Illuminate\Support\Facades\Cache::forget('unread_notifications_data');
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