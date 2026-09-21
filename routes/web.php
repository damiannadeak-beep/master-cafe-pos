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
use App\Http\Controllers\AdminWaitressController;
use App\Http\Controllers\AdminPromoController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\KonsumenController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminPengeluaranController;
use App\Http\Controllers\AdminPengeluaranBisnisController;
use App\Http\Controllers\AdminMejaController;
use App\Http\Controllers\WaitressPengeluaranController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\WaitressMejaController;

use App\Http\Controllers\SeoController;
use App\Http\Controllers\SystemController;

// ================= AREA PUBLIK =================
// Halaman yang bisa diakses tanpa perlu login
Route::get('/', [PublicController::class, 'home']);
Route::get('/katalog', [PublicController::class, 'katalog']);
Route::get('/lokasi', [PublicController::class, 'lokasi']);
Route::get('/kontak', [PublicController::class, 'kontak']);

// Route SEO untuk Google Search & Web Crawler
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);



// Route Pembaca Gambar Fail-Safe (Ganti Symlink di Hosting cPanel)
Route::get('/storage/{path}', [\App\Http\Controllers\StorageFallbackController::class, 'show'])
    ->where('path', '.*')
    ->name('storage.fallback');



use App\Http\Controllers\Auth\OwnerLoginController;
use App\Http\Controllers\Auth\WaitressLoginController;
use App\Http\Middleware\VerifySecretOwnerAccess;

$ownerSlug = config('auth.owner_path', 'ruang-owner-x92k');
$kasirSlug = config('auth.kasir_path', 'pos-kasir-gate-88');

// ================= JALUR RAHASIA PEMILIK (OWNER) =================
Route::prefix($ownerSlug)->middleware(['web', VerifySecretOwnerAccess::class])->group(function () {
    Route::get('/login', [OwnerLoginController::class, 'showLoginForm'])->middleware('throttle:60,1')->name('owner.login');
    Route::post('/login', [OwnerLoginController::class, 'login'])->middleware('throttle:10,1')->name('owner.login.submit');
});

