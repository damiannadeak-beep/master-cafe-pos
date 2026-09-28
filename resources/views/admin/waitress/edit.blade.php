@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Edit Staf Waitress</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Update Kredensial)</span>
            </div>
            <p class="text-secondary small mb-0">Perbarui profil, informasi kontak, atau ubah shift operasional staf.</p>
        </div>
        <a href="{{ route('admin.kasir.index') }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar Staf
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-person-gear" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Formulir Edit Akun</h5>
                    </div>
                    <span class="text-secondary small fw-medium">ID: #{{ $kasir->id }}</span>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.kasir.update', $kasir->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control text-white @error('name') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" value="{{ old('name', $kasir->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Alamat Email</label>
                            <input type="email" name="email" class="form-control text-white @error('email') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" value="{{ old('email', $kasir->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control text-white @error('no_hp') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" value="{{ old('no_hp', $kasir->no_hp) }}" placeholder="Contoh: 081234567890">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-secondary small fw-medium mb-1">Penugasan Shift</label>
                            <select class="form-select text-white @error('shift') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="shift" required>
                                <option value="pagi" {{ old('shift', $kasir->shift) == 'pagi' ? 'selected' : '' }}>🌅 Shift Pagi (08:00 - 17:00)</option>
                                <option value="malam" {{ old('shift', $kasir->shift) == 'malam' ? 'selected' : '' }}>🌙 Shift Malam (17:00 - 00:00)</option>
                            </select>
                            @error('shift') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="p-3 rounded-3 mb-4" style="background-color: #0e1217; border: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-shield-lock" style="color: #c08e5c;"></i>
                                <span class="fw-semibold text-white small">Ubah Password Akun</span>
                                <span class="text-secondary opacity-75 small" style="font-size: 0.75rem;">(Opsional)</span>
                            </div>
                            <p class="text-secondary small mb-3" style="font-size: 0.75rem;">Biarkan kolom password kosong apabila tidak bermaksud mengganti kata sandi staf.</p>

                            <div class="mb-3">
                                <label class="form-label text-secondary small fw-medium mb-1">Password Baru</label>
                                <input type="password" name="password" class="form-control text-white @error('password') is-invalid @enderror" style="background-color: #161b22; border-color: #21262d; border-radius: 8px;" placeholder="Minimal 6 karakter">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="form-label text-secondary small fw-medium mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" class="form-control text-white" style="background-color: #161b22; border-color: #21262d; border-radius: 8px;" placeholder="Ketik ulang password baru">
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2">
                            <a href="{{ route('admin.kasir.index') }}" class="btn btn-outline-secondary px-4 py-2 fw-medium rounded-3" style="border-color: #30363d; color: #d0d7de;">Batal</a>
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                <i class="bi bi-check2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
