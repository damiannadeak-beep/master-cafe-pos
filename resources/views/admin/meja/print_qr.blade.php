<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - {{ $meja->nama_meja_atau_nomor }}</title>
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
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
            max-width: 360px;
            border: 3px solid #c08e5c;
        }
        .qr-card h1 {
            color: #1a1a1a;
            margin-top: 0;
            margin-bottom: 4px;
            font-size: 24px;
            letter-spacing: 2px;
        }
        .qr-card .cafe-tag {
            color: #c08e5c;
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        .qr-card h2 {
            margin: 10px 0 15px;
            font-size: 30px;
            color: #1a1a1a;
            font-weight: 800;
        }
        .qr-image {
            margin-bottom: 15px;
            padding: 10px;
            background: #fafafa;
            border-radius: 12px;
            display: inline-block;
            border: 1px solid #eee;
        }
        .qr-image img {
            width: 230px;
            height: 230px;
            display: block;
        }
        .footer-text {
            font-size: 13px;
            color: #555;
            line-height: 1.5;
        }
        .print-btn {
            margin-top: 20px;
            background: linear-gradient(135deg, #c08e5c, #986c43);
            color: white;
            border: none;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: bold;
            border-radius: 25px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(192, 142, 92, 0.3);
        }
        .print-btn:hover {
            opacity: 0.9;
        }
        @media print {
            body {
                background-color: #fff;
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
        <h1>MASTER CAFE</h1>
        <div class="cafe-tag">Self-Service Dine-In</div>
        <h2>{{ $meja->nama_meja_atau_nomor }}</h2>
        
        <div class="qr-image">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($url) }}" alt="QR Code">
        </div>
        
        <div class="footer-text">
            <strong>Scan untuk Pesan</strong><br>
            Buka kamera HP Anda, pesan makanan & minuman, dan pesanan akan langsung diantar ke meja ini!
        </div>
        
        <button class="print-btn" onclick="window.print()">Cetak QR Code</button>
    </div>

</body>
</html>
