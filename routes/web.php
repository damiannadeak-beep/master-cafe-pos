<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\SocialAuthController;

// Import semua controller yang dibutuhkan
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMenuController;
use App\Http\Controllers\AdminKasirController;
use App\Http\Controllers\AdminPromoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminBahanController;
use App\Http\Controllers\AdminPengeluaranController;
use App\Http\Controllers\AdminMejaController;
use App\Http\Controllers\KasirPengeluaranController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\KasirMejaController;

// ================= AREA PUBLIK =================
// Halaman yang bisa diakses tanpa perlu login
Route::get('/', [PublicController::class, 'home']);
Route::get('/katalog', [PublicController::class, 'katalog']);
Route::get('/lokasi', [PublicController::class, 'lokasi']);
Route::get('/kontak', [PublicController::class, 'kontak']);



// Route Pembaca Gambar Fail-Safe (Ganti Symlink di Hostings cPanel)
Route::get('/storage/{path}', function ($path) {
    $homeDir = env('HOME') ?: getenv('HOME') ?: '/home/nadp3189';
    
    // Cari file di SELURUH kemungkinan lokasi folder cPanel
    $searchPaths = [
        storage_path('app/public/' . $path),
        public_path('storage/' . $path),
        // Folder utama home
        $homeDir . '/mastercafe.nadeak.net/public/storage/' . $path,
        $homeDir . '/mastercafe.nadeak.net/storage/app/public/' . $path,
        $homeDir . '/mastercafe.nadeak.net/storage/' . $path,
        // Folder public_html langsung
        $homeDir . '/public_html/storage/app/public/' . $path,
        $homeDir . '/public_html/storage/' . $path,
        $homeDir . '/public_html/public/storage/' . $path,
        // Folder public_html/mastercafe.nadeak.net
        $homeDir . '/public_html/mastercafe.nadeak.net/public/storage/' . $path,
        $homeDir . '/public_html/mastercafe.nadeak.net/storage/app/public/' . $path,
        $homeDir . '/public_html/mastercafe.nadeak.net/storage/' . $path,
        // Folder repositories
        $homeDir . '/repositories/master-cafe-pos/storage/app/public/' . $path,
        $homeDir . '/repositories/master-cafe-pos/public/storage/' . $path,
    ];

    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $searchPaths[] = $docRoot . '/storage/' . $path;
        $searchPaths[] = $docRoot . '/public/storage/' . $path;
        $searchPaths[] = $docRoot . '/storage/app/public/' . $path;
    }

    foreach (array_unique($searchPaths) as $candidate) {
        if (file_exists($candidate) && is_file($candidate)) {
            $mime = mime_content_type($candidate) ?: 'image/png';
            return response()->file($candidate, ['Content-Type' => $mime, 'Cache-Control' => 'public, max-age=86400']);
        }
    }
    
    // Fail-safe: jika gambar tidak ditemukan, kembalikan logo resmi Master Cafe
    $logoPaths = [
        public_path('images/logo.png'),
        base_path('public/images/logo.png'),
        $homeDir . '/public_html/mastercafe.nadeak.net/images/logo.png',
        $homeDir . '/repositories/master-cafe-pos/public/images/logo.png',
    ];
    foreach ($logoPaths as $logo) {
        if (file_exists($logo) && is_file($logo)) {
            return response()->file($logo, ['Content-Type' => 'image/png', 'Cache-Control' => 'public, max-age=86400']);
        }
    }
    return redirect('/images/logo.png');
})->where('path', '.*');



use App\Http\Controllers\Auth\OwnerLoginController;
use App\Http\Controllers\Auth\KasirLoginController;
use App\Http\Middleware\VerifySecretOwnerAccess;

$ownerSlug = config('auth.owner_path', 'ruang-owner-x92k');
$kasirSlug = config('auth.kasir_path', 'pos-kasir-gate-88');

// ================= JALUR RAHASIA PEMILIK (OWNER) =================
Route::prefix($ownerSlug)->middleware(['web', VerifySecretOwnerAccess::class])->group(function () {
    Route::get('/login', [OwnerLoginController::class, 'showLoginForm'])->middleware('throttle:5,1')->name('owner.login');
    Route::post('/login', [OwnerLoginController::class, 'login'])->middleware('throttle:3,1')->name('owner.login.submit');
});

