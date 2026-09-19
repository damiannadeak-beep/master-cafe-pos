@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0 d-print-none">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold text-accent">
                <i class="bi bi-calendar-check me-2"></i>Laporan Tutup Shift
            </h4>
            <div>
                <a href="{{ route('kasir.shift_report.excel') }}" class="btn btn-success rounded-pill me-2 font-sans fw-bold btn-touch">
                    <i class="bi bi-file-earmark-excel"></i>Export Excel
                </a>
                <a href="{{ route('kasir.shift_report.pdf') }}" class="btn btn-danger rounded-pill me-2 font-sans fw-bold btn-touch">
                    <i class="bi bi-file-pdf"></i>Export PDF
                </a>
                <button onclick="window.print()" class="btn btn-primary rounded-pill font-sans fw-bold btn-touch">
                    <i class="bi bi-printer"></i>Cetak Browser
                </button>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px; background: #e8f5e9; color: #2e7d32;">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <h6 class="text-white-50 mb-2">Total Tunai (Cash in Drawer)</h6>
                    <h3 class="fw-bold mb-0 text-success">Rp {{ number_format($totalCash, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3" style="width: 60px; height: 60px; background: #e3f2fd; color: #1565c0;">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                    <h6 class="text-white-50 mb-2">Total QRIS</h6>
                    <h3 class="fw-bold mb-0 text-primary">Rp {{ number_format($totalQris, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4 gradient-card-primary">
                <div class="card-body p-4 text-center">
                    <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3 bg-transparent icon-accent" style="width: 60px; height: 60px;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <h6 class="mb-2 text-white-50">Total Penjualan Shift Ini</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalSemua, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction List (Web View Only) -->
    <div class="card shadow-sm border-0 mt-4 rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Daftar Transaksi Selesai Hari Ini</h5>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Waktu</th>
                            <th>No. Pesanan</th>
                            <th>Tipe</th>
                            <th>Metode</th>
                            <th class="text-end">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $p)
                        <tr>
                            <td>{{ $p->tanggal ? \Carbon\Carbon::parse($p->tanggal)->format('H:i') : '-' }}</td>
                            <td><span class="badge bg-secondary">#{{ str_pad($p->id_pesanan, 4, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $p->pesanan->tipe_pesanan)) }}</td>
                            <td>
                                @if($p->metode == 'cash')
                                    <span class="badge bg-success">Tunai</span>
                                @elseif($p->metode == 'qris')
                                    <span class="badge bg-primary">QRIS</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end fw-bold">Rp {{ number_format($p->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-white-50">Belum ada transaksi selesai di shift ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Layout Print Browser -->
<div class="d-none d-print-block print-area" style="background: #fff; color: #111;">
    <!-- KOP Header Cetak -->
    <div class="d-flex justify-content-between align-items-start border-bottom border-2 border-dark pb-2 mb-3">
        <div>
            <h3 class="fw-bold mb-0 text-dark" style="letter-spacing: 0.5px;">MASTER CAFE POS</h3>
            <div class="small text-muted">Laporan Rekonsiliasi & Penutupan Shift Kasir</div>
        </div>
        <div class="text-end small text-dark">
            <div><strong>Kasir:</strong> {{ auth()->user()->name }}</div>
            <div><strong>Tanggal Shift:</strong> {{ $shift->waktu_buka ? $shift->waktu_buka->translatedFormat('d F Y') : date('d/m/Y') }}</div>
            <div><strong>Dicetak:</strong> {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
        </div>
    </div>

    <!-- 1. Ringkasan Kas & Rekonsiliasi Shift -->
    <h6 class="fw-bold text-dark text-uppercase border-bottom border-dark pb-1 mb-2">1. Ringkasan Kas & Rekonsiliasi Shift</h6>
    <table class="table table-bordered border-secondary table-sm mb-3 table-print" style="font-size: 8.5pt;">
        <tbody>
            <tr>
                <td style="width: 25%; background-color: #f8f9fa; font-weight: bold;">Waktu Buka Shift:</td>
                <td style="width: 25%;">{{ $shift->waktu_buka ? $shift->waktu_buka->format('d/m/Y H:i') : '-' }} WIB</td>
                <td style="width: 25%; background-color: #f8f9fa; font-weight: bold;">Modal Awal Kas:</td>
                <td style="width: 25%; text-align: right; font-weight: bold;">Rp {{ number_format($shift->modal_awal ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Waktu Tutup Shift:</td>
                <td>{{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') . ' WIB' : 'Shift Masih Berjalan (Open)' }}</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pemasukan Tunai:</td>
                <td style="text-align: right; font-weight: bold; color: #0d6832;">Rp {{ number_format($totalCash, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Status Shift:</td>
                <td><span class="badge bg-secondary">{{ strtoupper($shift->status) }}</span></td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pemasukan QRIS:</td>
                <td style="text-align: right; font-weight: bold; color: #0b5ed7;">Rp {{ number_format($totalQris, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Total Transaksi:</td>
                <td>{{ $pembayarans->count() }} Transaksi</td>
                <td style="background-color: #e9ecef; font-weight: bold;">TOTAL PENJUALAN:</td>
                <td style="text-align: right; font-weight: bold; background-color: #e9ecef;">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Pengeluaran Kasir:</td>
                <td style="color: #dc3545; font-weight: bold;">Rp {{ number_format($totalPengeluaran ?? ($shift->total_pengeluaran ?? 0), 0, ',', '.') }}</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Uang Fisik Aktual:</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($shift->uang_fisik_aktual ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="background-color: #f8f9fa; font-weight: bold;">Total Item Terjual:</td>
                <td>{{ $totalItemTerjual }} porsi / minuman</td>
                <td style="background-color: #f8f9fa; font-weight: bold;">Selisih Fisik Kas:</td>
                <td style="text-align: right; font-weight: bold; color: {{ ($shift->selisih ?? 0) < 0 ? '#dc3545' : (($shift->selisih ?? 0) > 0 ? '#0d6832' : '#000') }};">
                    Rp {{ number_format($shift->selisih ?? 0, 0, ',', '.') }}
                    @if(($shift->selisih ?? 0) == 0) (Pas) @elseif(($shift->selisih ?? 0) < 0) (Kurang) @else (Lebih) @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 2. Rekapitulasi Menu Terjual -->
    <h6 class="fw-bold text-dark text-uppercase border-bottom border-dark pb-1 mb-2">2. Rekapitulasi Menu Terjual</h6>
    <table class="table table-bordered border-secondary table-sm mb-3 table-print" style="font-size: 8.5pt;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="width: 35px;" class="text-center">No</th>
                <th>Menu / Produk</th>
                <th style="width: 90px;" class="text-center">Qty Terjual</th>
                <th style="width: 130px;" class="text-end">Subtotal</th>
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
                <td colspan="4" class="text-center py-2 text-muted">Belum ada item terjual</td>
            </tr>
            @endforelse
            @if($totalItemTerjual > 0)
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="2" class="text-end">TOTAL ITEM TERJUAL:</td>
                <td class="text-center">{{ $totalItemTerjual }}</td>
                <td class="text-end">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row pt-3 text-center" style="page-break-inside: avoid;">
        <div class="col-6">
            <div class="small">Kasir yang bertugas,</div>
            <div style="height: 50px;"></div>
            <div class="fw-bold"><u>{{ auth()->user()->name }}</u></div>
            <div class="small text-muted">Staf Kasir Master Cafe</div>
        </div>
        <div class="col-6">
            <div class="small">Mengetahui / Verifikasi,</div>
            <div style="height: 50px;"></div>
            <div class="fw-bold"><u>( Supervisor / Pemilik )</u></div>
            <div class="small text-muted">Manajemen Master Cafe</div>
        </div>
    </div>
</div>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }
    html, body {
        background: #ffffff !important;
        color: #000000 !important;
        height: auto !important;
        min-height: auto !important;
        overflow: visible !important;
        font-family: Arial, sans-serif !important;
    }
    .kasir-layout, .kasir-main, .kasir-container {
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        display: block !important;
        padding: 0 !important;
        margin: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .kasir-navbar, .kasir-topbar, .offcanvas, .d-print-none, nav, header {
        display: none !important;
    }
    .print-area {
        display: block !important;
        background: #ffffff !important;
        color: #000000 !important;
        padding: 0 !important;
    }
    .table-print {
        width: 100% !important;
        border-collapse: collapse !important;
        color: #000000 !important;
        background: transparent !important;
    }
    .table-print th, .table-print td {
        border: 1px solid #333333 !important;
        color: #000000 !important;
        padding: 4px 8px !important;
        background: transparent !important;
    }
    .table-print th {
        background-color: #f2f2f2 !important;
        font-weight: bold !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endsection


