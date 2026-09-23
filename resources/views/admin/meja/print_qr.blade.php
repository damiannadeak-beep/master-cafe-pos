<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - {{ $meja->nama_meja_atau_nomor }}</title>

    <!-- Favicon Resmi Master Cafe -->
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}?v=2" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-16x16.png') }}?v=2" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">

    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .qr-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 350px;
            border: 2px dashed #b34b63;
        }
        .brand-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 5px;
        }
        .brand-logo {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }
        .qr-card h1 {
            color: #b34b63;
            margin: 0;
            font-size: 24px;
            letter-spacing: 1px;
        }
        .qr-card h2 {
            margin: 10px 0 20px;
            font-size: 32px;
            color: #333;
        }
        .qr-image {
            margin-bottom: 20px;
        }
        .qr-image img {
            width: 250px;
            height: 250px;
            display: block;
            margin: 0 auto;
        }
        .footer-text {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }
        .print-btn {
            margin-top: 20px;
            background-color: #b34b63;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #8c3a4d;
        }
        @media print {
            body {
                background-color: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .qr-card {
                box-shadow: none;
                border: 2px solid #000;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="qr-card">
        <div class="brand-header">
            <img src="{{ asset('images/logo.png') }}" alt="Master Cafe Logo" class="brand-logo">
            <h1>MASTER CAFE</h1>
        </div>
        <h2>{{ $meja->nama_meja_atau_nomor }}</h2>
        
        <div class="qr-image">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($url) }}" alt="QR Code">
        </div>
        
        <div class="footer-text">
            <strong>Scan QR Code</strong><br>
            Untuk melihat menu dan memesan langsung dari HP Anda.
        </div>
        
        <button class="print-btn" onclick="window.print()">Cetak (Print)</button>
    </div>

</body>
</html>
