@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-0 pb-5">
    <div class="row g-4 align-items-start mt-2">
        <div class="col-12">
            <h4 class="fw-bold mb-0 text-accent"><i class="bi bi-wallet2 me-2"></i> Pengeluaran Kasir</h4>
            <p class="text-white-50 small">Catat pengeluaran harian seperti beli es batu, plastik, dll.</p>
        </div>

        @if(session('success'))
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 rounded-3 mb-0" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close btn-touch" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Kolom Kiri: Form Tambah Pengeluaran -->
        <div class="col-lg-4">
            <div class="kasir-card hover-lift card bg-transparent">
                <div class="card-header border-bottom-0 pt-4 pb-2 px-4">
                    <h6 class="mb-0 fw-bold text-accent">Tambah Pengeluaran</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kasir.pengeluaran.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-white-50">Deskripsi Barang / Keperluan</label>
                            <input type="text" name="deskripsi" class="form-control text-white border-secondary  form-control-lg  text-white border-0 shadow-sm" placeholder="Misal: Beli Es Batu" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-white-50">Nominal (Rp)</label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text  text-white border-0 text-white-50">Rp</span>
                                <input type="number" name="nominal" class="form-control text-white border-secondary  text-white border-0 ps-2" min="0" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-white-50">Keterangan (Opsional)</label>
                            <textarea name="keterangan" class="form-control text-white border-secondary  text-white border-0 shadow-sm" rows="2" placeholder="Catatan tambahan..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success  w-100 fw-bold rounded-pill shadow-sm text-white btn-touch">
                            <i class="bi bi-save me-1"></i> Simpan Pengeluaran
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Pengeluaran -->
        <div class="col-lg-8">
            <div class="kasir-card hover-lift card bg-transparent">
                <div class="card-header border-bottom-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-accent">Riwayat Pengeluaran Saya</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pengeluarans as $p)
                                <tr>
                                    <td class="small">{{ \Carbon\Carbon::parse($p->tanggal)->format('d M Y') }}</td>
                                    <td class="fw-bold">{{ $p->deskripsi }}</td>
                                    <td class="text-white-50 small">{{ $p->keterangan ?: '-' }}</td>
                                    <td class="text-end fw-bold text-accent">Rp {{ number_format($p->nominal, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-white-50 py-5">
                                        <i class="bi bi-wallet2 text-opacity-25 text-accent" style="font-size: 2.5rem;"></i>
                                        <p class="mt-2 mb-0">Belum ada catatan pengeluaran dari Anda.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($pengeluarans->hasPages())
                        <div class="mt-3">
                            {{ $pengeluarans->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


