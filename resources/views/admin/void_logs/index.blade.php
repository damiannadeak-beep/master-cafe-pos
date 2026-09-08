@extends('layouts.admin')

@section('title', 'Log Void Pesanan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="fw-bold mb-0 text-white"><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Pusat Laporan & Audit</h2>
        <p class="text-white-50 mb-0">Pantau ringkasan omset penjualan, kehadiran kasir, dan audit transaksi.</p>
    </div>
</div>

<!-- Tab Navigasi Pusat Laporan -->
<ul class="nav nav-pills mb-4 border-bottom pb-3 gap-2">
    <li class="nav-item">
        <a class="nav-link text-white-50" href="{{ route('admin.reports.index') }}">
            <i class="bi bi-bar-chart-fill me-1"></i> Laporan Penjualan
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-white-50" href="{{ route('admin.absensi.index') }}">
            <i class="bi bi-calendar-check-fill me-1"></i> Absensi Kasir
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('admin.void_logs.index') }}">
            <i class="bi bi-journal-x me-1"></i> Audit Void Kasir
        </a>
    </li>
</ul>

    <div class="card shadow mb-4 border-0">
        <div class="card-header py-3 text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" border-bottom-0">
            <h6 class="m-0 font-weight-bold text-primary">Data Void Kasir</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-dark table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="text-white">
                        <tr>
                            <th>Waktu Void</th>
                            <th>No. Pesanan</th>
                            <th>Nama Kasir</th>
                            <th>Total Nilai</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i') }}</td>
                            <td><span class="badge bg-secondary">#{{ str_pad($log->pesanan_id, 4, '0', STR_PAD_LEFT) }}</span></td>
                            <td>{{ $log->kasir_name }}</td>
                            <td class="text-danger fw-bold">Rp {{ number_format($log->total_nilai, 0, ',', '.') }}</td>
                            <td>{{ $log->alasan }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-white-50">Belum ada riwayat void pesanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
