@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Audit Pengeluaran Kasir</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Kas Laci POS)</span>
            </div>
            <p class="text-secondary small mb-0">Pantau riwayat pengeluaran kas kecil laci yang dicatat oleh kasir/waitress selama operasional toko.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.pengeluaran_bisnis.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2 px-3 py-2 fw-medium rounded-3" style="border-color: #30363d; color: #d0d7de;">
                <i class="bi bi-wallet2" style="color: #c08e5c;"></i>
                <span>Pengeluaran Bisnis Owner</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-3" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Stat Cards Ringkasan (Executive Dark) -->
    <div class="row g-3 mb-4">
        <!-- Total Pengeluaran Laci -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Total Pengeluaran Laci</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171;">
                            <i class="bi bi-cash-stack" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">Rp {{ number_format($totalNominal, 0, ',', '.') }}</h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                        Periode terpilih
                    </div>
                </div>
            </div>
        </div>

        <!-- Frekuensi Catatan -->
        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary small fw-medium">Frekuensi Catatan</span>
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 36px; height: 36px; background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08); color: #d0d7de;">
                            <i class="bi bi-journal-text" style="font-size: 1rem;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-white mb-1" style="font-size: 1.45rem;">{{ $totalTransaksi }} <span class="fs-6 fw-normal text-secondary">catatan</span></h3>
                    <div class="text-secondary small" style="font-size: 0.75rem;">
                        Total item dibeli kasir
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.pengeluaran.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" class="form-control form-control-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Waitress / Kasir</label>
                    <select name="waitress_id" class="form-select form-select-sm text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px; height: 38px;">
                        <option value="">Semua Waitress</option>
                        @foreach($waitresses as $w)
                            <option value="{{ $w->id }}" {{ $waitressId == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center justify-content-center gap-1.5 fw-medium flex-grow-1" style="border-radius: 8px; height: 38px;">
                        <i class="bi bi-funnel"></i>
                        <span>Terapkan Filter</span>
                    </button>
                    <a href="{{ route('admin.pengeluaran.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; width: 38px; height: 38px; flex-shrink: 0;" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Kasir -->
    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d !important;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-cash-stack" style="color: #c08e5c;"></i>
                <span class="fw-semibold text-white fs-6">Riwayat Kas Kecil Laci Kasir</span>
            </div>
            <span class="text-secondary small fw-medium">
                Total: {{ $pengeluarans->total() }} Data
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.02);">
                    <thead>
                        <tr style="border-bottom: 1px solid #21262d; background-color: rgba(255, 255, 255, 0.02);">
                            <th class="text-secondary text-uppercase fw-semibold py-3 ps-4" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 150px;">Tanggal</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 170px;">Kasir / Waitress</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Keperluan / Deskripsi</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">Keterangan Tambahan</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 160px;">Nominal (Rp)</th>
                            <th class="text-secondary text-uppercase fw-semibold py-3 pe-4 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengeluarans as $p)
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                            <td class="ps-4 py-3 text-nowrap">
                                <span class="d-block fw-medium text-white">{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</span>
                                <span class="text-secondary small" style="font-size: 0.75rem;">{{ $p->created_at ? $p->created_at->format('H:i') : '' }} WIB</span>
                            </td>
                            <td class="px-3 py-3">
                                @if($p->user)
                                    <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.75rem; font-weight: 500;">
                                        <i class="bi bi-person me-1"></i>{{ $p->user->name }}
                                    </span>
                                @else
                                    <span class="text-secondary small">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <span class="fw-medium text-white d-block">{{ $p->deskripsi }}</span>
                            </td>
                            <td class="px-3 py-3 text-secondary small">
                                {{ $p->keterangan ?: '-' }}
                            </td>
                            <td class="px-3 py-3 text-end text-nowrap">
                                <span class="fw-semibold text-white">Rp {{ number_format($p->nominal, 0, ',', '.') }}</span>
                            </td>
                            <td class="pe-4 py-3 text-end">
                                <form action="{{ route('admin.pengeluaran.destroy', $p->id) }}" method="POST" class="d-inline-flex m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pengeluaran kasir ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" title="Hapus jika salah input">
                                        <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary small">
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
        <div class="card-footer px-4 py-3" style="background-color: #161b22; border-top: 1px solid #21262d;">
            {{ $pengeluarans->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
