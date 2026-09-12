<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Dapur - #{{ $order->id }}</title>
    <style>
        /* Gaya Khusus untuk Printer Thermal 58mm */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 10px;
            width: 58mm; /* Lebar standar kertas thermal kecil */
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-weight-bold, .fw-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-1 { margin-top: 5px; }
        .mt-2 { margin-top: 10px; }
        
        .header {
            margin-bottom: 10px;
            border-bottom: 1px dashed #000;
            padding-bottom: 5px;
        }
        .header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 0;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            vertical-align: top;
            padding: 2px 0;
            text-align: left;
        }
        
        .border-bottom, .divider {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
            padding-bottom: 5px;
        }
        
        /* Hilangkan tombol print saat mencetak */
        @media print {
            .no-print { display: none !important; }
        }
        
        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 15px;
            font-weight: bold;
        }
    </style>
</head>
<body onload="window.print()">
    
    <button class="no-print btn-print" onclick="window.print()">Cetak Tiket Sekarang</button>

    <div class="header text-center">
        <h3>STRUK DAPUR</h3>
        <p class="font-weight-bold">PESANAN #{{ $order->id }}</p>
    </div>
    
    <div class="border-bottom">
        <table>
            <tr>
                <td>Tanggal</td>
                <td class="text-right">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td class="text-right font-weight-bold">{{ strtoupper(str_replace('_', ' ', $order->tipe_pesanan)) }}</td>
            </tr>
            <tr>
                <td>Meja</td>
                <td class="text-right font-weight-bold">{{ $order->meja->nama_meja_atau_nomor ?? '-' }}</td>
            </tr>
            @if($order->pembayaran && $order->pembayaran->metode === 'cash')
            <tr>
                <td>Bayar</td>
                <td class="text-right font-weight-bold">TUNAI (CASH)</td>
            </tr>
            @if($order->pembayaran->uang_kembalian > 0)
            <tr>
                <td>Uang Tamu</td>
                <td class="text-right">Rp {{ number_format($order->pembayaran->uang_diterima, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; font-size: 13px;">KEMBALIAN</td>
                <td class="text-right font-weight-bold" style="font-size: 13px;">Rp {{ number_format($order->pembayaran->uang_kembalian, 0, ',', '.') }}</td>
            </tr>
            @elseif($order->pembayaran->uang_diterima)
            <tr>
                <td>Keterangan</td>
                <td class="text-right font-weight-bold">UANG PAS</td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    <div class="border-bottom">
        <table style="margin-top: 5px; margin-bottom: 5px;">
            <thead>
                <tr>
                    <th style="width: 25px;">Qty</th>
                    <th>Item Menu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->detail_pesanan as $item)
                <tr>
                    <td class="font-weight-bold" style="font-size: 14px;">{{ $item->jumlah }}x</td>
                    <td style="font-size: 14px;">
                        {{ $item->menu->nama_menu }}
                        @if($item->selected_variants)
                            @php 
                                $variants = json_decode($item->selected_variants, true); 
                            @endphp
                            @if(is_array($variants) && count($variants) > 0)
                                <br><small style="font-size: 11px;">- 
                                    @foreach($variants as $idx => $v)
                                        {{ isset($v['qty']) && $v['qty'] > 1 ? $v['qty'].'x ' : '' }}{{ $v['name'] }}{{ $idx < count($variants) - 1 ? ', ' : '' }}
                                    @endforeach
                                </small>
                            @endif
                        @endif
                        @if($item->catatan)
                            <br><small style="font-size: 11px; font-style: italic;">* {{ $item->catatan }}</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-center mt-2">
        <p class="font-weight-bold" style="font-size: 15px; margin-top: 10px;">-- SEGERA SIAPKAN --</p>
    </div>
</body>
</html>
