@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-receipt text-warning me-2"></i>Audit Pengeluaran Kasir / Waitress</h2>
            <p class="text-white-50 mb-0">Pantau seluruh pengeluaran kas kecil laci yang dicatat oleh kasir/waitress selama operasional toko.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pengeluaran_bisnis.index') }}" class="btn btn-primary">
                <i class="bi bi-wallet2 me-1"></i> Buka Pengeluaran Bisnis Owner
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stat Cards Ringkasan -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Total Pengeluaran Laci</span>
                        <div class="text-danger bg-danger bg-opacity-10 p-2 rounded">
                            <i class="bi bi-cash-stack fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">Periode terpilih</small>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm text-white" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-white-50 small">Frekuensi Catatan</span>
                        <div class="text-info bg-info bg-opacity-10 p-2 rounded">
                            <i class="bi bi-journal-text fs-5"></i>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-0">{{ $totalTransaksi }} kali</h4>
                    <small class="text-white-50" style="font-size: 0.75rem;">Total item dibeli kasir</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-body py-3">
            <form action="{{ route('admin.pengeluaran.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-white-50 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d;" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-white-50 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d;" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-white-50 mb-1">Waitress / Kasir</label>
                    <select name="waitress_id" class="form-select form-select-sm text-white" style="background-color: #0e1217; border-color: #21262d;">
                        <option value="">Semua Waitress</option>
                        @foreach($waitresses as $w)
                            <option value="{{ $w->id }}" {{ $waitressId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i> Terapkan Filter</button>
                    <a href="{{ route('admin.pengeluaran.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Kasir -->
    <div class="card shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-header fw-bold text-white d-flex justify-content-between align-items-center" style="background-color: transparent; border-bottom: 1px solid #21262d !important;">
            <span><i class="bi bi-table me-2 text-warning"></i>Daftar Pengeluaran dari Kas Laci Kasir</span>
            <span class="badge bg-secondary">Hanya Lihat (Read-Only)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0">
                    <thead class="table-dark" style="border-bottom: 1px solid #21262d;">
                        <tr>
                            <th class="ps-3" style="width: 140px;">Tanggal</th>
                            <th style="width: 160px;">Kasir / Waitress</th>
                            <th>Keperluan / Deskripsi</th>
                            <th>Keterangan Tambahan</th>
                            <th class="text-end" style="width: 160px;">Nominal (Rp)</th>
                            <th class="text-center pe-3" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $p)
                        <tr>
                            <td class="ps-3 text-white-50">
                                <span class="d-block fw-semibold text-white">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</span>
                                <small style="font-size: 0.75rem;">{{ $p->created_at ? $p->created_at->format('H:i') : '' }} WIB</small>
                            </td>
                            <td>
                                @if($p->user)
                                    <span class="badge bg-secondary text-white py-1 px-2">
                                        <i class="bi bi-person me-1"></i>{{ $p->user->name }}
                                    </span>
                                @else
                                    <span class="text-white-50 small">-</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-white">{{ $p->deskripsi }}</td>
                            <td class="text-white-50 small">{{ $p->keterangan ?: '-' }}</td>
                            <td class="text-end text-danger fw-bold text-nowrap">
                                - Rp {{ number_format($p->nominal, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-3">
                                <form action="{{ route('admin.pengeluaran.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengeluaran kasir ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus jika salah input">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-white-50 py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                Tidak ada catatan pengeluaran kasir pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($pengeluarans->hasPages())
        <div class="card-footer border-top py-3" style="background-color: transparent; border-color: #21262d !important;">
            {{ $pengeluarans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
