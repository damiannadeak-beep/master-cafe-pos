<!-- Kolom Keamanan Akun -->
        <div class="col-lg-6">
            <div class="card admin-card border-0 shadow-sm h-100">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock me-2 text-danger"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.security') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Admin</label>
                            <input type="text" class="form-control text-white border-secondary  @error('name') is-invalid @enderror" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" class="form-control text-white border-secondary  @error('email') is-invalid @enderror" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold mb-3">Ubah Password (Opsional)</h6>
                        <p class="small text-white-50 mb-3">Kosongkan bagian ini jika Anda tidak ingin mengubah password.</p>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Saat Ini</label>
                            <input type="password" class="form-control text-white border-secondary  @error('current_password') is-invalid @enderror" name="current_password">
                            @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password Baru</label>
                            <input type="password" class="form-control text-white border-secondary  @error('password') is-invalid @enderror" name="password" minlength="8">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="password_confirmation" minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Perbarui Keamanan</button>
                    </form>
                </div>
            </div>
        </div>