<!-- Kolom Keamanan Akun -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock" style="color: #c08e5c; font-size: 1.1rem;"></i>
                <h5 class="mb-0 fw-bold text-white fs-6">Keamanan Akun</h5>
            </div>
            <span class="text-secondary small fw-medium">Autentikasi</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.settings.security') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-profile')">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Nama Admin</label>
                    <input type="text" class="form-control text-white @error('name') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-medium mb-1">Alamat Email</label>
                    <input type="email" class="form-control text-white @error('email') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="pt-3 border-top" style="border-color: #21262d !important;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="fw-semibold text-white small">Ubah Password</span>
                        <span class="text-secondary small">(Opsional)</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium mb-1">Password Saat Ini</label>
                        <input type="password" class="form-control text-white @error('current_password') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="current_password" placeholder="Masukkan password lama untuk konfirmasi">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-medium mb-1">Password Baru</label>
                        <input type="password" class="form-control text-white @error('password') is-invalid @enderror" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="password" minlength="8" placeholder="Minimal 8 karakter">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-medium mb-1">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="password_confirmation" minlength="8" placeholder="Ulangi password baru">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-outline-secondary w-100 fw-medium py-2 rounded-3 text-white" style="border-color: #30363d;">
                    <i class="bi bi-shield-check me-1"></i> Perbarui Keamanan Akun
                </button>
            </form>
        </div>
    </div>
</div>