// ================= JALUR RAHASIA WAITRESS (POS) =================
Route::prefix($kasirSlug)->middleware(['web'])->group(function () {
    Route::get('/login', [WaitressLoginController::class, 'showLoginForm'])->middleware('throttle:60,1')->name('kasir.login');
    Route::post('/login', [WaitressLoginController::class, 'login'])->middleware('throttle:10,1')->name('kasir.login.submit');
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
        Route::post('/settings/geofence', [AdminController::class, 'updateGeofenceSettings'])->name('settings.geofence');
        Route::post('/settings/lokasi', [AdminController::class, 'updateLokasiSettings'])->name('settings.lokasi');
        Route::post('/settings/kontak', [AdminController::class, 'updateKontakSettings'])->name('settings.kontak');
        Route::get('/reviews', [AdminController::class, 'reviews'])->name('reviews.index');
        Route::post('/reviews/{id}/reply', [AdminController::class, 'replyReview'])->name('reviews.reply');
        
        Route::get('/backup', [AdminController::class, 'backupDatabase'])->name('backup');

        // Route Bantuan (Clear Cache — hanya pemilik yang boleh)
        Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear_cache');
        
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

        // Pengeluaran Kasir (Read-only Audit Kasir)
        Route::get('/pengeluaran', [AdminPengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::delete('/pengeluaran/{id}', [AdminPengeluaranController::class, 'destroy'])->name('pengeluaran.destroy');

        // Pengeluaran Bisnis / Usaha (Owner)
        Route::get('/pengeluaran-bisnis', [AdminPengeluaranBisnisController::class, 'index'])->name('pengeluaran_bisnis.index');
        Route::post('/pengeluaran-bisnis', [AdminPengeluaranBisnisController::class, 'store'])->name('pengeluaran_bisnis.store');
        Route::put('/pengeluaran-bisnis/{id}', [AdminPengeluaranBisnisController::class, 'update'])->name('pengeluaran_bisnis.update');
        Route::delete('/pengeluaran-bisnis/{id}', [AdminPengeluaranBisnisController::class, 'destroy'])->name('pengeluaran_bisnis.destroy');

        // Meja
        Route::get('/meja', [AdminMejaController::class, 'index'])->name('meja.index');
        Route::post('/meja', [AdminMejaController::class, 'store'])->name('meja.store');
        Route::put('/meja/{id}', [AdminMejaController::class, 'update'])->name('meja.update');
        Route::delete('/meja/{id}', [AdminMejaController::class, 'destroy'])->name('meja.destroy');
        Route::get('/meja/{id}/qr', [AdminMejaController::class, 'printQr'])->name('meja.print_qr');

        // Waitress management
        Route::get('/kasir/manage', [AdminWaitressController::class, 'index'])->name('kasir.index');
        Route::post('/kasir', [AdminWaitressController::class, 'store'])->name('kasir.store');
        Route::get('/kasir/{id}/edit', [AdminWaitressController::class, 'edit'])->name('kasir.edit');
        Route::put('/kasir/{id}', [AdminWaitressController::class, 'update'])->name('kasir.update');
        Route::delete('/kasir/{id}', [AdminWaitressController::class, 'destroy'])->name('kasir.destroy');

        // Promo management
        Route::get('/promo', [AdminPromoController::class, 'index'])->name('promo.index');
        Route::get('/promo/create', [AdminPromoController::class, 'create'])->name('promo.create');
        Route::post('/promo', [AdminPromoController::class, 'store'])->name('promo.store');
        Route::get('/promo/{id}/edit', [AdminPromoController::class, 'edit'])->name('promo.edit');
        Route::put('/promo/{id}', [AdminPromoController::class, 'update'])->name('promo.update');
        Route::patch('/promo/{id}/toggle-status', [AdminPromoController::class, 'toggleStatus'])->name('promo.toggle_status');
        Route::delete('/promo/{id}', [AdminPromoController::class, 'destroy'])->name('promo.destroy');
        // User management
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    });

    // Role: Waitress / Kasir
    Route::middleware(['role:kasir'])->prefix('kasir')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('kasir.pos');
        Route::get('/pesanan-aktif', [PosController::class, 'pesananAktif'])->name('kasir.pesanan_aktif');
        Route::post('/manual-order', [PosController::class, 'storeManualOrder']);
        Route::put('/order/{id_pesanan}/status', [PosController::class, 'updateOrderStatus']);
        Route::put('/order/item/{id_detail}/toggle-served', [PosController::class, 'toggleItemServed'])->name('kasir.order.item.toggle_served');
        Route::put('/order/{id_pesanan}/pay', [PosController::class, 'payOrder']);
        Route::post('/order/{id_pesanan}/snap-token', [PosController::class, 'getKasirSnapToken'])->name('kasir.order.snap_token');
        Route::post('/order/{id_pesanan}/qris-success', [PosController::class, 'markQrisPaid'])->name('kasir.order.qris_success');
        Route::put('/order/{id_pesanan}/verify-payment', [PosController::class, 'verifyPayment']);
        Route::put('/order/{id_pesanan}/reject-payment', [PosController::class, 'rejectPayment']);
        Route::put('/order/{id_pesanan}/void', [PosController::class, 'voidOrder'])->name('kasir.order.void');
        Route::post('/order/{id_pesanan}/split', [PosController::class, 'splitOrder'])->name('kasir.order.split');
        Route::get('/order/{id}/receipt', [PosController::class, 'printReceipt'])->name('kasir.order.receipt');
        Route::post('/order/{id}/print-thermal', [PosController::class, 'printThermalReceipt'])->name('kasir.order.thermal');
        Route::get('/order/{id}/kitchen-receipt', [PosController::class, 'printKitchenReceipt'])->name('kasir.order.kitchen');
        Route::get('/shift-report', [\App\Http\Controllers\ShiftController::class, 'shiftReport'])->name('kasir.shift_report');
        Route::get('/shift-report/pdf', [\App\Http\Controllers\ShiftController::class, 'exportShiftReportPdf'])->name('kasir.shift_report.pdf');
        Route::get('/shift-report/excel', [\App\Http\Controllers\ShiftController::class, 'exportShiftReportExcel'])->name('kasir.shift_report.excel');
        Route::get('/api/active-orders-count', [PosController::class, 'activeOrdersCount'])->name('kasir.active_orders_count');
        Route::get('/api/notifications', [PosController::class, 'getNotifications']);
        Route::post('/api/notifications/{id}/read', [PosController::class, 'readNotification']);

        // Pengeluaran Waitress
        Route::get('/pengeluaran', [WaitressPengeluaranController::class, 'index'])->name('kasir.pengeluaran.index');
        Route::post('/pengeluaran', [WaitressPengeluaranController::class, 'store'])->name('kasir.pengeluaran.store');

        // Manajemen Meja Waitress
        Route::get('/meja', [WaitressMejaController::class, 'index'])->name('kasir.meja.index');
        Route::put('/meja/{id}/toggle', [WaitressMejaController::class, 'toggle'])->name('kasir.meja.toggle');
        Route::post('/meja/pindah', [WaitressMejaController::class, 'pindahMeja'])->name('kasir.meja.pindah');
        Route::post('/meja/gabung', [WaitressMejaController::class, 'gabungMeja'])->name('kasir.meja.gabung');

        // Ketersediaan Menu Waitress
        Route::get('/stok', [\App\Http\Controllers\WaitressStokController::class, 'index'])->name('kasir.stok.index');
        Route::post('/stok', [\App\Http\Controllers\WaitressStokController::class, 'update'])->name('kasir.stok.update');
        Route::put('/stok/{id}/toggle', [\App\Http\Controllers\WaitressStokController::class, 'toggle'])->name('kasir.stok.toggle');

        // Absensi Geolocation
        Route::get('/absensi', [\App\Http\Controllers\AbsensiController::class, 'index'])->name('kasir.absensi.index');
        Route::post('/absensi', [\App\Http\Controllers\AbsensiController::class, 'store'])->name('kasir.absensi.store');

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
    Route::get('/konsumen/menu/{id_meja?}', [OrderController::class, 'showMenu'])->name('konsumen.menu.meja')->middleware($tableMenuMiddleware);
    Route::get('/menu/{id_meja?}', [OrderController::class, 'showMenu'])->middleware($tableMenuMiddleware);
    
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

    // Checkout & Pembayaran
    Route::get('/konsumen/checkout/{id_pesanan}', [PaymentController::class, 'checkout'])->name('konsumen.checkout');
    Route::get('/checkout/{id_pesanan}', [PaymentController::class, 'checkout']);
    Route::post('/konsumen/order/{id_pesanan}/simulate-midtrans-pay', [PaymentController::class, 'simulateMidtransPay']);
    Route::post('/order/{id_pesanan}/simulate-midtrans-pay', [PaymentController::class, 'simulateMidtransPay']);
    Route::post('/konsumen/order/{id_pesanan}/choose-cash', [PaymentController::class, 'chooseCashPay']);
    Route::post('/order/{id_pesanan}/choose-cash', [PaymentController::class, 'chooseCashPay']);
    Route::post('/konsumen/order/{id_pesanan}/upload-bukti', [PaymentController::class, 'uploadBukti'])->name('konsumen.upload_bukti');
    Route::post('/order/{id_pesanan}/upload-bukti', [PaymentController::class, 'uploadBukti']);

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

    // Selesai Sesi Tamu / Konsumen (Clear session & redirect)
    Route::get('/konsumen/selesai-sesi', [SystemController::class, 'selesaiSesi'])->name('konsumen.selesai_sesi');
    Route::get('/selesai-sesi', [SystemController::class, 'selesaiSesi']);
});

// Fallback Route untuk foto profil konsumen (mencegah 404 pada cPanel multi-root)
Route::get('/uploads/profil/{filename}', [SystemController::class, 'profileImageFallback'])
    ->where('filename', '[a-zA-Z0-9._-]+');


