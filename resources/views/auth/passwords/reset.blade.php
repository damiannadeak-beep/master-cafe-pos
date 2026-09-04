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
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-white mb-1" style="font-family: 'Rye', serif;">Buat Kata Sandi Baru</h3>
            <p class="text-secondary small">Pastikan kata sandi baru Anda kuat dan mudah diingat.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label fw-bold small text-secondary">Alamat Email</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror border-start-0" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" readonly style="background-color: #0e1217; color: #8b949e; border-left: none;">
                </div>
                @error('email')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-bold small text-secondary">Kata Sandi Baru</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror border-end-0" name="password" required autocomplete="new-password" autofocus placeholder="Masukkan kata sandi baru" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
                @error('password')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password-confirm" class="form-label fw-bold small text-secondary">Ulangi Kata Sandi Baru</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input id="password-confirm" type="password" class="form-control border-end-0" name="password_confirmation" required autocomplete="new-password" placeholder="Ketik ulang kata sandi baru" style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <span class="input-group-text toggle-password" style="cursor: pointer; border-top-right-radius: 0.85rem; border-bottom-right-radius: 0.85rem; border-left: none; background: #0e1217; border-color: #21262d;" onclick="togglePasswordVisibility('password-confirm', this)"><i class="bi bi-eye-slash text-secondary"></i></span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-auth-primary btn-touch">
                    Simpan Kata Sandi <i class="bi bi-check2-circle ms-2"></i>
                </button>
            </div>
        </form>
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