<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Shift Kasir - {{ $hariIni ?? date('Y-m-d') }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #111;
            line-height: 1.35;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #222;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .brand-title {
            font-size: 16pt;
            font-weight: bold;
            color: #000;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .brand-subtitle {
            font-size: 9.5pt;
            color: #444;
            margin-top: 3px;
        }
        .meta-text {
            text-align: right;
            font-size: 8.5pt;
            color: #333;
        }
        .section-heading {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #111;
            border-bottom: 1px solid #444;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        table.data-table th {
            background-color: #f2f2f2;
            color: #000;
            font-weight: bold;
            padding: 6px 8px;
            border: 1px solid #999;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 8px;
            border: 1px solid #ccc;
            vertical-align: top;
        }
        .text-end {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .fw-bold {
            font-weight: bold;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .summary-box td {
            padding: 5px 8px;
            border: 1px solid #ccc;
            font-size: 9pt;
        }
        .summary-label {
            background-color: #f9f9f9;
            width: 32%;
            font-weight: bold;
            color: #333;
        }
        .summary-val {
            text-align: right;
            font-weight: bold;
        }
        .badge-status {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
            background: #e0e0e0;
        }
        .footer-sign {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .footer-sign td {
            width: 50%;
            text-align: center;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <!-- Header KOP -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand-title">MASTER CAFE POS</div>
                <div class="brand-subtitle">Laporan Rekonsiliasi & Penutupan Shift Kasir</div>
            </td>
            <td class="meta-text" style="vertical-align: top;">
                <div><strong>Staf Kasir:</strong> {{ auth()->user()->name }}</div>
                <div><strong>Tanggal Shift:</strong> {{ $shift->waktu_buka ? $shift->waktu_buka->translatedFormat('d F Y') : date('d/m/Y') }}</div>
                <div><strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Shift -->
    <div class="section-heading">1. Ringkasan Kas & Rekonsiliasi Shift</div>
    <table class="summary-box">
        <tr>
            <td class="summary-label">Waktu Buka Shift:</td>
            <td>{{ $shift->waktu_buka ? $shift->waktu_buka->format('d/m/Y H:i') : '-' }} WIB</td>
            <td class="summary-label">Modal Awal Kas:</td>
            <td class="summary-val">Rp {{ number_format($shift->modal_awal ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Waktu Tutup Shift:</td>
            <td>{{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') . ' WIB' : 'Shift Masih Berjalan (Open)' }}</td>
            <td class="summary-label">Pemasukan Tunai:</td>
            <td class="summary-val" style="color: #0d6832;">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Status Shift:</td>
            <td><span class="badge-status">{{ strtoupper($shift->status) }}</span></td>
            <td class="summary-label">Pemasukan QRIS:</td>
            <td class="summary-val" style="color: #0b5ed7;">Rp {{ number_format($totalQris, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Total Transaksi Selesai:</td>
            <td>{{ $pembayarans->count() }} Transaksi</td>
            <td class="summary-label" style="background-color: #eee;">TOTAL PENJUALAN SHIFT:</td>
            <td class="summary-val" style="background-color: #eee; font-size: 10pt;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Pengeluaran Kasir (Shift):</td>
            <td style="color: #dc3545; font-weight: bold;">Rp {{ number_format($shift->total_pengeluaran ?? 0, 0, ',', '.') }}</td>
            <td class="summary-label">Uang Fisik Aktual:</td>
            <td class="summary-val">Rp {{ number_format($shift->uang_fisik_aktual ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="summary-label">Total Item Terjual:</td>
            <td>{{ $totalItemTerjual }} porsi / minuman</td>
            <td class="summary-label">Selisih Fisik Kas:</td>
            <td class="summary-val" style="color: {{ ($shift->selisih ?? 0) < 0 ? '#dc3545' : (($shift->selisih ?? 0) > 0 ? '#0d6832' : '#000') }};">
                Rp {{ number_format($shift->selisih ?? 0, 0, ',', '.') }}
                @if(($shift->selisih ?? 0) == 0) (Uang Pas) @elseif(($shift->selisih ?? 0) < 0) (Kurang) @else (Lebih) @endif
            </td>
        </tr>
    </table>

    <!-- Rekapitulasi Menu Terjual -->
    <div class="section-heading">2. Rekapitulasi Menu Terjual Selama Shift</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Nama Menu / Produk</th>
                <th style="width: 80px;" class="text-center">Qty Terjual</th>
                <th style="width: 120px;" class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($rekapMenu as $nama => $data)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>{{ $nama }}</td>
                <td class="text-center fw-bold">{{ $data['jumlah'] }}</td>
                <td class="text-end">Rp {{ number_format($data['subtotal'], 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center" style="padding: 10px; color: #888;">Belum ada item terjual pada shift ini</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="2" class="text-end">TOTAL KESELURUHAN ITEM TERJUAL:</td>
                <td class="text-center">{{ $totalItemTerjual }}</td>
                <td class="text-end">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Daftar Transaksi -->
    <div class="section-heading">3. Rincian Riwayat Transaksi Shift</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th style="width: 70px;">Waktu</th>
                <th style="width: 75px;">No. Order</th>
                <th>Tipe Pesanan</th>
                <th style="width: 80px;" class="text-center">Metode</th>
                <th style="width: 110px;" class="text-end">Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            @php $tNo = 1; @endphp
            @forelse($pembayarans as $p)
            <tr>
                <td class="text-center">{{ $tNo++ }}</td>
                <td>{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('H:i') : '-' }} WIB</td>
                <td><strong>#{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }}</strong></td>
                <td>{{ ucfirst(str_replace('_', ' ', $p->pesanan->tipe_pesanan ?? 'Dine In')) }}</td>
                <td class="text-center">
                    @if($p->metode == 'cash')
                        Tunai
                    @elseif($p->metode == 'qris')
                        QRIS
                    @else
                        {{ strtoupper($p->metode ?? '-') }}
                    @endif
                </td>
                <td class="text-end fw-bold">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 10px; color: #888;">Belum ada transaksi selesai pada shift ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="footer-sign">
        <tr>
            <td>
                <div>Kasir yang bertugas,</div>
                <div style="height: 45px;"></div>
                <div class="fw-bold"><u>{{ auth()->user()->name }}</u></div>
                <div style="font-size: 8pt; color: #666;">Staf Kasir Master Cafe</div>
            </td>
            <td>
                <div>Mengetahui / Verifikasi,</div>
                <div style="height: 45px;"></div>
                <div class="fw-bold"><u>( Supervisor / Pemilik )</u></div>
                <div style="font-size: 8pt; color: #666;">Manajemen Master Cafe</div>
            </td>
        </tr>
    </table>
</body>
</html>