// ================= JALUR RAHASIA KASIR (POS) =================
Route::prefix($kasirSlug)->middleware(['web'])->group(function () {
    Route::get('/login', [KasirLoginController::class, 'showLoginForm'])->middleware('throttle:5,1')->name('kasir.login');
    Route::post('/login', [KasirLoginController::class, 'login'])->middleware('throttle:3,1')->name('kasir.login.submit');
});

// ================= DECOY & STEALTH ROUTES =================
// Menyamarkan jalur umum menjadi 404 murni agar bot/hacker mengira tidak ada panel admin/kasir
Route::any('/admin', fn() => abort(404));
Route::any('/administrator', fn() => abort(404));
Route::any('/owner', fn() => abort(404));
Route::any('/kasir', fn() => abort(404));
Route::any('/staff/login', fn() => abort(404));
Route::any('/wp-admin', fn() => abort(404));

// Logout dan decoy 404 untuk rute bawaan /login
Route::get('/login', fn() => abort(404))->name('login');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');



// ================= AREA AUTENTIKASI =================
// Semua route di dalam grup ini wajib login
Route::middleware(['auth'])->group(function () {
    
    // Halaman Redirect Default setelah login (jika user mengakses /home secara manual)
    Route::get('/home', function () {
        if (auth()->user()->hasRole('pemilik')) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->hasRole('kasir')) {
            return redirect()->route('kasir.pos');
        }
        return redirect('/');
    });

    // Endpoint Web Push Subscription
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'update']);

    // Role: Pemilik (Admin)
    Route::middleware(['role:pemilik'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/api/ai/sales-analysis', [AdminController::class, 'aiSalesAnalysis'])->name('ai_sales_analysis');
        Route::get('/laporan', [AdminController::class, 'reports'])->name('reports.index');
        Route::get('/reports/revenue', [AdminController::class, 'downloadRevenueReport'])->name('reports.revenue');
        Route::get('/reports/pdf', [AdminController::class, 'exportPdf'])->name('reports.pdf');
        Route::get('/reports/csv', [AdminController::class, 'exportCsv'])->name('reports.csv');
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings/profile', [AdminController::class, 'updateStoreProfile'])->name('settings.profile');
        Route::post('/settings/security', [AdminController::class, 'updateSecurity'])->name('settings.security');
        Route::post('/settings/payment', [AdminController::class, 'updatePaymentSettings'])->name('settings.payment');
        Route::post('/settings/printer', [AdminController::class, 'updatePrinterSettings'])->name('settings.printer');
        Route::post('/settings/absensi', [AdminController::class, 'updateAbsensiSettings'])->name('settings.absensi');
        Route::post('/settings/lokasi', [AdminController::class, 'updateLokasiSettings'])->name('settings.lokasi');
        Route::post('/settings/kontak', [AdminController::class, 'updateKontakSettings'])->name('settings.kontak');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{id}/reply', [AdminController::class, 'replyReview'])->name('reviews.reply');
        
        Route::get('/backup', [AdminController::class, 'backupDatabase'])->name('backup');

        // Route Bantuan (Clear Cache Ã¢â‚¬â€ hanya pemilik yang boleh)
        Route::get('/clear-cache', function() {
            \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            return redirect()->back()->with('success', 'Cache berhasil dibersihkan.');
        })->name('clear_cache');
        
        // Laporan Absensi
        Route::get('/absensi', [AdminController::class, 'absensiReport'])->name('absensi.index');
        Route::put('/absensi/{id}', [\App\Http\Controllers\AbsensiController::class, 'updateAdmin'])->name('absensi.update');

        // Log Void Pesanan
        Route::get('/void-logs', [\App\Http\Controllers\AdminVoidLogController::class, 'index'])->name('void_logs.index');
        
        // Log Aktivitas
        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity_logs.index');
        Route::get('/backups', [AdminController::class, 'backups'])->name('backups.index');
        Route::post('/backups/run', [AdminController::class, 'runBackup'])->name('backups.run');
        Route::get('/backups/download/{filename}', [AdminController::class, 'downloadBackup'])->name('backups.download');
        Route::delete('/backups/{filename}', [AdminController::class, 'deleteBackup'])->name('backups.delete');

        // Menu management (dikelompokkan)
        Route::get('/menu', [AdminMenuController::class, 'index'])->name('menu.index');
        Route::get('/menu/create', [AdminMenuController::class, 'create'])->name('menu.create');
        Route::post('/menu', [AdminMenuController::class, 'store'])->name('menu.store');
        Route::get('/menu/{id}/edit', [AdminMenuController::class, 'edit'])->name('menu.edit');
        Route::put('/menu/{id}', [AdminMenuController::class, 'update'])->name('menu.update');
        Route::delete('/menu/{id}', [AdminMenuController::class, 'destroy'])->name('menu.destroy');
        Route::post('/menu/{id}/stock', [AdminMenuController::class, 'updateStock'])->name('menu.stock');
        Route::post('/menu/ai-description', [AdminMenuController::class, 'generateAiDescription'])->name('menu.ai_description');

        // Stok Bahan Baku
        Route::get('/stok', [AdminBahanController::class, 'index'])->name('stok.index');
        Route::post('/stok', [AdminBahanController::class, 'store'])->name('stok.store');
        Route::put('/stok/{id}', [AdminBahanController::class, 'update'])->name('stok.update');
        Route::delete('/stok/{id}', [AdminBahanController::class, 'destroy'])->name('stok.destroy');

        // Pengeluaran
        Route::get('/pengeluaran', [AdminPengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::post('/pengeluaran', [AdminPengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::delete('/pengeluaran/{id}', [AdminPengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

        // Meja
        Route::get('/meja', [AdminMejaController::class, 'index'])->name('meja.index');
        Route::post('/meja', [AdminMejaController::class, 'store'])->name('meja.store');
        Route::put('/meja/{id}', [AdminMejaController::class, 'update'])->name('meja.update');
        Route::delete('/meja/{id}', [AdminMejaController::class, 'destroy'])->name('meja.destroy');
        Route::get('/meja/{id}/qr', [AdminMejaController::class, 'printQr'])->name('meja.print_qr');

        // Kasir management
        Route::get('/kasir/manage', [AdminKasirController::class, 'index'])->name('kasir.index');
        Route::post('/kasir', [AdminKasirController::class, 'store'])->name('kasir.store');
        Route::get('/kasir/{id}/edit', [AdminKasirController::class, 'edit'])->name('kasir.edit');
        Route::put('/kasir/{id}', [AdminKasirController::class, 'update'])->name('kasir.update');
        Route::delete('/kasir/{id}', [AdminKasirController::class, 'destroy'])->name('kasir.destroy');

        // Promo management
        Route::get('/promo', [AdminPromoController::class, 'index'])->name('promo.index');
        Route::get('/promo/create', [AdminPromoController::class, 'create'])->name('promo.create');
        Route::post('/promo', [AdminPromoController::class, 'store'])->name('promo.store');
        Route::get('/promo/{id}/edit', [AdminPromoController::class, 'edit'])->name('promo.edit');
        Route::put('/promo/{id}', [AdminPromoController::class, 'update'])->name('promo.update');
        Route::delete('/promo/{id}', [AdminPromoController::class, 'destroy'])->name('promo.destroy');
        // User management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        // Permintaan Belanja (Admin)
        Route::get('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminIndex'])->name('permintaan.index');
        Route::post('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminStore'])->name('permintaan.store');
        Route::put('/permintaan-belanja/{id}', [\App\Http\Controllers\PermintaanBelanjaController::class, 'adminUpdateStatus'])->name('permintaan.update');

    });

    // Role: Kasir
    Route::middleware(['role:kasir'])->prefix('kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('kasir.pos');
        Route::get('/pesanan-aktif', [PosController::class, 'pesananAktif'])->name('kasir.pesanan_aktif');
        Route::post('/manual-order', [PosController::class, 'storeManualOrder']);
        Route::put('/order/{id_pesanan}/status', [PosController::class, 'updateOrderStatus']);
        Route::put('/order/{id_pesanan}/pay', [PosController::class, 'payOrder']);
        Route::put('/order/{id_pesanan}/verify-payment', [PosController::class, 'verifyPayment']);
        Route::put('/order/{id_pesanan}/reject-payment', [PosController::class, 'rejectPayment']);
        Route::put('/order/{id_pesanan}/void', [PosController::class, 'voidOrder'])->name('kasir.order.void');
        Route::post('/order/{id_pesanan}/split', [PosController::class, 'splitOrder'])->name('kasir.order.split');
        Route::get('/order/{id}/receipt', [PosController::class, 'printReceipt'])->name('kasir.order.receipt');
        Route::post('/order/{id}/print-thermal', [PosController::class, 'printThermalReceipt'])->name('kasir.order.thermal');
        Route::get('/order/{id}/kitchen-receipt', [PosController::class, 'printKitchenReceipt'])->name('kasir.order.kitchen');
        Route::get('/shift-report', [PosController::class, 'shiftReport'])->name('kasir.shift_report');
        Route::get('/shift-report/pdf', [PosController::class, 'exportShiftReportPdf'])->name('kasir.shift_report.pdf');
        Route::get('/shift-report/excel', [PosController::class, 'exportShiftReportExcel'])->name('kasir.shift_report.excel');
        Route::get('/api/active-orders-count', [PosController::class, 'activeOrdersCount'])->name('kasir.active_orders_count');
        Route::get('/api/notifications', [PosController::class, 'getNotifications']);
        Route::post('/api/notifications/{id}/read', [PosController::class, 'readNotification']);

        // Pengeluaran Kasir
        Route::get('/pengeluaran', [KasirPengeluaranController::class, 'index'])->name('kasir.pengeluaran.index');
        Route::post('/pengeluaran', [KasirPengeluaranController::class, 'store'])->name('kasir.pengeluaran.store');

        // Manajemen Meja Kasir
        Route::get('/meja', [KasirMejaController::class, 'index'])->name('kasir.meja.index');
        Route::put('/meja/{id}/toggle', [KasirMejaController::class, 'toggle'])->name('kasir.meja.toggle');

        // Stok Opname Kasir
        Route::get('/stok', [\App\Http\Controllers\KasirStokController::class, 'index'])->name('kasir.stok.index');
        Route::post('/stok', [\App\Http\Controllers\KasirStokController::class, 'update'])->name('kasir.stok.update');

        // Absensi Geolocation
        Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('kasir.absensi.index');
        Route::post('/absensi', [\App\Http\Controllers\AbsensiController::class, 'store'])->name('kasir.absensi.store');
        // Permintaan Belanja (Kasir)
        Route::get('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'kasirIndex'])->name('kasir.permintaan.index');
        Route::post('/permintaan-belanja', [\App\Http\Controllers\PermintaanBelanjaController::class, 'kasirStore'])->name('kasir.permintaan.store');

        // Shift Kasir
        Route::get('/shift/buka', [\App\Http\Controllers\ShiftController::class, 'bukaShift'])->name('kasir.shift.buka');
        Route::post('/shift/buka', [\App\Http\Controllers\ShiftController::class, 'storeBukaShift'])->name('kasir.shift.storeBuka');
        Route::get('/shift/tutup', [\App\Http\Controllers\ShiftController::class, 'tutupShift'])->name('kasir.shift.tutup');
        Route::post('/shift/tutup', [\App\Http\Controllers\ShiftController::class, 'storeTutupShift'])->name('kasir.shift.storeTutup');
    });

    // Role: Konsumen (Akun Terdaftar)
    Route::middleware(['role:konsumen'])->prefix('konsumen')->group(function () {
        Route::get('/profil', [KonsumenController::class, 'index']);
        Route::post('/profil/update', [KonsumenController::class, 'updateProfil']);
    });
});

// =========================================================================
// AREA PEMESANAN MEJA GUEST (DINE-IN TANPA LOGIN / PUBLIC ORDERING)
// =========================================================================
Route::group([], function () {
    // Akses Menu Meja via QR Code (Signed URL wajib di production, fleksibel di local)
    $tableMenuMiddleware = app()->isProduction() ? ['signed'] : [];
    Route::get('/konsumen/menu/{id_meja}', [OrderController::class, 'showMenu'])->name('konsumen.menu.meja')->middleware($tableMenuMiddleware);
    Route::get('/menu/{id_meja}', [OrderController::class, 'showMenu'])->middleware($tableMenuMiddleware);
    
    // Pemilihan Tipe & Info
    Route::get('/konsumen/pilih-tipe', [OrderController::class, 'pilihTipePesanan'])->name('pilih_tipe');
    Route::get('/konsumen/menu', [OrderController::class, 'pilihMeja'])->name('pilih_meja');
    Route::get('/konsumen/menu-takeaway', [OrderController::class, 'menuTakeaway'])->name('menu_takeaway');
    Route::get('/konsumen/menu-nanti', [OrderController::class, 'menuNanti'])->name('menu_nanti');

    // Tambah Pesanan (Dine-in Guest / Konsumen)
    Route::post('/konsumen/order/add', [OrderController::class, 'tambahPesanan'])->middleware('throttle:30,1');
    Route::post('/order/add', [OrderController::class, 'tambahPesanan'])->middleware('throttle:30,1');

    // Pembatalan Pesanan Sebelum Diproses
    Route::post('/konsumen/order/{id}/cancel', [OrderController::class, 'cancelOrder']);
    Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder']);

    // Checkout & Pembayaran (Pay-First Policy)
    Route::get('/konsumen/checkout/{id_pesanan}', [PaymentController::class, 'checkout'])->name('konsumen.checkout');
    Route::get('/checkout/{id_pesanan}', [PaymentController::class, 'checkout']);
    Route::post('/konsumen/order/{id_pesanan}/simulate-midtrans-pay', [PaymentController::class, 'simulateMidtransPay']);
    Route::post('/order/{id_pesanan}/simulate-midtrans-pay', [PaymentController::class, 'simulateMidtransPay']);

    // Panggil Pelayan (Call Bell) dari Meja
    Route::post('/konsumen/call-bell', [OrderController::class, 'callBell'])->middleware('throttle:5,1');
    Route::post('/call-bell', [OrderController::class, 'callBell'])->middleware('throttle:5,1');

    // Rating & Review (Guest or Registered)
    Route::post('/rating/store', [KonsumenController::class, 'storeRating'])->name('konsumen.rating.store');
    Route::post('/konsumen/rating/store', [KonsumenController::class, 'storeRating']);

    // Live Tracking, Status & E-Receipt Pesanan Tamu
    Route::get('/tracking/{order_token}', [OrderController::class, 'tracking'])->name('order.tracking');
    Route::get('/konsumen/tracking/{order_token}', [OrderController::class, 'tracking']);
    Route::get('/tracking/{order_token}/receipt', [OrderController::class, 'downloadReceipt'])->name('order.receipt');
    Route::get('/api/tracking/{order_token}/status', [OrderController::class, 'getOrderStatus'])->name('order.status.api');
});

// Fallback Route untuk foto profil konsumen (mencegah 404 pada cPanel multi-root)
Route::get('/uploads/profil/{filename}', function ($filename) {
    $cleanName = basename($filename);
    $searchPaths = [
        public_path('uploads/profil/' . $cleanName),
        base_path('public/uploads/profil/' . $cleanName),
        '/home/nadp3189/repositories/master-cafe-pos/public/uploads/profil/' . $cleanName,
        '/home/nadp3189/public_html/mastercafe.nadeak.net/uploads/profil/' . $cleanName,
        '/home/nadp3189/public_html/uploads/profil/' . $cleanName,
    ];

    foreach ($searchPaths as $path) {
        if (file_exists($path)) {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $mimeType = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };
            return response()->file($path, ['Content-Type' => $mimeType]);
        }
    }

    $name = auth()->check() ? auth()->user()->name : 'User';
    return redirect('https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=c08e5c&color=fff&size=120');
})->where('filename', '[a-zA-Z0-9._-]+');


