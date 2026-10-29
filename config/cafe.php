<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master Cafe Business & Operational Settings
    |--------------------------------------------------------------------------
    |
    | Konfigurasi terpusat untuk operasional kafe, batas geofence, waktu
    | kedaluwarsa pesanan, dan koordinat fisik default.
    |
    */

    'name' => env('CAFE_NAME', 'Master Cafe'),

    // Batas waktu kadaluarsa pesanan tamu belum bayar (dalam detik, default 15 menit = 900 detik)
    'order_expiry_seconds' => (int) env('ORDER_EXPIRY_SECONDS', 900),

    // Lokasi GPS Kafe Default (digunakan sebagai fallback jika belum diatur di database)
    'default_location' => [
        'latitude' => (float) env('CAFE_DEFAULT_LATITUDE', 1.4828),
        'longitude' => (float) env('CAFE_DEFAULT_LONGITUDE', 102.1332),
        // Radius toleransi GPS dalam meter untuk absensi & validasi takeaway
        'geofence_radius' => (float) env('CAFE_GEOFENCE_RADIUS', 150),
    ],

    // Pengaturan Kas & Shift Kasir
    'shift' => [
        'default_initial_cash' => (float) env('SHIFT_DEFAULT_INITIAL_CASH', 100000),
    ],
];
