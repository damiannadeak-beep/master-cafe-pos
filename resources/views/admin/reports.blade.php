@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Pusat Laporan & Audit</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Finansial & Operasional)</span>
            </div>
            <p class="text-secondary small mb-0">Pantau ringkasan omset penjualan, kehadiran kasir, dan audit rekonsiliasi kasir.</p>
        </div>
    </div>

    <!-- Segmented Control Tab Navigasi -->
    <div class="d-flex align-items-center gap-1.5 p-1 rounded-3 mb-4 overflow-auto" style="background-color: #161b22; border: 1px solid #21262d; width: fit-content; max-width: 100%;">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap" style="background: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3);">
            <i class="bi bi-bar-chart-fill me-1.5"></i> Laporan Penjualan
        </a>
        <a href="{{ route('admin.absensi.index') }}" class="btn btn-sm text-secondary fw-medium px-3 py-1.5 rounded-2 text-nowrap" style="border: 1px solid transparent;">
            <i class="bi bi-calendar-check me-1.5"></i> Absensi Waitress
        </a>
        <a href="{{ route('admin.void_logs.index') }}" class="btn btn-sm text-secondary fw-medium px-3 py-1.5 rounded-2 text-nowrap" style="border: 1px solid transparent;">
            <i class="bi bi-journal-x me-1.5"></i> Audit Void Waitress
        </a>
    </div>

    <!-- Filter Rentang Waktu & Aksi Ekspor -->
    <div class="card border-0 shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1.5 fw-medium" style="border-radius: 8px; height: 38px;">
                        <i class="bi bi-funnel"></i>
                        <span>Tampilkan Grafik</span>
                    </button>
                    <a href="{{ route('admin.reports.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 fw-medium" style="border-radius: 8px; height: 38px; border-color: #30363d; color: #d0d7de;">
                        <i class="bi bi-file-earmark-excel" style="color: #34d399;"></i>
                        <span>Export Excel</span>
                    </a>
                    <a href="{{ route('admin.reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1.5 fw-medium" target="_blank" style="border-radius: 8px; height: 38px; border-color: #30363d; color: #d0d7de;">
                        <i class="bi bi-file-earmark-pdf" style="color: #f87171;"></i>
                        <span>Cetak PDF</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards Laporan Finansial (Executive Dark P&L) -->
    <div class="row g-3 mb-4">
        <!-- 1. Omzet Penjualan -->
        <div class="col-lg col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-medium d-block mb-1">1. Omzet Penjualan (Gross)</span>
                    <h5 class="fw-bold mb-0 text-white" style="font-size: 1.25rem;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <!-- 2. HPP Bahan Baku -->
        <div class="col-lg col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-medium d-block mb-1">2. HPP Bahan Baku (COGS)</span>
                    <h5 class="fw-bold mb-0 text-secondary" style="font-size: 1.25rem;">- Rp {{ number_format($totalHpp ?? 0, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <!-- 3. Laba Kotor Usaha -->
        <div class="col-lg col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-medium">3. Laba Kotor Usaha</span>
                        <span class="badge rounded-2 px-2 py-0.5" style="background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.68rem;">{{ $grossMarginPercent }}%</span>
                    </div>
                    <h5 class="fw-bold mb-0 text-white" style="font-size: 1.25rem;">Rp {{ number_format($labaKotor, 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>

        <!-- 4. Beban Operasional -->
        <div class="col-lg col-md-6 col-sm-6">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3">
                    <span class="text-secondary small fw-medium d-block mb-1">4. Beban Operasional</span>
                    <h5 class="fw-bold mb-0" style="color: #f87171; font-size: 1.25rem;">- Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h5>
                    <small class="text-secondary opacity-75 d-block mt-0.5" style="font-size: 0.7rem;">Laci: {{ number_format($totalPengeluaranKasir, 0, ',', '.') }} | Bisnis: {{ number_format($totalPengeluaranBisnis, 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <!-- 5. Laba Bersih Riil -->
        <div class="col-lg col-md-6 col-sm-12">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small fw-medium">5. Laba Bersih Riil (Net)</span>
                        <span class="badge rounded-2 px-2 py-0.5" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.68rem;">{{ $netMarginPercent }}%</span>
                    </div>
                    <h5 class="fw-bold mb-0" style="color: {{ ($labaBersih ?? 0) >= 0 ? '#34d399' : '#f87171' }}; font-size: 1.25rem;">
                        Rp {{ number_format($labaBersih, 0, ',', '.') }}
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Penjualan -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-graph-up" style="color: #c08e5c;"></i>
                        <span class="fw-semibold text-white fs-6">Grafik Total Penjualan</span>
                    </div>
                    <span class="text-secondary small">
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    </span>
                </div>
                <div class="card-body p-4">
                    <div style="height: 300px; position: relative;">
                        <canvas id="salesReportChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 2: Menu Terlaris & Metode Pembayaran -->
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-trophy" style="color: #c08e5c;"></i>
                        <span class="fw-semibold text-white fs-6">Menu Terlaris (Top 10)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                            <thead>
                                <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                                    <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 90px;">Rank</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nama Menu</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 150px;">Total Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSeller as $index => $item)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td class="ps-4 py-3">
                                        @if($index == 0)
                                            <span class="badge rounded-2 px-2 py-1" style="background-color: rgba(192, 142, 92, 0.2); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.4);"><i class="bi bi-star-fill me-1"></i> #1</span>
                                        @elseif($index == 1)
                                            <span class="badge rounded-2 px-2 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15);">#2</span>
                                        @elseif($index == 2)
                                            <span class="badge rounded-2 px-2 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15);">#3</span>
                                        @else
                                            <span class="text-secondary ps-2 small">{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 fw-medium text-white">{{ $item->nama_menu }}</td>
                                    <td class="pe-4 py-3 text-end">
                                        <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem;">
                                            {{ $item->total_terjual }} porsi
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-secondary small py-5">Belum ada data penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart" style="color: #c08e5c;"></i>
                        <span class="fw-semibold text-white fs-6">Metode Pembayaran</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center">
                    <div style="height: 250px; width: 100%; position: relative;">
                        <canvas id="paymentMethodChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3: Kinerja Kasir / Waitress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge" style="color: #c08e5c;"></i>
                        <span class="fw-semibold text-white fs-6">Kinerja Waitress per Shift</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                            <thead>
                                <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                                    <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Nama Waitress</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Shift</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px;">Transaksi</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px;">Total Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirPerformance as $kasir)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td class="ps-4 py-3 fw-medium text-white">{{ $kasir->name }}</td>
                                    <td class="px-3 py-3">
                                        @if($kasir->shift == 'pagi')
                                            <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);"><i class="bi bi-brightness-high me-1 text-warning"></i> Pagi</span>
                                        @else
                                            <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);"><i class="bi bi-moon-stars me-1 text-info"></i> Malam</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3 text-center text-white-50">{{ $kasir->total_transaksi }}</td>
                                    <td class="pe-4 py-3 text-end fw-semibold text-white">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-secondary small py-5">Belum ada data staf waitress.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 4: Riwayat Tutup Kasir (Rekonsiliasi) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-safe" style="color: #c08e5c;"></i>
                        <span class="fw-semibold text-white fs-6">Riwayat Rekonsiliasi Kasir (Buka / Tutup Laci)</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                            <thead>
                                <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                                    <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px;">Waitress</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Waktu Shift</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Modal Awal</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Tunai Sistem</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Uang Fisik</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Selisih Laci</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirShifts as $shift)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                    <td class="ps-4 py-3 fw-medium text-white">{{ $shift->user->name ?? 'Unknown' }}</td>
                                    <td class="px-3 py-3 text-nowrap">
                                        <span class="d-block text-white small">{{ $shift->waktu_buka->format('d/m/Y H:i') }}</span>
                                        <span class="text-secondary small" style="font-size: 0.72rem;">s/d {{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') : 'Sekarang' }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-secondary small">Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 text-secondary small">Rp {{ number_format($shift->total_pemasukan_tunai, 0, ',', '.') }}</td>
                                    <td class="px-3 py-3 fw-medium text-white">
                                        @if($shift->uang_fisik_aktual)
                                            Rp {{ number_format($shift->uang_fisik_aktual, 0, ',', '.') }}
                                        @else
                                            <span class="text-secondary">-</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-3">
                                        @if($shift->status == 'open')
                                            <span class="badge rounded-2 px-2 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">Sedang Berjalan</span>
                                        @else
                                            @if($shift->selisih == 0)
                                                <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25);">
                                                    <i class="bi bi-check-circle me-1"></i> Balance (0)
                                                </span>
                                            @elseif($shift->selisih < 0)
                                                <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);">
                                                    <i class="bi bi-arrow-down me-1"></i> -Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}
                                                </span>
                                            @else
                                                <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                                                    <i class="bi bi-arrow-up me-1"></i> +Rp {{ number_format($shift->selisih, 0, ',', '.') }}
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="pe-4 py-3 text-center">
                                        @if($shift->status == 'open')
                                            <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25);">Open</span>
                                        @else
                                            <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-secondary small py-5">Belum ada riwayat shift untuk periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initReportCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initReportCharts, 50);
            return;
        }

        const labels = @json($chartLabels);
        const data = @json($chartData);

        const ctx = document.getElementById('salesReportChart');
        if (ctx) {
            const existingSales = Chart.getChart(ctx);
            if (existingSales) existingSales.destroy();

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Penjualan',
                        data: data,
                        fill: true,
                        backgroundColor: 'rgba(192, 142, 92, 0.08)',
                        borderColor: '#c08e5c',
                        borderWidth: 2.5,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#c08e5c',
                        pointBorderColor: '#161b22',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#161b22',
                            titleColor: '#ffffff',
                            bodyColor: '#c08e5c',
                            borderColor: '#30363d',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: (context) => 'Penjualan: Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#8b949e', font: { size: 11 } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: {
                                color: '#8b949e',
                                font: { size: 11 },
                                callback: (value) => 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    }
                }
            });
        }

        // Doughnut Chart Metode Pembayaran
        const paymentMethods = @json($paymentMethods);
        const pmLabels = paymentMethods.map(item => item.metode.toUpperCase());
        const pmData = paymentMethods.map(item => item.total);
        
        const pmCtx = document.getElementById('paymentMethodChart');
        if (pmCtx && pmLabels.length > 0) {
            const existingPm = Chart.getChart(pmCtx);
            if (existingPm) existingPm.destroy();

            new Chart(pmCtx, {
                type: 'doughnut',
                data: {
                    labels: pmLabels,
                    datasets: [{
                        data: pmData,
                        backgroundColor: [
                            'rgba(192, 142, 92, 0.85)', // Bronze QRIS
                            'rgba(52, 211, 153, 0.85)', // Emerald Cash
                            'rgba(99, 102, 241, 0.85)'  // Indigo Transfer / Lainnya
                        ],
                        borderColor: '#161b22',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'bottom',
                            labels: {
                                color: '#8b949e',
                                font: { size: 12 },
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            backgroundColor: '#161b22',
                            titleColor: '#ffffff',
                            bodyColor: '#d0d7de',
                            borderColor: '#30363d',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: (context) => ' ' + context.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    }
                }
            });
        } else if (pmCtx) {
            pmCtx.parentElement.innerHTML = '<p class="text-secondary small text-center w-100 mb-0 py-5">Belum ada data pembayaran</p>';
        }
    }

    initReportCharts();
})();
</script>
@endsection
