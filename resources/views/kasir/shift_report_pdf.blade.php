<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Shift Kasir - {{ $hariIni }}</title>
    <style>
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            background: #fff;
        }
        .kop-header {
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
            margin: 0;
            text-transform: uppercase;
        }
        .brand-subtitle {
            font-size: 10pt;
            color: #333;
            margin-top: 4px;
        }
        .report-meta {
            text-align: right;
            font-size: 9pt;
            color: #333;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #000;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10pt;
        }
        th {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }
        td {
            padding: 8px;
            border-bottom: 1px dotted #ccc;
            vertical-align: middle;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        .kpi-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .kpi-grid td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        .kpi-label {
            font-size: 9pt;
            color: #555;
            text-transform: uppercase;
        }
        .kpi-value {
            font-size: 14pt;
            font-weight: bold;
            color: #000;
            margin-top: 5px;
        }
        .footer-note {
            margin-top: 30px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 8pt;
            color: #666;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="kop-header">
        <tr>
            <td style="border: none; padding: 0;">
                <h1 class="brand-title">MASTER CAFE POS</h1>
                <div class="brand-subtitle">Laporan Rekonsiliasi Tutup Shift Kasir</div>
            </td>
            <td style="border: none; padding: 0; text-align: right;" class="report-meta">
                <strong>STAF KASIR: {{ strtoupper(auth()->user()->name) }}</strong><br>
                Tanggal Shift: {{ \Carbon\Carbon::parse($hariIni)->translatedFormat('d F Y') }}<br>
                Dicetak: {{ now()->translatedFormat('H:i') }} WIB
            </td>
        </tr>
    </table>

    <!-- KPI SUMMARY GRID -->
    <table class="kpi-grid">
        <tr>
            <td width="33%">
                <div class="kpi-label">Kas Tunai (Laci Kas)</div>
                <div class="kpi-value">Rp {{ number_format($totalCash, 0, ',', '.') }}</div>
            </td>
            <td width="33%">
                <div class="kpi-label">Non-Tunai (QRIS)</div>
                <div class="kpi-value">Rp {{ number_format($totalQris, 0, ',', '.') }}</div>
            </td>
            <td width="34%">
                <div class="kpi-label">Total Omzet Shift</div>
                <div class="kpi-value">Rp {{ number_format($totalSemua, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- ITEM PENJUALAN -->
    <div class="section-title">Rekap Item Menu Terjual</div>
    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th>Nama Menu / Item</th>
                <th width="20%" class="text-center">Jumlah Terjual</th>
                <th width="25%" class="text-right">Subtotal Penjualan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td class="fw-bold">{{ $nama }}</td>
                <td class="text-center">{{ $data['jumlah'] }} porsi</td>
                <td class="text-right fw-bold">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="color: #888;">Belum ada item terjual pada shift ini.</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="font-weight: bold;">
                <td colspan="2" class="text-right" style="border-top: 1px solid #000; border-bottom: 2px solid #000;">TOTAL ITEM TERJUAL</td>
                <td class="text-center" style="border-top: 1px solid #000; border-bottom: 2px solid #000;">{{ $totalItemTerjual }} porsi</td>
                <td class="text-right" style="border-top: 1px solid #000; border-bottom: 2px solid #000;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- FOOTER NOTE -->
    <table class="footer-note">
        <tr>
            <td style="border: none; padding: 0;">
                Laporan Tutup Shift Kasir sah secara sistem komputerisasi Master Cafe POS.
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                Dicetak pada {{ date('d/m/Y H:i:s') }}
            </td>
        </tr>
    </table>

</body>
</html>
