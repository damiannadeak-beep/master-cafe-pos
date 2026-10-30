<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Otoritas Pemilik — Master Cafe</title>
    
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}?v=2" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-16x16.png') }}?v=2" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    
    <!-- Google Fonts: Poppins (Standar Tunggal Seluruh Aplikasi Master Cafe) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,500;1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-canvas: #0c0f14;
            --bg-surface: #141820;
            --bg-elevated: #1a202c;
            --border-subtle: #232936;
            --border-focus: #c08e5c;
            --bronze: #c08e5c;
            --bronze-hover: #a87647;
            --bronze-soft: rgba(192, 142, 92, 0.12);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-canvas);
            color: var(--text-primary);
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            margin: 0;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        .split-layout {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ================= SISI KIRI: BRAND & EDITORIAL ================= */
        .editorial-panel {
            flex: 1.15;
            position: relative;
            background: #090c10 url("{{ asset('images/cafe_hero.jpg') }}") center/cover no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px 56px;
            overflow: hidden;
        }

        /* Overlay Gelap Kopi Mewah untuk Keterbacaan Teks */
        .editorial-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                145deg,
                rgba(12, 15, 20, 0.88) 0%,
                rgba(12, 15, 20, 0.94) 50%,
                rgba(24, 18, 13, 0.96) 100%
            );
            z-index: 1;
        }

        .editorial-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Header Brand */
        .brand-header {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-logo {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(192, 142, 92, 0.6);
            background: #ffffff;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .brand-name {
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .brand-sub {
            font-size: 12px;
            color: var(--bronze);
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 600;
        }

        /* Editorial Body */
        .editorial-body {
            max-width: 520px;
            margin: 60px 0;
        }

        .quote-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--bronze);
            margin-bottom: 20px;
        }

        .quote-badge::before {
            content: "";
            display: inline-block;
            width: 24px;
            height: 1.5px;
            background: var(--bronze);
        }

        .editorial-quote {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            line-height: 1.5;
            font-weight: 500;
            color: #ffffff;
            margin-bottom: 20px;
            letter-spacing: -0.3px;
        }

        .editorial-quote em {
            font-style: italic;
            font-weight: 600;
            color: #dfb082;
        }

        .editorial-author {
            font-size: 14px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Footer Metrik & Lokasi */
        .editorial-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 28px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .info-pill {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-pill-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .info-pill-value {
            font-size: 13.5px;
            color: #e2e8f0;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #22c55e;
            box-shadow: 0 0 8px rgba(34, 197, 94, 0.7);
        }

        /* ================= SISI KANAN: FORM UTILITY ================= */
        .form-panel {
            flex: 1;
            background-color: var(--bg-canvas);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 56px 64px;
            position: relative;
        }

        .form-container {
            width: 100%;
            max-width: 420px;
            margin: auto;
        }

        .form-top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 48px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .back-link:hover {
            color: var(--text-primary);
            transform: translateX(-3px);
        }

        .gate-tag {
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border-subtle);
            color: var(--text-secondary);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Header Form */
        .form-header {
            margin-bottom: 36px;
        }

        .role-badge {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            color: var(--bronze);
            background: var(--bronze-soft);
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .form-title {
            font-size: 26px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            letter-spacing: -0.4px;
        }

        .form-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Alert */
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13.5px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        /* Form Inputs */
        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: var(--text-muted);
            font-size: 17px;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-input {
            width: 100%;
            height: 50px;
            background-color: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 10px;
            padding: 0 46px 0 46px;
            color: var(--text-primary);
            font-family: inherit;
            font-size: 14.5px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--bronze);
            background-color: var(--bg-elevated);
            box-shadow: 0 0 0 3px rgba(192, 142, 92, 0.2);
        }

        .form-input:focus ~ .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: var(--bronze);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 17px;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--text-primary);
        }

        /* Remember Me */
        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            font-size: 13.5px;
            color: var(--text-secondary);
        }

        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-input {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 1.5px solid #334155;
            border-radius: 5px;
            background: var(--bg-surface);
            cursor: pointer;
            display: grid;
            place-content: center;
            transition: all 0.15s ease;
        }

        .checkbox-input:checked {
            background-color: var(--bronze);
            border-color: var(--bronze);
        }

        .checkbox-input:checked::before {
            content: "\F26E";
            font-family: "bootstrap-icons";
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
        }

        /* Tombol Submit Elegan */
        .btn-submit {
            width: 100%;
            height: 50px;
            background-color: var(--bronze);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 4px 16px rgba(192, 142, 92, 0.25);
        }

        .btn-submit:hover {
            background-color: var(--bronze-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(192, 142, 92, 0.35);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            font-size: 17px;
            transition: transform 0.2s ease;
        }

        .btn-submit:hover i {
            transform: translateX(3px);
        }

        /* Footer Copyright */
        .form-footer {
            margin-top: 48px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ================= RESPONSIVE DESIGN ================= */
        @media (max-width: 991.98px) {
            .split-layout {
                flex-direction: column;
            }

            .editorial-panel {
                flex: none;
                padding: 40px 24px 32px 24px;
                min-height: auto;
            }

            .editorial-body {
                margin: 28px 0;
            }

            .editorial-quote {
                font-size: 22px;
            }

            .editorial-footer {
                display: none;
            }

            .form-panel {
                flex: 1;
                padding: 40px 24px;
            }

            .form-top-nav {
                margin-bottom: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="split-layout">
        
        <!-- SISI KIRI: BRAND & EDITORIAL CAFE -->
        <aside class="editorial-panel">
            <div class="editorial-overlay"></div>
            
            <div class="editorial-content">
                <!-- Header Brand Master Cafe -->
                <div class="brand-header">
                    <img src="{{ asset('images/logo.png') }}" alt="Master Cafe Logo" class="brand-logo">
                    <div>
                        <div class="brand-name">Master Cafe</div>
                        <div class="brand-sub">Executive Authority</div>
                    </div>
                </div>

                <!-- Kutipan Nilai & Karakter Kafe -->
                <div class="editorial-body">
                    <div class="quote-badge">Filosofi Operasional</div>
                    <blockquote class="editorial-quote">
                        &ldquo;Secangkir kopi istimewa bermula dari <em>ketelitian rasa</em>; bisnis kafe yang bertumbuh teguh bertumpu pada <em>presisi data</em>.&rdquo;
                    </blockquote>
                    <p class="editorial-author">
                        <i class="bi bi-shield-lock text-warning"></i>
                        Portal Eksklusif Manajemen & Pemilik Bisnis
                    </p>
                </div>

                <!-- Footer Informasi Kafe & Waktu Real-Time -->
                <div class="editorial-footer">
                    <div class="info-pill">
                        <span class="info-pill-label">Lokasi Utama</span>
                        <span class="info-pill-value">Bengkalis, Riau &bull; Sejak 2020</span>
                    </div>

                    <div class="info-pill" style="text-align: right;">
                        <span class="info-pill-label">Status Server POS</span>
                        <span class="info-pill-value">
                            <span class="status-dot"></span>
                            Terhubung &bull; <span id="liveClockWIB">WIB</span>
                        </span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- SISI KANAN: FORM LOGIN OTENTIK & BERSIH -->
        <main class="form-panel">
            <div class="form-container">
                
                <div class="form-top-nav">
                    <a href="{{ url('/') }}" class="back-link">
                        <i class="bi bi-arrow-left"></i>
                        <span>Ke Beranda Kafe</span>
                    </a>
                    <span class="gate-tag">Jalur Khusus Terverifikasi</span>
                </div>

                <div class="form-header">
                    <span class="role-badge">Portal Pemilik</span>
                    <h1 class="form-title">Selamat Datang</h1>
                    <p class="form-desc">Masukkan email & kata sandi otoritas untuk mengakses dashboard keuangan, omzet, dan laporan kafe.</p>
                </div>

                @if ($errors->any())
                    <div class="alert-error">
                        <i class="bi bi-exclamation-triangle-fill" style="flex-shrink: 0; font-size: 16px;"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('owner.login.submit') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label" for="email">Email Pemilik</label>
                        <div class="input-wrapper">
                            <i class="bi bi-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" 
                                   placeholder="pemilik@mastercafe.com" required autofocus value="{{ old('email') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Kata Sandi</label>
                        <div class="input-wrapper">
                            <i class="bi bi-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" 
                                   placeholder="Masukkan kata sandi" required>
                            <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Lihat kata sandi">
                                <i class="bi bi-eye-slash" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                            <span>Ingat sesi di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <span>Masuk ke Dashboard</span>
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </form>

                <footer class="form-footer">
                    &copy; {{ date('Y') }} Master Cafe. Seluruh Hak Cipta Dilindungi.
                </footer>
            </div>
        </main>

    </div>

    <script>
        // Password Visibility Toggle
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                if (isPassword) {
                    passwordInput.setAttribute('type', 'text');
                    toggleIcon.className = 'bi bi-eye';
                    toggleBtn.setAttribute('aria-label', 'Sembunyikan kata sandi');
                } else {
                    passwordInput.setAttribute('type', 'password');
                    toggleIcon.className = 'bi bi-eye-slash';
                    toggleBtn.setAttribute('aria-label', 'Lihat kata sandi');
                }
            });
        }

        // Live Clock (WIB / Bengkalis Time)
        function updateLiveClock() {
            const clockEl = document.getElementById('liveClockWIB');
            if (!clockEl) return;
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            clockEl.textContent = `${hours}:${minutes} WIB`;
        }
        updateLiveClock();
        setInterval(updateLiveClock, 30000);
    </script>
</body>
</html>
