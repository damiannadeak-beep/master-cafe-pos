<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gerbang Kasir POS — Master Cafe</title>
    
    <!-- Google Fonts as per DESIGN_SYSTEM.md -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Caveat:wght@600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Rye&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --bg-base: #0e1217;
            --bg-surface: #161b22;
            --border-subtle: #21262d;
            --gradient-bronze: linear-gradient(135deg, #986c43 0%, #c08e5c 100%);
            --bronze-accent: #c08e5c;
            --text-main: #ffffff;
            --text-muted: #a0aab2;
            --pos-emerald: #10b981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: var(--bg-base);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
            -webkit-font-smoothing: antialiased;
        }

        .ambient-bg {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 50% 15%, rgba(192, 142, 92, 0.1) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .login-card {
            background-color: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 32px 24px;
            width: 100%;
            max-width: 416px;
            position: relative;
            z-index: 10;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
        }

        @media (min-width: 480px) {
            .login-card {
                padding: 40px 32px;
            }
        }

        /* Brand Logo Badge adhering to DESIGN_SYSTEM.md */
        .brand-badge {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            text-align: center;
            width: 64px;
            height: 64px;
            background-color: #ffffff;
            border-radius: 50%;
            margin: 0 auto 16px auto;
            box-shadow: 0 8px 24px rgba(192, 142, 92, 0.25);
        }

        .brand-badge .text-master {
            font-family: 'Rye', serif;
            font-size: 11px;
            color: #000000;
            letter-spacing: 1px;
            margin-bottom: -4px;
            z-index: 2;
            line-height: 1;
        }

        .brand-badge .text-cafe {
            font-family: 'Alex Brush', cursive;
            font-size: 16px;
            color: #000000;
            margin-bottom: 0px;
            z-index: 2;
            line-height: 1;
        }

        .brand-badge .text-since {
            font-family: 'Caveat', cursive;
            font-size: 7px;
            color: #000000;
            letter-spacing: 0.5px;
            z-index: 2;
            line-height: 1;
        }

        .brand-badge .aksen-gradasi {
            position: absolute;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 2px solid transparent;
            background: var(--gradient-bronze) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            clip-path: polygon(0 0, 100% 0, 100% 80%, 0 20%);
        }

        .portal-title {
            font-family: 'Rye', serif;
            font-size: 22px;
            letter-spacing: 0.5px;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .portal-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.75px;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: var(--text-muted);
            font-size: 18px;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            height: 48px;
            background-color: var(--bg-base);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 0 16px 0 44px;
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--bronze-accent);
            box-shadow: 0 0 0 3px rgba(192, 142, 92, 0.2);
            background-color: #12171e;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            padding: 4px;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: var(--text-main);
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
            color: var(--text-muted);
        }

        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-input {
            appearance: none;
            width: 18px;
            height: 18px;
            border: 1px solid var(--border-subtle);
            border-radius: 6px;
            background-color: var(--bg-base);
            cursor: pointer;
            display: grid;
            place-content: center;
            transition: all 0.2s;
        }

        .checkbox-input:checked {
            background: var(--gradient-bronze);
            border-color: var(--bronze-accent);
        }

        .checkbox-input:checked::before {
            content: "\F26E";
            font-family: "bootstrap-icons";
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
        }

        .btn-submit {
            width: 100%;
            height: 48px;
            background: var(--gradient-bronze);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 16px rgba(152, 108, 67, 0.35);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(152, 108, 67, 0.5);
            filter: brightness(1.06);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit .bi {
            font-size: 18px;
        }

        .alert-error {
            background-color: rgba(225, 29, 72, 0.1);
            border: 1px solid rgba(225, 29, 72, 0.3);
            color: #fda4af;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .footer-badge {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border-subtle);
            font-size: 12px;
            color: #626d79;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background-color: var(--pos-emerald);
            box-shadow: 0 0 8px var(--pos-emerald);
        }
    </style>
</head>
<body>
    <div class="ambient-bg"></div>

    <div class="login-card">
        <!-- Official Master Cafe Badge -->
        <div class="brand-badge">
            <div class="aksen-gradasi"></div>
            <span class="text-master">MASTER</span>
            <span class="text-cafe">Cafe</span>
            <span class="text-since">SINCE 2020</span>
        </div>

        <h1 class="portal-title">Gerbang Kasir</h1>
        <p class="portal-subtitle">Terminal Operasional Kasir & Layanan POS</p>

        @if ($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 16px; flex-shrink: 0;"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('kasir.login.submit') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="email">Email Akun Kasir</label>
                <div class="input-wrapper">
                    <i class="bi bi-person-badge input-icon"></i>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="kasir@mastercafe.com" required autofocus value="{{ old('email') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <div class="input-wrapper">
                    <i class="bi bi-key input-icon"></i>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="••••••••" required>
                    <button type="button" class="toggle-password" id="togglePasswordBtn" aria-label="Lihat kata sandi">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" class="checkbox-input" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat sesi perangkat kasir ini</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="bi bi-calculator"></i>
                Buka Terminal Kasir
            </button>
        </form>

        <div class="footer-badge">
            <span class="pulse-dot"></span>
            <span>Terminal Kasir Terproteksi</span>
        </div>
    </div>

    <script>
        // Password Visibility Toggle
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    </script>
</body>
</html>
