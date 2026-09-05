<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan & Bisnis - Master Cafe</title>
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
        .periode-badge {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 16px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
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
                <h1 class="brand-title">MASTER CAFE</h1>
                <div class="brand-subtitle">Sistem Kasir & Operasional Cafe Modern</div>
            </td>
            <td style="border: none; padding: 0; text-align: right;" class="report-meta">
                <strong>LAPORAN KEUANGAN & BISNIS</strong><br>
                Dicetak: {{ now()->translatedFormat('d F Y H:i') }}<br>
                Oleh Staf: {{ auth()->user()->name }}
            </td>
        </tr>
    </table>

    <div class="periode-badge">
        Periode Laporan: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
    </div>

    <!-- 1. KPI SUMMARY GRID -->
    <table class="kpi-grid">
        <tr>
            <td width="20%">
                <div class="kpi-label">Pendapatan Omzet</div>
                <div class="kpi-value">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div class="kpi-label">Modal Bahan (HPP)</div>
                <div class="kpi-value">Rp {{ number_format($totalHpp, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div class="kpi-label">Laba Kotor</div>
                <div class="kpi-value">Rp {{ number_format($labaKotor, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div class="kpi-label">Pengeluaran</div>
                <div class="kpi-value">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div class="kpi-label">Laba Bersih</div>
                <div class="kpi-value">Rp {{ number_format($labaBersih, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. MENU TERLARIS -->
    <div class="section-title">1. REKAP MENU TERLARIS (TOP 10)</div>
    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">No</th>
                <th>Nama Menu / Produk</th>
                <th width="25%" class="text-right">Total Terjual</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bestSeller as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->nama_menu }}</td>
                <td class="text-right fw-bold">{{ number_format($item->total_terjual, 0, ',', '.') }} porsi</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada data penjualan pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 3. KINERJA KASIR -->
    <div class="section-title">2. KINERJA & PENJUALAN STAF KASIR</div>
    <table>
        <thead>
            <tr>
                <th width="8%" class="text-center">No</th>
                <th>Nama Staf Kasir</th>
                <th width="18%" class="text-center">Shift Kerja</th>
                <th width="20%" class="text-center">Jumlah Transaksi</th>
                <th width="25%" class="text-right">Total Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kasirPerformance as $index => $kasir)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="fw-bold">{{ $kasir->name }}</td>
                <td class="text-center">{{ ucfirst($kasir->shift) }}</td>
                <td class="text-center">{{ $kasir->total_transaksi }} Transaksi</td>
                <td class="text-right fw-bold">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center" style="color: #888;">Belum ada data aktivitas kasir.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 4. PENGGUNAAN STOK BAHAN BAKU -->
    <div class="section-title">3. REKAP PENGGUNAAN STOK BAHAN BAKU</div>
    <table>
        <thead>
            <tr>
                <th width="10%" class="text-center">No</th>
                <th>Nama Bahan Baku</th>
                <th width="30%" class="text-right">Total Terpakai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stockUsage as $index => $stok)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stok->nama_bahan }}</td>
                <td class="text-right fw-bold">{{ $stok->total_penggunaan }} {{ $stok->satuan }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada penggunaan bahan baku.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- 5. METODE PEMBAYARAN -->
    <div class="section-title">4. RINGKASAN METODE PEMBAYARAN</div>
    <table>
        <thead>
            <tr>
                <th>Metode Pembayaran</th>
                <th width="25%" class="text-center">Total Transaksi</th>
                <th width="30%" class="text-right">Nominal Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentMethods as $pm)
            <tr>
                <td class="fw-bold">{{ strtoupper($pm->metode === 'qris' ? 'TRANSFER BANK / QRIS' : $pm->metode) }}</td>
                <td class="text-center">{{ $pm->total_transaksi }} Transaksi</td>
                <td class="text-right fw-bold">Rp {{ number_format($pm->total, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="text-center" style="color: #888;">Belum ada transaksi pembayaran.</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- FOOTER -->
    <table class="footer-note">
        <tr>
            <td style="border: none; padding: 0;">
                Dokumen resmi komputerisasi Master Cafe - Berlaku tanpa tanda tangan basah.
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                Halaman 1 dari 1
            </td>
        </tr>
    </table>

</body>
</html>