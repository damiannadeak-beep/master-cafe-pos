@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Pusat Laporan & Audit</h2>
            <p class="text-white-50 mb-0">Pantau ringkasan omset penjualan, kehadiran kasir, dan audit transaksi.</p>
        </div>
    </div>

    <!-- Tab Navigasi Pusat Laporan -->
    <ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.reports.index') }}">
                <i class="bi bi-bar-chart-fill me-1"></i> Laporan Penjualan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white-50" href="{{ route('admin.absensi.index') }}">
                <i class="bi bi-calendar-check-fill me-1"></i> Absensi Waitress
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white-50" href="{{ route('admin.void_logs.index') }}">
                <i class="bi bi-journal-x me-1"></i> Audit Void Waitress
            </a>
        </li>
    </ul>

    <!-- Filter Rentang Waktu -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-6 d-flex gap-2 flex-wrap align-items-end">
                    <button type="submit" class="btn btn-primary">Tampilkan Grafik</button>
                    <a href="{{ route('admin.reports.csv', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                    </a>
                    <a href="{{ route('admin.reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-danger" target="_blank">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Cetak PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header fw-bold">
                    Grafik Total Penjualan ({{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }})
                </div>
                <div class="card-body">
                    <canvas id="salesReportChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 2: Menu Terlaris & Metode Pembayaran -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <i class="bi bi-trophy text-warning me-2"></i> Menu Terlaris (Top 10)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Peringkat</th>
                                    <th>Nama Menu</th>
                                    <th>Total Terjual</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bestSeller as $index => $item)
                                <tr>
                                    <td>
                                        @if($index == 0) <span class="badge bg-warning text-white"><i class="bi bi-star-fill"></i> 1</span>
                                        @elseif($index == 1) <span class="badge bg-secondary"><i class="bi bi-star-fill"></i> 2</span>
                                        @elseif($index == 2) <span class="badge bg-danger"><i class="bi bi-star-fill"></i> 3</span>
                                        @else {{ $index + 1 }} @endif
                                    </td>
                                    <td class="fw-medium">{{ $item->nama_menu }}</td>
                                    <td><span class="badge bg-primary rounded-pill">{{ $item->total_terjual }} porsi</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-white-50">Belum ada data penjualan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <i class="bi bi-pie-chart text-info me-2"></i> Metode Pembayaran
                </div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <canvas id="paymentMethodChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris 3: Kinerja Kasir / Waitress -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <i class="bi bi-person-badge text-primary me-2"></i> Kinerja Waitress per Shift
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nama Waitress</th>
                                    <th>Shift</th>
                                    <th>Transaksi</th>
                                    <th>Pendapatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirPerformance as $kasir)
                                <tr>
                                    <td class="fw-medium">{{ $kasir->name }}</td>
                                    <td>
                                        @if($kasir->shift == 'pagi')
                                            <span class="badge bg-info text-white"><i class="bi bi-brightness-high"></i> Pagi</span>
                                        @else
                                            <span class="badge "><i class="bi bi-moon-stars"></i> Malam</span>
                                        @endif
                                    </td>
                                    <td>{{ $kasir->total_transaksi }}</td>
                                    <td class="text-success fw-bold">Rp {{ number_format($kasir->total_pendapatan, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-white-50">Belum ada data staf waitress.</td></tr>
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
        <div class="col-md-12">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-bold text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <i class="bi bi-safe text-danger me-2"></i> Riwayat Rekonsiliasi Waitress (Buka/Tutup Laci)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Waitress</th>
                                    <th>Waktu Shift</th>
                                    <th>Modal Awal</th>
                                    <th>Pemasukan Tunai Sistem</th>
                                    <th>Uang Fisik Waitress</th>
                                    <th>Selisih Laci</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kasirShifts as $shift)
                                <tr>
                                    <td class="fw-medium">{{ $shift->user->name ?? 'Unknown' }}</td>
                                    <td>
                                        {{ $shift->waktu_buka->format('d/m/Y H:i') }} <br>
                                        <small class="text-white-50">s/d {{ $shift->waktu_tutup ? $shift->waktu_tutup->format('d/m/Y H:i') : 'Sekarang' }}</small>
                                    </td>
                                    <td>Rp {{ number_format($shift->modal_awal, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($shift->total_pemasukan_tunai, 0, ',', '.') }}</td>
                                    <td>
                                        @if($shift->uang_fisik_aktual)
                                            Rp {{ number_format($shift->uang_fisik_aktual, 0, ',', '.') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($shift->status == 'open')
                                            <span class="badge bg-secondary">Sedang Berjalan</span>
                                        @else
                                            @if($shift->selisih == 0)
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Balance (0)</span>
                                            @elseif($shift->selisih < 0)
                                                <span class="badge bg-danger"><i class="bi bi-arrow-down me-1"></i> Kurang Rp {{ number_format(abs($shift->selisih), 0, ',', '.') }}</span>
                                            @else
                                                <span class="badge bg-warning text-white"><i class="bi bi-arrow-up me-1"></i> Lebih Rp {{ number_format($shift->selisih, 0, ',', '.') }}</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if($shift->status == 'open')
                                            <span class="badge bg-primary">Open</span>
                                        @else
                                            <span class="badge ">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="7" class="text-center text-white-50">Belum ada riwayat shift untuk periode ini.</td></tr>
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
                        backgroundColor: 'rgba(54, 162, 235, 0.12)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => 'Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#495057' }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#e9ecef' },
                            ticks: {
                                color: '#495057',
                                callback: (value) => 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    }
                }
            });
        }

        // Pie Chart Metode Pembayaran
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
                            'rgba(54, 162, 235, 0.8)', // QRIS (biasanya biru)
                            'rgba(75, 192, 192, 0.8)', // Cash (hijau)
                            'rgba(255, 206, 86, 0.8)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (context) => context.label + ': Rp ' + context.formattedValue.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                        }
                    }
                }
            });
        } else if (pmCtx) {
            // Fallback jika kosong
            pmCtx.parentElement.innerHTML = '<p class="text-white-50 text-center w-100">Belum ada data pembayaran</p>';
        }
    }

    initReportCharts();
})();
</script>
@endsection

