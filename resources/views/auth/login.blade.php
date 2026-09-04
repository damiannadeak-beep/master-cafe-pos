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
        max-width: 440px;
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
            <h3 class="fw-bold text-white mb-1" style="font-family: 'Rye', serif;">Masuk ke Master Cafe</h3>
            <p class="text-secondary small">Pesan hidangan istimewa langsung dari meja Anda</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 text-center mb-4 small" style="background-color: rgba(245, 101, 101, 0.1); color: #f56565; border: 1px solid rgba(245, 101, 101, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-secondary">Email Address</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                </div>
                @error('email')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label fw-bold small text-secondary mb-0">Password</label>
                    @if (Route::has('password.request'))
                        <a class="small text-decoration-none fw-bold" style="color: #c08e5c;" href="{{ route('password.request') }}">Lupa Password?</a>
                    @endif
                </div>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-end-0" name="password" required autocomplete="current-password" placeholder="Masukkan password" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
                @error('password')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="background-color: #0e1217; border-color: #21262d;">
                    <label class="form-check-label text-secondary fw-semibold small" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-auth-primary btn-touch">
                    Masuk Sekarang <i class="bi bi-arrow-right-circle ms-2"></i>
                </button>
                <a href="{{ route('google.login') }}" class="btn btn-google-auth d-flex align-items-center justify-content-center btn-touch">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" class="me-2" style="width: 20px; height: 20px;">
                    Masuk dengan Google
                </a>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top border-secondary">
            <p class="text-secondary small mb-0">Belum punya akun? <a href="{{ route('register') }}" class="fw-bold text-decoration-none" style="color: #c08e5c;">Daftar Sekarang</a></p>
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




