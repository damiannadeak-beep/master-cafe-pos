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
    public function getKasirSnapToken(Request $request, $id)
    {
        try {
            $pesanan = Pesanan::findOrFail($id);
            $customAmount = $request->input('amount') ? (float)$request->input('amount') : null;
            $snapData = $this->paymentService->createSnapToken($pesanan, $customAmount);
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
                    $ids = [(int)$id_pesanan];
                }
            }

            $targetStatus = $request->validated('status');
            $result = $this->posService->updateOrderStatus($ids, $targetStatus, auth()->id());

            return response()->json([
                'message' => 'Status pesanan berhasil diupdate',
                'status' => $result['status'],
                'order_ids' => $result['order_ids']
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
            $result = $this->posService->toggleItemServed((int) $id_detail);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
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
            $lastPesanan?->loadMissing('pembayaran');
            return response()->json([
                'message' => 'Pembayaran berhasil dikonfirmasi.',
                'id_pesanan' => $lastPesanan ? $lastPesanan->id : (int)$id_pesanan,
                'status' => $lastPesanan?->status,
                'metode' => $lastPesanan?->pembayaran?->metode ?? 'cash',
                'total_bayar' => (float) ($lastPesanan?->pembayaran?->total_bayar ?? 0),
                'uang_diterima' => (float) ($lastPesanan?->pembayaran?->uang_diterima ?? 0),
                'uang_kembalian' => (float) ($lastPesanan?->pembayaran?->uang_kembalian ?? 0),
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

            if ($pesananAsli->pembayaran && $pesananAsli->pembayaran->isPaid()) {
                throw new \Exception('Pesanan sudah dibayar, tidak bisa dipisah.');
            }

            $this->posService->splitOrder($pesananAsli, $validated['split_items']);

            return response()->json(['message' => 'Pesanan berhasil dipisah.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Menggabungkan pesanan aktif (Merge Bill / Merge Orders)
     */
    public function mergeOrder(Request $request, $id_pesanan)
    {
        $request->validate([
            'merge_with_id' => 'required|integer|exists:pesanan,id'
        ]);

        try {
            $targetPesanan = Pesanan::with(['detail_pesanan', 'pembayaran'])->findOrFail($id_pesanan);
            $sourcePesanan = Pesanan::with(['detail_pesanan', 'pembayaran'])->findOrFail($request->merge_with_id);

            $merged = $this->posService->mergeOrders($targetPesanan, $sourcePesanan);

            return response()->json([
                'message' => "Pesanan #{$sourcePesanan->id} berhasil digabungkan ke Pesanan #{$targetPesanan->id}.",
                'target_order_id' => $merged->id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil daftar pesanan aktif lain yang bisa digabung (Merge Candidates)
     */
    public function getMergeCandidates(Request $request, $id_pesanan)
    {
        try {
            $candidates = Pesanan::with(['meja', 'detail_pesanan.menu', 'pembayaran'])
                ->where('id', '!=', $id_pesanan)
                ->whereNotIn('status', [\App\Enums\OrderStatus::CANCELLED->value, \App\Enums\OrderStatus::VOID->value, \App\Enums\OrderStatus::COMPLETED->value])
                ->where(function ($q) {
                    $q->whereDoesntHave('pembayaran')
                      ->orWhereHas('pembayaran', function ($p) {
                          $p->where('status', '!=', \App\Enums\PaymentStatus::PAID->value);
                      });
                })
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($order) {
                    $mejaName = $order->meja ? ($order->meja->nama_meja_atau_nomor ?? 'Meja ' . $order->meja->nomor_meja) : 'Bawa Pulang';
                    $cust = $order->customer_name ?: 'Tamu';
                    $total = number_format((float)$order->total, 0, ',', '.');
                    $count = $order->detail_pesanan->count();
                    return [
                        'id' => $order->id,
                        'label' => "Pesanan #{$order->id} - {$mejaName} ({$cust}) - Rp {$total} ({$count} item)"
                    ];
                });

            return response()->json($candidates);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Mengambil rincian item pesanan untuk keperluan Split Bill
     */
    public function getOrderDetails($id_pesanan)
    {
        try {
            $pesanan = Pesanan::with('detail_pesanan.menu')->findOrFail($id_pesanan);
            $items = $pesanan->detail_pesanan->map(function ($d) {
                return [
                    'id' => $d->id,
                    'menu' => ['nama_menu' => $d->menu?->nama_menu ?? 'Item'],
                    'jumlah' => (int) $d->jumlah,
                    'subtotal' => (float) $d->subtotal,
                ];
            });

            return response()->json([
                'id' => $pesanan->id,
                'customer_name' => $pesanan->customer_name ?: 'Tamu',
                'items' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memuat item pesanan: ' . $e->getMessage()], 404);
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