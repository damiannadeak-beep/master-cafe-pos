@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Manajemen Meja & QR Code</h2>
            <p class="text-white-50 mb-0">Kelola meja dan cetak QR code untuk pemesanan konsumen.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center mb-3">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center mb-3">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Kolom Kiri: Form Tambah Meja -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm h-100 border-0" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #30363d;">
                    <span class="fw-bold text-white fs-6 d-flex align-items-center gap-2">
                        <i class="bi bi-plus-square text-primary"></i> Tambah Meja Baru
                    </span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.meja.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-semibold">Nama / Nomor Meja</label>
                            <input type="text" name="nama_meja_atau_nomor" class="form-control bg-dark border-secondary border-opacity-25 text-white" placeholder="Misal: Meja 1, VIP A, Meja 12" required>
                            <small class="text-white-50" style="font-size: 0.78rem;">Nomor ini akan tertera pada stiker QR Code meja.</small>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2" style="border-radius: 8px;">
                            <i class="bi bi-save"></i> Simpan Meja
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Tabel Data Meja -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm h-100 border-0" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #30363d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3 text-warning"></i>
                        <span class="fw-bold text-white fs-6">Daftar Meja</span>
                    </div>
                    <span class="text-secondary small fw-medium">
                        Total: {{ $mejas->count() }} Meja
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255, 255, 255, 0.04);">
                            <thead>
                                <tr style="border-bottom: 1px solid #30363d; background-color: rgba(255, 255, 255, 0.03);">
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-center" style="font-size: 0.75rem; letter-spacing: 0.5px; width: 50px;">No</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3" style="font-size: 0.75rem; letter-spacing: 0.5px; min-width: 130px;">Nama / Nomor Meja</th>
                                    <th class="text-secondary text-uppercase fw-semibold py-3 px-3 text-end" style="font-size: 0.75rem; letter-spacing: 0.5px; min-width: 190px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mejas as $index => $m)
                                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                                    <td class="text-center text-white-50 px-2 fw-semibold" style="font-size: 0.95rem;">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="px-3 py-3">
                                        <span class="fw-semibold text-white" style="font-size: 1rem; letter-spacing: 0.2px;">{{ $m->nama_meja_atau_nomor }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-end">
                                        <div class="action-btn-group d-inline-flex align-items-center justify-content-end gap-2 flex-nowrap" style="gap: 8px !important;">
                                            <!-- Tombol Print QR -->
                                            <a href="{{ route('admin.meja.print_qr', $m->id) }}" target="_blank" class="btn btn-icon-action btn-outline-success d-inline-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; border-radius: 8px; background: rgba(35, 134, 54, 0.08);" title="Cetak QR Code">
                                                <i class="bi bi-qr-code fs-6 d-flex align-items-center justify-content-center"></i>
                                            </a>
                                            
                                            <!-- Tombol Edit -->
                                            <button type="button" class="btn btn-icon-action btn-outline-secondary d-inline-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; border-radius: 8px; color: #c08e5c; border-color: rgba(192, 142, 92, 0.25); background: rgba(192, 142, 92, 0.08);" data-bs-toggle="modal" data-bs-target="#editModal{{ $m->id }}" title="Edit Meja">
                                                <i class="bi bi-pencil fs-6 d-flex align-items-center justify-content-center"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form class="d-inline-flex m-0 p-0" action="{{ route('admin.meja.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus meja ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-icon-action btn-outline-danger d-inline-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px; border-radius: 8px; background: rgba(248, 81, 73, 0.05);" title="Hapus Meja">
                                                    <i class="bi bi-trash fs-6 d-flex align-items-center justify-content-center"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Modal Edit -->
                                        <div class="modal fade text-start" id="editModal{{ $m->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content" style="background-color: #161b22; border: 1px solid #30363d; border-radius: 12px; color: #fff;">
                                                    <div class="modal-header border-secondary border-opacity-25">
                                                        <h5 class="modal-title fs-6 fw-bold text-white d-flex align-items-center gap-2">
                                                            <i class="bi bi-pencil-square text-primary"></i> Edit Meja
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <form action="{{ route('admin.meja.update', $m->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label text-secondary small fw-semibold">Nama / Nomor Meja</label>
                                                                <input type="text" name="nama_meja_atau_nomor" class="form-control bg-dark border-secondary border-opacity-25 text-white" value="{{ $m->nama_meja_atau_nomor }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-secondary border-opacity-25">
                                                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
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
                                        <i class="bi bi-inbox fs-2 text-secondary d-block mb-2 opacity-50"></i>
                                        <span>Belum ada data meja yang ditambahkan.</span>
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

<style>
    /* Styling tombol aksi agar selalu presisi di tengah */
    .action-btn-group .btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
        vertical-align: middle !important;
    }
    .action-btn-group .btn i {
        display: inline-block;
        line-height: 1;
        margin: 0;
        padding: 0;
    }

    /* Optimasi Tampilan Mobile untuk Manajemen Meja */
    @media (max-width: 576px) {
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive table {
            min-width: 360px;
        }
        .action-btn-group {
            gap: 8px !important;
        }
        .action-btn-group .btn-icon-action,
        .action-btn-group .btn-qr-action {
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 10px !important;
        }
        .action-btn-group .btn-qr-action i,
        .action-btn-group .btn-icon-action i {
            font-size: 1.15rem !important;
            line-height: 1 !important;
        }
    }
</style>
@endsection
