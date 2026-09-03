@extends('layouts.app')

@section('content')
<style>
    .staff-auth-wrapper {
        background-color: #0e1217;
        min-height: 88vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2.5rem 1rem;
    }

    .staff-auth-card {
        background: #161b22;
        border: 1px solid #21262d;
        border-radius: 1.75rem;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
        padding: 2.75rem 2.25rem;
        width: 100%;
        max-width: 440px;
        color: #f8f9fa;
    }

    .staff-brand-icon {
        width: 72px;
        height: 72px;
        border-radius: 1.25rem;
        background: var(--gradient-bronze);
        color: #ffffff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.25rem;
        box-shadow: 0 8px 24px rgba(178, 122, 77, 0.3);
    }

    .staff-input-group .input-group-text {
        background: #0e1217;
        border-color: #21262d;
        border-right: none;
        color: #c08e5c;
        border-top-left-radius: 0.85rem;
        border-bottom-left-radius: 0.85rem;
    }

    .staff-input-group .form-control {
        background: #0e1217;
        border-color: #21262d;
        border-left: none;
        border-top-right-radius: 0.85rem;
        border-bottom-right-radius: 0.85rem;
        color: #ffffff;
        font-size: 0.95rem;
        padding: 0.75rem 1rem;
    }

    .staff-input-group .form-control::placeholder {
        color: #6c757d;
    }

    .staff-input-group .form-control:focus {
        background: #0e1217;
        border-color: #c08e5c;
        color: #ffffff;
        box-shadow: none;
    }

    .btn-staff-primary {
        background: #c08e5c;
        color: #ffffff !important;
        font-weight: 700;
        padding: 0.85rem 1.5rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 24px rgba(178, 122, 77, 0.35);
        transition: all 0.25s ease;
    }

    .btn-staff-primary:hover {
        background: #986c43;
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(178, 122, 77, 0.5);
    }
</style>

<div class="staff-auth-wrapper">
    <div class="staff-auth-card text-center">
        <div class="staff-brand-icon">
            <i class="bi bi-shield-lock-fill fs-2"></i>
        </div>
        <h3 class="fw-bold text-white mb-1" style="font-family: 'Rye', serif;">Portal Staf & Kasir</h3>
        <p class="text-secondary small mb-4">Masuk ke sistem Mesin POS atau Dashboard Admin</p>

        @if (session('error'))
            <div class="alert alert-danger shadow-sm border-0 rounded-3 text-center mb-4 small" style="background-color: rgba(245, 101, 101, 0.1); color: #f56565; border: 1px solid rgba(245, 101, 101, 0.2) !important;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="text-start">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-secondary">Email Staf / Kasir</label>
                <div class="input-group staff-input-group">
                    <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="staf@mastercafe.com">
                </div>
                @error('email')
                    <span class="text-warning small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-bold small text-secondary">Password</label>
                <div class="input-group staff-input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-end-0" name="password" required autocomplete="current-password" placeholder="••••••••" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
                @error('password')
                    <span class="text-warning small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }} style="background-color: #0e1217; border-color: #21262d;">
                    <label class="form-check-label text-secondary small fw-semibold" for="remember">
                        Ingat Sesi Login Ini
                    </label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-staff-primary btn-touch">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Ke Portal Staf
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-50">
            <p class="text-secondary small mb-0">Bukan Staf/Kasir? <a href="{{ route('login') }}" class="fw-bold text-decoration-none" style="color: #c08e5c;">Login Konsumen</a></p>
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




