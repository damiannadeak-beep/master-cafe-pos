<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Pertanyaan Wawancara - Master Cafe Bengkalis</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 40px; }
        h2 { text-align: center; margin-bottom: 5px; color: #333; }
        p.subtitle { text-align: center; margin-bottom: 40px; color: #666; font-size: 14px;}
        .question-box { margin-bottom: 25px; padding: 15px; border-bottom: 1px solid #eee; }
        .q-number { font-weight: bold; font-size: 16px; color: #d97706; margin-bottom: 5px; }
        .q-text { font-size: 15px; color: #333; }
    </style>
</head>
<body>
    <h2>Daftar Pertanyaan Wawancara</h2>
    <p class="subtitle">Studi Kasus: Pemilik Master Cafe Bengkalis</p>
    
    <div class="question-box">
        <div class="q-number">Pertanyaan 1: Kendala Operasional Harian</div>
        <div class="q-text">Apa kendala terbesar yang paling sering mengganggu kelancaran pesanan dari meja pelanggan hingga ke dapur?</div>
    </div>

    <div class="question-box">
        <div class="q-number">Pertanyaan 2: Keuangan & Kasir (Shift)</div>
        <div class="q-text">Apakah pernah atau sering terjadi selisih antara total catatan penjualan dengan uang fisik yang ada di laci kasir saat tutup warung?</div>
    </div>

    <div class="question-box">
        <div class="q-number">Pertanyaan 3: Pemantauan Stok Bahan Baku</div>
        <div class="q-text">Bagaimana cara Bapak/Ibu mengecek sisa bahan baku? Apakah karyawan pernah lupa mencatat sehingga bahan tiba-tiba habis saat jam sibuk?</div>
    </div>

    <div class="question-box">
        <div class="q-number">Pertanyaan 4: Efisiensi Pembayaran</div>
        <div class="q-text">Bagaimana cara kasir mencatat dan memverifikasi pembayaran digital (seperti QRIS atau transfer)? Apakah prosesnya sudah cepat atau masih memakan waktu?</div>
    </div>

    <div class="question-box">
        <div class="q-number">Pertanyaan 5: Solusi Pemesanan Mandiri</div>
        <div class="q-text">Jika ada sistem di mana pelanggan bisa memesan dan langsung membayar sendiri dari meja (via Scan QR), apakah menurut Bapak/Ibu itu akan sangat membantu?</div>
    </div>

    <div class="question-box">
        <div class="q-number">Pertanyaan 6: Harapan Terhadap Sistem Baru</div>
        <div class="q-text">Fitur atau jenis laporan seperti apa yang paling Bapak/Ibu butuhkan agar pengelolaan Master Cafe menjadi jauh lebih mudah ke depannya?</div>
    </div>
</body>
</html>
';

$pdf = Pdf::loadHTML($html);
$pdf->setPaper('a4', 'portrait');
$pdf->save(public_path('Pertanyaan_Wawancara_Master_Cafe.pdf'));
echo "PDF saved at: " . public_path('Pertanyaan_Wawancara_Master_Cafe.pdf');
