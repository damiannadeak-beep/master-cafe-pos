    <meta name="color-scheme" content="dark">
    <meta name="theme-color" content="#0e1217">

    <!-- Search Engine Optimization (SEO) & Social Meta Tags -->
    <meta name="description" content="Master Cafe - Kafe & Resto santai di Bengkalis. Nikmati aneka kopi pilihan, hidangan istimewa, suasana nyaman, dan kemudahan pemesanan mandiri online (QR Order).">
    <meta name="keywords" content="Master Cafe, Master Cafe Bengkalis, Cafe Bengkalis, Kopi Bengkalis, Kuliner Bengkalis, Tempat Nongkrong Bengkalis, Resto Bengkalis">
    <meta name="author" content="Master Cafe">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Master Cafe - Kafe & Resto Modern Bengkalis">
    <meta property="og:description" content="Kunjungi Master Cafe di Bengkalis. Cita rasa istimewa dengan kenyamanan tempat terbaik untuk santai dan berkumpul.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Master Cafe - Kafe & Resto Modern Bengkalis">
    <meta name="twitter:description" content="Kunjungi Master Cafe di Bengkalis. Cita rasa istimewa dengan kenyamanan tempat terbaik untuk santai dan berkumpul.">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">

    <!-- JSON-LD Structured Data for Google Rich Snippets -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "CafeOrCoffeeShop",
      "name": "Master Cafe",
      "image": "{{ asset('images/logo.png') }}",
      "@@id": "{{ url('/') }}",
      "url": "{{ url('/') }}",
      "telephone": "{{ \App\Models\Setting::getVal('kontak_wa', '+62 812-3456-7890') }}",
      "priceRange": "Rp 5.000 - Rp 50.000",
      "address": {
        "@@type": "PostalAddress",
        "streetAddress": "{{ \App\Models\Setting::getVal('lokasi_utama_alamat', 'Jl. Bantan, Senggoro') }}",
        "addressLocality": "Bengkalis",
        "addressRegion": "Riau",
        "postalCode": "28711",
        "addressCountry": "ID"
      },
      "geo": {
        "@@type": "GeoCoordinates",
        "latitude": {{ \App\Models\Setting::getVal('cafe_latitude') ?: (\App\Models\Setting::getVal('warung_latitude') ?: 1.4828) }},
        "longitude": {{ \App\Models\Setting::getVal('cafe_longitude') ?: (\App\Models\Setting::getVal('warung_longitude') ?: 102.1332) }}
      },
      "servesCuisine": "Indonesian, Coffee, Cafe"
    }
    </script>
    <style>
        :root, html, body {
            background-color: #0e1217 !important;
            color: #ffffff !important;
            color-scheme: dark;
        }
    </style>
    <link rel="icon" href="{{ asset('favicon-32x32.png') }}?v=2" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-16x16.png') }}?v=2" type="image/png" sizes="16x16">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Alex+Brush&family=Caveat:wght@600&family=Great+Vibes&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Rye&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="manifest" href="/manifest.json">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <script src="/js/pwa-offline.js"></script>
    <style>
        :root {
            --bg-base: #0e1217;
            --bg-surface: #161b22;
            --border-subtle: #21262d;
            --text-main: #ffffff;
            --text-muted: #b5bdc4;
            
            /* Premium Gradients */
            --gradient-bronze: linear-gradient(135deg, #986c43 0%, #c08e5c 100%);
            --gradient-bronze-hover: linear-gradient(135deg, #d49b78 0%, #a26436 100%);
            --gradient-dark-bronze: linear-gradient(135deg, #2c1e15 0%, #17110d 100%);
            --gradient-surface: linear-gradient(145deg, #1e2229 0%, #16191f 100%);
            
            --bs-primary: #c08e5c;
            --bs-primary-rgb: 178, 122, 77;
        }
        /* 60fps Native Touch & Smooth Scrolling */
        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }
        body {
            background: #0e1217;
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            overscroll-behavior-y: contain;
        }
        * {
            -webkit-tap-highlight-color: transparent;
        }
        button, a, input, select, textarea {
            touch-action: manipulation;
        }
        /* Hardware Acceleration for Interactive Elements */
        .btn, .nav-link, .card, .badge, .form-control, .form-select {
            transform: translateZ(0);
            backface-visibility: hidden;
        }
        .overflow-auto, .overflow-y-auto, .cart-list, .table-responsive {
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }
        .hover-lift {
            transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        body, button, input, select, textarea, h1, h2, h3, h4, h5, h6, .nav-link, .navbar-brand {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        
        /* Core Shared Utilities */
        .toast-container {
            z-index: 1095 !important;
            pointer-events: none;
        }
        .toast.master-toast {
            pointer-events: auto;
            position: relative;
            background: rgba(22, 27, 34, 0.97) !important;
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            border-radius: 16px !important;
            box-shadow: 0 16px 40px -6px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05) !important;
            overflow: hidden;
            color: #ffffff !important;
            padding: 0 !important;
            max-width: 420px;
            width: calc(100vw - 32px);
            margin-bottom: 10px;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
        }
        .master-toast-indicator {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 5px;
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
        }
        .master-toast-content {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 14px 13px 16px;
        }
        .master-toast-icon {
            width: 36px;
            height: 36px;
            min-width: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .master-toast-text {
            flex: 1;
            min-width: 0;
            font-size: 0.88rem;
            line-height: 1.45;
            font-weight: 500;
            color: #f0f6fc;
            word-break: break-word;
        }
        .master-toast-close {
            width: 28px;
            height: 28px;
            min-width: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #8b949e;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 4px;
            padding: 0;
            flex-shrink: 0;
        }
        .master-toast-close:hover {
            background: rgba(255, 255, 255, 0.16);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }
        @media (max-width: 575.98px) {
            .toast-container {
                left: 0 !important;
                right: 0 !important;
                bottom: 14px !important;
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 0 14px !important;
            }
            .toast.master-toast {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        .toast-bronze { background-color: #161b22; border: 1px solid #986c43; color: #ffffff; }
        
        .navbar-brand-logo { display: flex; align-items: center; justify-content: center; position: relative; text-align: center; width: 56px; height: 56px; background-color: #ffffff; border-radius: 50%; text-decoration: none; margin-right: 16px; }
        .navbar-brand-logo .text-master { font-family: 'Rye', serif !important; font-size: 10px; color: #000000; letter-spacing: 1px; margin-bottom: -4px; z-index: 2; line-height: 1; }
        .navbar-brand-logo .text-cafe { font-family: 'Alex Brush', cursive !important; font-size: 14px; color: #000000; margin-bottom: 0px; z-index: 2; line-height: 1; }
        .navbar-brand-logo .text-since { font-family: 'Caveat', cursive !important; font-size: 6px; color: #000000; letter-spacing: 0.5px; z-index: 2; line-height: 1; }
        .font-cursive { font-family: 'Alex Brush', cursive !important; }
        .navbar-brand-logo .aksen-gradasi { position: absolute; width: 48px; height: 48px; border-radius: 50%; border: 2px solid transparent; background: var(--gradient-bronze) border-box; -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0); -webkit-mask-composite: destination-out; mask-composite: exclude; clip-path: polygon(0 0, 100% 0, 100% 80%, 0 20%); }
        
                /* Bootstrap Dark Override */
        :root, [data-bs-theme="light"] { --bs-body-bg: var(--bg-base); --bs-body-color: var(--text-main); --bs-card-bg: var(--bg-surface); --bs-card-color: var(--text-main); --bs-card-border-color: var(--border-subtle); --bs-table-bg: var(--bg-surface); --bs-table-color: var(--text-main); --bs-border-color: var(--border-subtle); --bs-secondary-bg: var(--bg-surface); --bs-modal-bg: var(--bg-surface); --bs-dropdown-bg: var(--bg-surface); --bs-dropdown-color: var(--text-main); --bs-list-group-bg: var(--bg-surface); --bs-list-group-color: var(--text-main); --bs-list-group-border-color: var(--border-subtle); --bs-heading-color: var(--text-main); background-color: var(--bg-base) !important; color: var(--text-main) !important; }
        .card, .modal-content, .dropdown-menu { background-color: var(--bg-surface) !important; color: var(--text-main) !important; border-color: var(--border-subtle) !important; }
        .card, .admin-card, .kasir-card { overflow: hidden !important; }
        .table-responsive { border-radius: 0 !important; }
        .table thead th { background-color: rgba(192, 142, 92, 0.15) !important; }
        
        /* Fix Inputs */
        .form-control, .form-select { background-color: var(--bg-surface) !important; color: var(--text-main) !important; border-color: var(--border-subtle) !important; }
        .form-control::placeholder { color: rgba(255,255,255,0.4) !important; }
        .form-control:focus, .form-select:focus { background-color: var(--bg-surface) !important; color: var(--text-main) !important; border-color: var(--bs-primary) !important; box-shadow: 0 0 0 0.25rem rgba(192, 142, 92, 0.25) !important; }
        
        /* Fix Table */
        .table, .table-dark { --bs-table-bg: var(--bg-surface); --bs-table-color: var(--text-main); color: var(--text-main) !important; }
        .table th, .table-dark th { color: var(--text-main) !important; font-weight: 600; }
        
        /* Iconography Consistency */
        .btn .bi, .nav-link .bi, .dropdown-item .bi { font-size: 20px; } .btn:has(.bi) { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; } .btn-icon { width: 38px !important; height: 38px !important; min-width: 38px !important; padding: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; border-radius: 8px !important; flex-shrink: 0; } .btn-icon .bi { margin: 0 !important; font-size: 1.1rem !important; line-height: 1 !important; } .nav-link:has(.bi) { display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn .bi:not(:last-child), .nav-link .bi:not(:last-child), .dropdown-item .bi:not(:last-child) { margin-right: 8px !important; }
        body, h1, h2, h3, h4, h5, h6, .bi { -webkit-font-smoothing: antialiased !important; -moz-osx-font-smoothing: grayscale !important; }
        
        /* Colors */
        .text-secondary { color: var(--text-muted) !important; }
        .btn-outline-primary { color: #c08e5c !important; border-color: #c08e5c !important; }
        .btn-outline-primary:hover, .btn-outline-primary:focus { background-color: #c08e5c !important; color: #ffffff !important; }
        .btn-outline-secondary { color: var(--text-muted) !important; border-color: var(--border-subtle) !important; }
        .btn-outline-secondary:hover, .btn-outline-secondary:focus { background-color: var(--border-subtle) !important; color: #ffffff !important; }

        /* ============================================================
           MOBILE BOTTOM SHEET SYSTEM (<768px)
           ============================================================ */
        @media (max-width: 767.98px) {
            body .modal.modal-bottom-sheet {
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
            }
            body .modal.modal-bottom-sheet .modal-dialog,
            body .modal.modal-bottom-sheet .modal-dialog-bottom-sheet {
                position: fixed !important;
                bottom: 0 !important;
                left: 0 !important;
                right: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                height: auto !important;
                display: flex !important;
                align-items: flex-end !important;
                justify-content: flex-end !important;
                transform: translate3d(0, 100%, 0) !important;
                transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1) !important;
                pointer-events: auto !important;
                z-index: 1060 !important;
            }
            body .modal.modal-bottom-sheet.show .modal-dialog,
            body .modal.modal-bottom-sheet.show .modal-dialog-bottom-sheet {
                transform: translate3d(0, 0, 0) !important;
            }
            body .modal.modal-bottom-sheet .modal-content {
                border-bottom-left-radius: 0 !important;
                border-bottom-right-radius: 0 !important;
                border-top-left-radius: 1.5rem !important;
                border-top-right-radius: 1.5rem !important;
                border-left: none !important;
                border-right: none !important;
                border-bottom: none !important;
                border-top: 1px solid rgba(255, 255, 255, 0.12) !important;
                max-height: 88vh !important;
                width: 100% !important;
                box-shadow: 0 -12px 40px rgba(0, 0, 0, 0.75) !important;
                background-color: #161b22 !important;
                display: flex !important;
                flex-direction: column !important;
            }
            .bottom-sheet-drag-handle-wrapper {
                cursor: grab;
                touch-action: none;
                user-select: none;
                -webkit-user-select: none;
            }
            .bottom-sheet-drag-handle {
                width: 48px;
                height: 5px;
                background: rgba(255, 255, 255, 0.38);
                border-radius: 999px;
                margin: 8px auto 4px auto;
                cursor: grab;
                flex-shrink: 0;
                transition: background 0.2s ease, transform 0.2s ease;
            }
            .bottom-sheet-drag-handle:active,
            .modal-drag-zone:active .bottom-sheet-drag-handle {
                background: #c08e5c;
                transform: scaleX(1.15);
            }
        }
        @media (min-width: 768px) {
            body .modal.modal-bottom-sheet .modal-dialog,
            body .modal.modal-bottom-sheet .modal-dialog-bottom-sheet {
                max-width: 420px;
                margin: 1.75rem auto;
                display: flex;
                align-items: center;
                min-height: calc(100% - 3.5rem);
            }
            body .modal.modal-bottom-sheet .bottom-sheet-drag-handle-wrapper {
                display: none !important;
            }
        }
    </style>


