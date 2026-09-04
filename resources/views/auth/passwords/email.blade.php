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
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-white mb-1" style="font-family: 'Rye', serif;">Lupa Kata Sandi?</h3>
            <p class="text-secondary small">Masukkan alamat email Anda yang terdaftar, kami akan mengirimkan tautan untuk mengatur ulang kata sandi.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success shadow-sm border-0 rounded-3 text-center mb-4 small" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2) !important;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <div class="mb-4">
                <label for="email" class="form-label fw-bold small text-secondary">Alamat Email</label>
                <div class="input-group auth-input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="nama@email.com">
                </div>
                @error('email')
                    <span class="text-danger small mt-1 d-block fw-bold">{{ $message }}</span>
                @enderror
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-auth-primary btn-touch">
                    Kirim Tautan Reset <i class="bi bi-send ms-2"></i>
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4 pt-3 border-top border-secondary">
            <a href="{{ route('login') }}" class="text-secondary small text-decoration-none hover-white transition-all"><i class="bi bi-arrow-left me-1"></i> Kembali ke halaman Login</a>
        </div>
    </div>
</div>
@endsection