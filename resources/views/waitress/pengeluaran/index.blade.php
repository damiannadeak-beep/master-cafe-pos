@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-white mb-0"><i class="bi bi-wallet2 me-2" style="color: #c08e5c;"></i>Pengeluaran Operasional Waitress</h4>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Kas Kecil)</span>
            </div>
            <p class="text-secondary small mb-0">Catat kebutuhan belanja harian darurat seperti es batu, kantong plastik, atau bahan operasional mendesak.</p>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(239, 68, 68, 0.1); border-color: rgba(239, 68, 68, 0.25); color: #f87171;">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metric Summary Cards -->
    @php
        $todayTotal = \App\Models\Pengeluaran::where('user_id', auth()->id())->whereDate('tanggal', date('Y-m-d'))->sum('nominal');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Pengeluaran Saya Hari Ini</div>
                        <div class="fs-4 fw-bold text-white">Rp {{ number_format($todayTotal, 0, ',', '.') }}</div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">{{ \Carbon\Carbon::now()->isoFormat('D MMMM Y') }}</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;">
                        <i class="bi bi-cash-coin fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
                <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-secondary small fw-medium mb-1">Total Riwayat Catatan</div>
                        <div class="fs-4 fw-bold text-white">{{ $pengeluarans->total() }} <span class="fs-6 fw-normal text-secondary">Catatan</span></div>
                        <div class="text-secondary small mt-0.5" style="font-size: 0.72rem;">Pengeluaran dicatat akun Anda</div>
                    </div>
                    <div class="d-flex align-items-center justify-content-center rounded-3" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.25); color: #fbbf24;">
                        <i class="bi bi-receipt fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Kolom Kiri: Form Tambah Pengeluaran -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-plus-circle" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Catat Pengeluaran Baru</h5>
                    </div>
                    <span class="text-secondary small fw-medium">Input Kas</span>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('kasir.pengeluaran.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Deskripsi Barang / Keperluan</label>
                            <input type="text" name="deskripsi" class="form-control text-white @error('deskripsi') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" placeholder="Misal: Beli Es Batu Kristal 2 Bal" required>
                            @error('deskripsi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Nominal (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text text-secondary border-end-0" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px 0 0 8px;">Rp</span>
                                <input type="number" name="nominal" class="form-control text-white border-start-0 @error('nominal') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 0 8px 8px 0;" min="0" placeholder="0" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" required>
                                @error('nominal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-medium mb-1">Keterangan / Catatan Tambahan (Opsional)</label>
                            <textarea name="keterangan" class="form-control text-white" rows="2" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" placeholder="Contoh: Dibeli di warung sebelah karena stok habis saat jam ramai..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-medium py-2 rounded-3 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-check2"></i> Simpan Pengeluaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Riwayat Pengeluaran -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Riwayat Pengeluaran Saya</h5>
                    </div>
                    <span class="text-secondary small fw-medium">
                        Total: {{ $pengeluarans->total() }} Catatan
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="color: #c9d1d9;">
                            <thead style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.5px;">Tanggal</th>
                                    <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Barang / Keperluan</th>
                                    <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Keterangan</th>
                                    <th class="pe-4 py-3 text-end text-secondary text-uppercase fw-semibold" style="width: 150px; font-size: 0.72rem; letter-spacing: 0.5px;">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengeluarans as $p)
                                    <tr style="border-bottom: 1px solid #21262d;">
                                        <td class="ps-4 py-3 text-secondary small text-nowrap">
                                            <div class="text-white fw-medium">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</div>
                                            <div class="text-secondary" style="font-size: 0.7rem;">{{ $p->created_at ? $p->created_at->format('H:i') . ' WIB' : '-' }}</div>
                                        </td>
                                        <td class="py-3">
                                            <span class="fw-semibold text-white fs-6">{{ $p->deskripsi }}</span>
                                        </td>
                                        <td class="py-3 text-secondary small">
                                            {{ $p->keterangan ?: '-' }}
                                        </td>
                                        <td class="pe-4 py-3 text-end fw-bold text-nowrap" style="color: #c08e5c;">
                                            Rp {{ number_format($p->nominal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.03); border: 1px solid #21262d; color: #8b949e;">
                                                    <i class="bi bi-wallet2 fs-3"></i>
                                                </div>
                                                <h6 class="text-white fw-bold mb-1">Belum Ada Catatan Pengeluaran</h6>
                                                <p class="text-secondary small mb-0">Catat pengeluaran operasional baru melalui formulir di samping.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($pengeluarans->hasPages())
                    <div class="card-footer py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-top: 1px solid #21262d;">
                        {{ $pengeluarans->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
