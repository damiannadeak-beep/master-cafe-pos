<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Semua QR Code Meja - Master Cafe</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .header-action {
            max-width: 900px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 15px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .print-btn {
            background: linear-gradient(135deg, #c08e5c, #986c43);
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
        }
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .qr-card {
            background-color: #fff;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            text-align: center;
            border: 2px solid #c08e5c;
            page-break-inside: avoid;
        }
        .qr-card h1 {
            color: #1a1a1a;
            margin: 0 0 2px 0;
            font-size: 20px;
            letter-spacing: 1.5px;
        }
        .qr-card .cafe-tag {
            color: #c08e5c;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .qr-card h2 {
            margin: 6px 0 10px;
            font-size: 24px;
            color: #1a1a1a;
            font-weight: 800;
        }
        .qr-image {
            margin-bottom: 10px;
            padding: 8px;
            background: #fafafa;
            border-radius: 10px;
            display: inline-block;
            border: 1px solid #eee;
        }
        .qr-image img {
            width: 180px;
            height: 180px;
            display: block;
        }
        .footer-text {
            font-size: 11px;
            color: #555;
            line-height: 1.4;
        }
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .header-action {
                display: none;
            }
            .grid-container {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .qr-card {
                box-shadow: none;
                border: 2px solid #000;
            }
        }
    </style>
</head>
<body>

    <div class="header-action">
        <div>
            <h3 style="margin: 0; color: #1a1a1a;">Lembar Cetak QR Code Meja</h3>
            <p style="margin: 4px 0 0; color: #666; font-size: 13px;">Total {{ $mejas->count() }} Meja siap dicetak untuk akrilik meja.</p>
        </div>
        <button class="print-btn" onclick="window.print()">🖨️ Cetak Semua Halaman</button>
    </div>

    <div class="grid-container">
        @foreach($mejas as $meja)
            <div class="qr-card">
                <h1>MASTER CAFE</h1>
                <div class="cafe-tag">Self-Service Dine-In</div>
                <h2>{{ $meja->nama_meja_atau_nomor }}</h2>
                
                <div class="qr-image">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($meja->qr_url) }}" alt="QR Code {{ $meja->nama_meja_atau_nomor }}">
                </div>
                
                <div class="footer-text">
                    <strong>Scan untuk Pesan</strong><br>
                    Pesan makanan & minuman langsung ke meja ini!
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
