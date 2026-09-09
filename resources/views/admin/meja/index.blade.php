@extends('layouts.admin')

@section('content')
<div class="container py-2">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-accent" style="font-family: 'Rye', serif;"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Manajemen Meja & QR Code</h2>
            <p class="text-white-50 mb-0">Kelola identitas meja kafe dan cetak stiker QR code untuk pemesanan langsung konsumen di tempat</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($mejas->count() > 0)
                <a href="{{ route('admin.meja.print_all_qr') }}" target="_blank" class="btn fw-bold px-3 py-2 rounded-pill btn-touch" style="background: var(--gradient-bronze); color: white; border: none; box-shadow: 0 4px 15px rgba(192, 142, 92, 0.3);">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Semua QR Meja
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <!-- Kolom Kiri: Form Tambah Meja -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header fw-bold text-white border-0 py-3 px-4" style="background-color: #14171c; border-bottom: 1px solid #21262d !important;">
                    <i class="bi bi-plus-circle text-accent me-2"></i> Tambah Meja Baru
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.meja.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-white-50 small fw-bold">NAMA / NOMOR MEJA</label>
                            <input type="text" name="nama_meja_atau_nomor" class="form-control bg-dark text-white border-secondary rounded-3 py-2" placeholder="Misal: Meja 1, VIP A, Meja Lt 2" required>
                            <small class="text-secondary mt-1 d-block" style="font-size: 0.75rem;">Nomor ini akan tercetak pada stiker QR dan struk pesanan dapur.</small>
                        </div>
                        <button type="submit" class="btn fw-bold w-100 rounded-3 py-2 btn-touch" style="background: var(--gradient-bronze); color: white; border: none;">
                            <i class="bi bi-check-lg me-1"></i> Simpan Meja
                        </button>
                    </form>

                    <hr style="border-color: #21262d;" class="my-4">

                    <div class="rounded-3 p-3 text-white-50 small" style="background-color: #0e1217; border: 1px solid #21262d;">
                        <div class="fw-bold text-white mb-1"><i class="bi bi-shield-check text-success me-1"></i>Signed QR Code:</div>
                        Setiap meja secara otomatis memiliki tautan QR khusus bertanda-tangan digital agar ID meja tidak dapat dimanipulasi dari luar.
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Meja -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header fw-bold text-white border-0 py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: #14171c; border-bottom: 1px solid #21262d !important;">
                    <span><i class="bi bi-table text-accent me-2"></i>Daftar Meja Terdaftar</span>
                    <span class="badge rounded-pill bg-dark border border-secondary text-white-50 px-3 py-1">{{ $mejas->count() }} Meja</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="background-color: #161b22;">
                            <thead style="background-color: #0e1217;">
                                <tr>
                                    <th class="ps-4" style="width: 70px;">No</th>
                                    <th>Nama / Nomor Meja</th>
                                    <th class="text-center pe-4" style="width: 220px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mejas as $index => $m)
                                <tr>
                                    <td class="ps-4 text-white-50">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-white">
                                            <i class="bi bi-geo-alt text-accent me-1"></i> {{ $m->nama_meja_atau_nomor }}
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <!-- Tombol Print QR -->
                                        <a href="{{ route('admin.meja.print_qr', $m->id) }}" target="_blank" class="btn btn-sm btn-outline-warning me-1 rounded-3" title="Cetak QR Code">
                                            <i class="bi bi-qr-code me-1"></i> Cetak QR
                                        </a>
                                        
                                        <!-- Tombol Edit -->
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1 rounded-3" data-bs-toggle="modal" data-bs-target="#editModal{{ $m->id }}" title="Edit Nama Meja">
                                            <i class="bi bi-pencil"></i>
                                        </button>

                                        <!-- Tombol Hapus -->
                                        <form class="d-inline" action="{{ route('admin.meja.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus meja {{ $m->nama_meja_atau_nomor }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger rounded-3" title="Hapus Meja"><i class="bi bi-trash"></i></button>
                                        </form>

                                        <!-- Modal Edit -->
                                        <div class="modal fade text-start" id="editModal{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important; color: white;">
                                                    <div class="modal-header border-secondary border-opacity-25">
                                                        <h5 class="modal-title fw-bold text-accent">Edit Meja</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.meja.update', $m->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label text-white-50 small fw-bold">NAMA / NOMOR MEJA</label>
                                                                <input type="text" name="nama_meja_atau_nomor" class="form-control bg-dark text-white border-secondary rounded-3" value="{{ $m->nama_meja_atau_nomor }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-secondary border-opacity-25">
                                                            <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn fw-bold rounded-3" style="background: var(--gradient-bronze); color: white; border: none;">Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-white-50 py-5">
                                        <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                        Belum ada data meja yang didaftarkan.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
