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
            return response()->view('konsumen.meja_not_found', compact('id_meja'), 404);
        }
        
        session(['id_meja' => $id_meja]);
        
        // Cek apakah ada pesanan 'unpaid' aktif di meja ini yang sudah dikonfirmasi pembayarannya (Konsep Open Bill Resmi)
        $pesananAktif = Pesanan::where('id_meja', $id_meja)
            ->where('status', '!=', 'completed')
            ->whereHas('pembayaran', function($q) {
                $q->where('status', 'unpaid')
                  ->where('metode', 'cash');
            })->first();

        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();

        return view('konsumen.menu', compact('meja', 'menus', 'pesananAktif', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan pilihan tipe pesanan (dine-in vs takeaway).
     */
    public function pilihTipePesanan()
    {
        $mejas = Meja::where('is_available', true)->orderBy('nama_meja_atau_nomor', 'asc')->get();
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
        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_takeaway', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menampilkan menu untuk pesanan Dine-In dari jarak jauh (tanpa meja).
     */
    public function menuNanti()
    {
        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        [$promos, $promoMenuIds] = $this->getActivePromosWithMenuIds();
            
        return view('konsumen.menu_nanti', compact('menus', 'promos', 'promoMenuIds'));
    }

    /**
     * Menambahkan item ke pesanan aktif atau membuat pesanan baru (Open Bill)
     */
    public function tambahPesanan(TambahPesananRequest $request, OrderService $orderService)
    {
        $validated = $request->validated();

        $tipe_pesanan = $validated['tipe_pesanan'] ?? 'dine_in';
        $id_meja = $validated['id_meja'] ?? null;

        if ($tipe_pesanan === 'takeaway') {
            $id_meja = null;
        }

        // Validasi Proteksi GPS / Geofencing untuk Pemesanan Meja (Dine-In)
        if ($tipe_pesanan === 'dine_in' && Setting::getVal('geofence_active', '0') == '1') {
            $cafeLat = Setting::getVal('cafe_latitude') ?: Setting::getVal('warung_latitude');
            $cafeLng = Setting::getVal('cafe_longitude') ?: Setting::getVal('warung_longitude');
            $maxRadius = (float) Setting::getVal('geofence_radius', 100);

            if (!empty($cafeLat) && !empty($cafeLng)) {
                $userLat = $validated['user_lat'] ?? $request->input('user_lat');
                $userLng = $validated['user_lng'] ?? $request->input('user_lng');

                if (empty($userLat) || empty($userLng)) {
                    return response()->json([
                        'error' => 'Pemesanan meja (Dine-In) membutuhkan verifikasi lokasi GPS. Pastikan GPS aktif dan izinkan akses lokasi pada browser Anda.'
                    ], 422);
                }

                $distance = $this->calculateDistanceMeters((float)$cafeLat, (float)$cafeLng, (float)$userLat, (float)$userLng);

                if ($distance > $maxRadius) {
                    return response()->json([
                        'error' => 'Maaf, Anda terdeteksi berada di luar area kafe (jarak sekitar ' . round($distance) . ' meter, batas radius ' . round($maxRadius) . ' meter). Pemesanan meja (Dine-In) hanya dapat dilakukan saat Anda berada langsung di lokasi kafe.'
                    ], 422);
                }
            }
        }

        try {
            DB::beginTransaction();

            // Tentukan Nama Tamu / Konsumen
            $mejaModel = $id_meja ? Meja::find($id_meja) : null;
            $guestName = !empty($validated['guest_name']) ? trim($validated['guest_name']) : null;
            if (empty($guestName)) {
                $guestName = auth()->check() ? auth()->user()->name : ($mejaModel ? ('Tamu ' . $mejaModel->nama_meja_atau_nomor) : 'Tamu');
            }
            $guestPhone = !empty($validated['guest_phone']) ? trim($validated['guest_phone']) : null;

            $orderToken = (string) \Illuminate\Support\Str::uuid();


            // Buat Pesanan & Pembayaran Baru
            $pesanan = Pesanan::create([
                'id_konsumen' => auth()->id(),
                'guest_name' => $guestName,
                'guest_phone' => $guestPhone,
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

            // Broadcast status meja terisi (PesananBaru baru dikirim ke Waitress setelah konsumen selesai memilih metode bayar)
            if ($id_meja && $tipe_pesanan === 'dine_in' && $mejaModel) {
                try {
                    broadcast(new \App\Events\MejaStatusUpdated($mejaModel));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning('[OrderController] Gagal broadcast MejaStatusUpdated: ' . $e->getMessage());
                }
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

            $token = $request->input('token') ?? $request->query('token') ?? $request->header('X-Order-Token') ?? session('order_token');
            $isOwner = false;
            if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
                $isOwner = true;
            } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
                $isOwner = true;
            } elseif (!$pesanan->id_konsumen && session('active_order_id') == $pesanan->id) {
                $isOwner = true;
            }

            if (!$isOwner) {
                throw new \Exception('Anda tidak berhak membatalkan pesanan ini.');
            }

            // Kebijakan kafe: Pesanan yang diinput oleh konsumen tidak dapat dibatalkan karena merupakan tanggung jawab pemesan
            throw new \Exception('Pesanan yang telah dibuat oleh konsumen tidak dapat dibatalkan karena merupakan tanggung jawab pemesan.');

            $pesanan->cancelOrder();

            session()->forget(['order_token', 'active_order_id']);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Pesanan berhasil dibatalkan.']);
            }

            $redirectUrl = $pesanan->id_meja 
                ? URL::signedRoute('konsumen.menu.meja', ['id_meja' => $pesanan->id_meja]) 
                : url('/katalog');
            return redirect($redirectUrl)->with('success', 'Pesanan berhasil dibatalkan.');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Memanggil Pelayan (Call Bell) dari meja.
     */
    public function callBell(CallBellRequest $request)
    {
        $validated = $request->validated();

        $meja = Meja::findOrFail($validated['id_meja']);

        // Mencegah spam (cek apakah ada notifikasi call_bell BELUM DITANGGAPI untuk meja ini dalam 2 menit terakhir)
        $recentCall = \App\Models\Notification::where('type', 'call_bell')
            ->where('id_meja', $meja->id)
            ->where('is_read', false)
            ->where('created_at', '>=', now()->subMinutes(2))
            ->first();

        if ($recentCall) {
            return response()->json(['error' => 'Pelayan sudah dipanggil dan sedang menuju ke meja Anda.'], 429);
        }

        $tableName = preg_match('/^meja\s+/i', $meja->nama_meja_atau_nomor) 
            ? ucwords($meja->nama_meja_atau_nomor) 
            : 'Meja ' . ucwords($meja->nama_meja_atau_nomor);

        \App\Models\Notification::create([
            'type' => 'call_bell',
            'id_meja' => $meja->id,
            'message' => 'Panggilan Pelayan dari ' . $tableName . '!',
            'is_read' => false
        ]);

        // Broadcast event real-time ke Waitress Station
        try {
            broadcast(new \App\Events\MejaStatusUpdated($meja));
        } catch (\Throwable $e) {
            Log::warning('[OrderController] Gagal broadcast call_bell: ' . $e->getMessage());
        }

        // Trigger Push Notification to Admin and Kasir
        $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->with('pushSubscriptions')->get();
        \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
            'Panggilan Pelayan!',
            $tableName . ' membutuhkan bantuan pelayan.',
            '/kasir/pesanan-aktif'
        ));

        return response()->json([
            'message' => 'Pelayan telah dipanggil dan segera menuju meja Anda.',
            'id_meja' => $meja->id
        ]);
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
            ->first();

        // Cek expiry 15 menit untuk pembayaran pending online
        if ($pesanan && $this->checkOrderExpiry($pesanan)) {
            session()->forget(['order_token', 'active_order_id']);
            $redirectUrl = ($pesanan->id_meja) 
                ? \Illuminate\Support\Facades\URL::signedRoute('konsumen.menu.meja', ['id_meja' => $pesanan->id_meja]) 
                : url('/katalog');
            return redirect($redirectUrl)->with('info', 'Waktu batas pembayaran pesanan (15 menit) telah habis. Pesanan dibatalkan otomatis.');
        }

        if (!$pesanan || in_array($pesanan->status, ['cancelled', 'void'])) {
            session()->forget(['order_token', 'active_order_id']);
            $redirectUrl = ($pesanan && $pesanan->id_meja) 
                ? \Illuminate\Support\Facades\URL::signedRoute('konsumen.menu.meja', ['id_meja' => $pesanan->id_meja]) 
                : url('/katalog');
            return redirect($redirectUrl)->with('info', 'Pesanan telah dibatalkan atau tidak ditemukan.');
        }

        $this->checkAndUpdateMidtransStatus($pesanan, $request);

        $pembayaran = $pesanan->pembayaran;
        $meja = $pesanan->meja;

        // Hitung sisa waktu pembayaran (15 menit = 900 detik)
        $remainingSeconds = 0;
        if ($pesanan->status === 'pending' && $pembayaran && $pembayaran->status === 'unpaid') {
            $createdAt = $pesanan->created_at ?: now();
            $remainingSeconds = max(0, 900 - $createdAt->diffInSeconds(now()));
        }

        // Cari semua pesanan terkait dalam sesi yang sama (Dine-In per Meja, Takeaway per Nomor HP)
        $activeTableOrders = collect([$pesanan]);
        $currentGuestName = trim($pesanan->guest_name ?? '');
        $cleanPhone = !empty($pesanan->guest_phone) ? preg_replace('/[^0-9]/', '', $pesanan->guest_phone) : null;
        $orderDate = $pesanan->created_at ? $pesanan->created_at->toDateString() : now()->toDateString();
        $orderTime = $pesanan->created_at ?: now();

        $otherOrdersQuery = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'meja', 'rating'])
            ->whereDate('created_at', $orderDate)
            ->where('created_at', '>=', $orderTime->copy()->subHours(3))
            ->where('created_at', '<=', $orderTime->copy()->addHours(3))
            ->whereIn('status', ['pending', 'processing'])
            ->where('id', '!=', $pesanan->id);

        $otherOrdersQuery->where(function ($q) use ($pesanan, $currentGuestName, $cleanPhone) {
            // 1. Jika pengguna login
            if (auth()->check() && auth()->id()) {
                $q->orWhere('id_konsumen', auth()->id());
            }

            // 2. Berdasarkan nomor telepon tamu (Takeaway maupun Dine-In)
            if (!empty($cleanPhone)) {
                $q->orWhere('guest_phone', $pesanan->guest_phone)
                  ->orWhereRaw("REGEXP_REPLACE(COALESCE(guest_phone, ''), '[^0-9]', '') = ?", [$cleanPhone]);
            }

            // 3. Berdasarkan meja yang sama
            if (!empty($pesanan->id_meja)) {
                $q->orWhere(function ($sub) use ($pesanan, $currentGuestName) {
                    $sub->where('id_meja', $pesanan->id_meja);
                    if (!empty($currentGuestName)) {
                        $sub->whereRaw("LOWER(TRIM(COALESCE(guest_name, ''))) = ?", [strtolower($currentGuestName)]);
                    }
                });
            }

            // 4. Berdasarkan nama tamu yang sama (menggabungkan Dine-In dan Takeaway dalam sesi yang sama)
            if (!empty($currentGuestName) && !str_starts_with(strtolower($currentGuestName), 'tamu meja')) {
                $q->orWhereRaw("LOWER(TRIM(COALESCE(guest_name, ''))) = ?", [strtolower($currentGuestName)]);
            }
        });

        $otherOrders = $otherOrdersQuery->orderBy('id', 'asc')->get();

        if ($otherOrders->isNotEmpty()) {
            $activeTableOrders = collect([$pesanan])->merge($otherOrders)->sortBy('id')->values();
        }

        return view('konsumen.tracking', compact('pesanan', 'pembayaran', 'meja', 'activeTableOrders', 'remainingSeconds'));
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
    public function getOrderStatus(Request $request, $order_token)
    {
        $pesanan = Pesanan::with(['pembayaran'])
            ->where('order_token', $order_token)
            ->first();

        if (!$pesanan || $this->checkOrderExpiry($pesanan)) {
            return response()->json([
                'status' => 'cancelled',
                'payment_status' => 'cancelled',
                'is_expired' => true,
                'message' => 'Waktu batas pembayaran telah habis atau pesanan dibatalkan.',
            ]);
        }

        $this->checkAndUpdateMidtransStatus($pesanan, $request);

        $remainingSeconds = 0;
        if ($pesanan->status === 'pending' && $pesanan->pembayaran && $pesanan->pembayaran->status === 'unpaid') {
            $createdAt = $pesanan->created_at ?: now();
            $remainingSeconds = max(0, 900 - $createdAt->diffInSeconds(now()));
        }

        return response()->json([
            'id' => $pesanan->id,
            'status' => $pesanan->status,
            'payment_status' => $pesanan->pembayaran?->status ?? 'unpaid',
            'payment_method' => $pesanan->pembayaran?->metode ?? '-',
            'total' => $pesanan->total,
            'updated_at' => $pesanan->updated_at->toIso8601String(),
            'remaining_seconds' => $remainingSeconds,
        ]);
    }

    /**
     * Cek apakah pesanan telah kadaluarsa (khusus pembayaran online/takeaway belum bayar > 15 menit).
     */
    private function checkOrderExpiry($pesanan): bool
    {
        if (!$pesanan || $pesanan->status !== 'pending') {
            return false;
        }

        $pembayaran = $pesanan->pembayaran;
        if ($pembayaran && $pembayaran->status === 'unpaid' && $pembayaran->metode !== 'cash') {
            $createdAt = $pesanan->created_at ?: now();
            if ($createdAt->diffInSeconds(now()) >= 900) { // 15 menit = 900 detik
                $pesanan->cancelOrder();
                return true;
            }
        }

        return false;
    }

    /**
     * Helper internal untuk verifikasi status Midtrans secara proaktif & otomatis
     */
    private function checkAndUpdateMidtransStatus($pesanan, Request $request = null)
    {
        if (!$pesanan || !$pesanan->pembayaran || $pesanan->pembayaran->status === 'paid') {
            return;
        }

        // Jika konsumen sudah memilih bayar tunai (cash) dan tidak sedang kembali dari redirect Midtrans, abaikan
        if ($pesanan->pembayaran->metode === 'cash' && (!$request || (!$request->has('paid') && !$request->has('order_id')))) {
            return;
        }

        $isPaid = false;
        $metodeBayar = $pesanan->pembayaran->metode ?: 'qris';

        // 1. Cek dari Query Parameter Sukses (Redirect dari Midtrans Snap, finish URL, atau klik Saya Sudah Bayar)
        if ($request) {
            $qPaid = $request->query('paid');
            $qAuto = $request->query('auto_settle');
            $qStatus = $request->query('transaction_status');
            $qCode = $request->query('status_code');

            if ($qAuto == '1' || in_array($qStatus, ['capture', 'settlement']) || ($qPaid == '1' && in_array($qCode, ['200', '201']))) {
                $isPaid = true;
                if ($request->query('payment_type')) {
                    $metodeBayar = in_array($request->query('payment_type'), ['qris', 'gopay', 'shopeepay']) ? 'qris' : 'bank_transfer';
                }
            }
        }

        // 2. Cek langsung ke API Midtrans (jika belum terdeteksi dari query params)
        if (!$isPaid) {
            $serverKey = trim(Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey')));
            $rawIsProd = Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction'));
            $isProduction = filter_var($rawIsProd, FILTER_VALIDATE_BOOLEAN);

            if (!empty($serverKey)) {
                $candidates = [];
                if ($request) {
                    if ($request->query('order_id')) $candidates[] = $request->query('order_id');
                    if ($request->query('midtrans_order_id')) $candidates[] = $request->query('midtrans_order_id');
                }
                if (!empty($pesanan->pembayaran->midtrans_order_id)) {
                    $candidates[] = $pesanan->pembayaran->midtrans_order_id;
                }
                if (session('midtrans_order_id_' . $pesanan->id)) {
                    $candidates[] = session('midtrans_order_id_' . $pesanan->id);
                }

                $candidates = array_unique(array_filter($candidates));
                if (empty($candidates)) {
                    return;
                }

                try {
                    \Midtrans\Config::$serverKey = $serverKey;
                    \Midtrans\Config::$isProduction = $isProduction;

                    foreach (array_unique(array_filter($candidates)) as $targetMidtransId) {
                        try {
                            $statusMidtrans = \Midtrans\Transaction::status($targetMidtransId);
                            $status = is_object($statusMidtrans) ? ($statusMidtrans->transaction_status ?? '') : ($statusMidtrans['transaction_status'] ?? '');
                            if (in_array($status, ['capture', 'settlement'])) {
                                $isPaid = true;
                                $type = is_object($statusMidtrans) ? ($statusMidtrans->payment_type ?? '') : ($statusMidtrans['payment_type'] ?? '');
                                $metodeBayar = in_array($type, ['qris', 'gopay', 'shopeepay']) ? 'qris' : 'bank_transfer';
                                break;
                            }
                        } catch (\Throwable $e) {
                            // Candidate failed, continue loop
                        }
                    }
                } catch (\Throwable $e) {
                    Log::info('[OrderController] checkAndUpdateMidtransStatus notice: ' . $e->getMessage());
                }
            }
        }

        // 3. Jika terverifikasi lunas, tandai database & dorong ke Kasir / Waitress
        if ($isPaid) {
            $pesanan->pembayaran->update([
                'status' => 'paid',
                'metode' => $metodeBayar,
                'tanggal' => now(),
            ]);
            $pesanan->update(['status' => 'processing']);

            try {
                broadcast(new \App\Events\PesananBaru($pesanan));
                if ($pesanan->id_meja && $pesanan->meja) {
                    broadcast(new \App\Events\MejaStatusUpdated($pesanan->meja));
                }
            } catch (\Throwable $e) {
                Log::warning('[OrderController] Gagal broadcast WebSocket status update: ' . $e->getMessage());
            }

            $namaKonsumen = $pesanan->customer_name;
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => 'Pesanan Lunas Midtrans (' . strtoupper($metodeBayar) . '): Order #' . $pesanan->id . ' (' . $namaKonsumen . ') telah lunas.',
                'is_read' => false
            ]);

            try {
                $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
                \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                    'Pesanan Lunas (Midtrans)',
                    'Order #' . $pesanan->id . ' (' . $namaKonsumen . ') telah dibayar lunas via Midtrans (' . strtoupper($metodeBayar) . ').',
                    '/kasir/pesanan-aktif'
                ));
            } catch (\Throwable $e) {
                Log::warning('[OrderController] Gagal kirim WebPush: ' . $e->getMessage());
            }
        }
    }

    /**
     * Menghitung jarak antara dua koordinat GPS dalam satuan meter (Formula Haversine).
     */
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
