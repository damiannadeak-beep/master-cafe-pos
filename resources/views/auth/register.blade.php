@extends('layouts.app')

@section('content')
<style>
    .auth-wrapper {
        background-color: #0e1217;
        min-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
    }

    .auth-card {
        background: #161b22;
        border: 1px solid #21262d;
        border-radius: 1.75rem;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5);
        padding: 2.5rem;
        width: 100%;
        max-width: 460px;
    }

    .auth-brand-icon {
        width: 68px;
        height: 68px;
        border-radius: 1.25rem;
        background: var(--gradient-bronze);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 20px rgba(178, 122, 77, 0.2);
    }

    .auth-input-group .input-group-text {
        background: #0e1217;
        border-color: #21262d;
        border-right: none;
        color: #c08e5c;
        border-top-left-radius: 0.85rem;
        border-bottom-left-radius: 0.85rem;
    }

    .auth-input-group .form-control {
        background: #0e1217;
        border-color: #21262d;
        border-left: none;
        border-top-right-radius: 0.85rem;
        border-bottom-right-radius: 0.85rem;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
        color: #ffffff;
    }

    .auth-input-group .form-control:focus {
        border-color: #c08e5c;
        box-shadow: none;
        background: #0e1217;
        color: #ffffff;
    }
    
    .auth-input-group .form-control::placeholder {
        color: #6c757d;
    }

    .btn-auth-primary {
        background: #c08e5c;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.85rem 1.5rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 20px rgba(178, 122, 77, 0.2);
        transition: all 0.2s ease;
    }

    .btn-auth-primary:hover {
        background: #986c43;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(178, 122, 77, 0.3);
    }

    .btn-google-auth {
        background: #161b22;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.8rem 1.5rem;
        border-radius: 999px;
        border: 1px solid #21262d;
        transition: all 0.2s ease;
    }

    .btn-google-auth:hover {
        background: #21262d;
        border-color: #c08e5c;
    }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow" style="height: 80px; width: 80px; object-fit: cover; margin-bottom: 1rem;">
            <h3 class="fw-bold text-white mb-1" style="font-family: 'Rye', serif;">Daftar Akun Konsumen</h3>
            <p class="text-secondary small">Nikmati kemudahan pesan langsung dari meja Anda</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="mb-3">
                <label for="name" class="form-label fw-bold small text-secondary">Nama Lengkap</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Contoh: Damian Nadeak">
                </div>
                @error('name')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-secondary">Email Address</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@email.com">
                </div>
                @error('email')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold small text-secondary">Password</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-end-0" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
                @error('password')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="form-label fw-bold small text-secondary">Konfirmasi Password</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-check-circle"></i></span>
                    <input id="password-confirm" type="password" class="form-control border-end-0" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password Anda" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password-confirm', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-auth-primary btn-touch">
                    Daftar Sekarang <i class="bi bi-person-plus-fill ms-2"></i>
                </button>
                <a href="{{ route('google.login') }}" class="btn btn-google-auth d-flex align-items-center justify-content-center btn-touch">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" class="me-2" style="width: 20px; height: 20px;">
                    Daftar dengan Google
                </a>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top border-secondary">
            <p class="text-secondary small mb-0">Sudah punya akun? <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color: #c08e5c;">Masuk di sini</a></p>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId, iconElement) {
    const input = document.getElementById(inputId);
    const icon = iconElement.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>
@endsection




