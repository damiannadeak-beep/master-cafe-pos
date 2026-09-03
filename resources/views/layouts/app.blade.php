<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Master Cafe</title>
    
    @include("layouts.includes.head-assets")
        <style>
        @import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Rye&family=Outfit:wght@300;400;600&display=swap');
        body, button, input, select, textarea, .nav-link {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Rye', serif !important;
        }
        .font-cursive {
            font-family: 'Great Vibes', cursive !important;
        }
        .app-navbar { background: var(--gradient-surface) !important; border-bottom: 1px solid var(--border-subtle); position: relative; z-index: 1050; box-shadow: 0 4px 20px rgba(45, 26, 17, 0.25); }
    </style>
</head>
<body class="text-light" data-bs-theme="dark" style="background-color: #0e1217 !important;">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark shadow-sm sticky-top">
            <div class="container">
                <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">
                    <i class="bi bi-cup-hot-fill me-1"></i> Master Cafe
                </a>
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-semibold">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active text-primary' : '' }}" href="/">Beranda</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('katalog') ? 'active text-primary' : '' }}" href="/katalog">Katalog Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('lokasi') ? 'active text-primary' : '' }}" href="/lokasi">Lokasi</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('kontak') ? 'active text-primary' : '' }}" href="/kontak">Kontak</a>
                        </li>
                    </ul>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item d-flex align-items-center">
                                @if (Route::has('login'))
                                    <a class="nav-link py-1" href="{{ route('login') }}">Masuk</a>
                                @endif
                                @if (Route::has('register'))
                                    <a class="btn btn-primary btn-sm rounded-pill px-3 ms-2 fw-semibold py-1" href="{{ route('register') }}">Daftar</a>
                                @endif
                            </li>
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold text-light d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    @if(Auth::user()->foto)
                                        <img src="{{ asset('uploads/profil/' . Auth::user()->foto) }}" alt="Foto" class="rounded-circle me-2 border border-primary" style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <i class="bi bi-person-circle text-primary me-2 fs-5"></i>
                                    @endif
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="navbarDropdown">
                                    
                                    @role('pemilik')
                                        <a class="dropdown-item" href="/admin/dashboard"><i class="bi bi-speedometer2 me-2"></i> Dashboard Admin</a>
                                    @endrole

                                    @role('kasir')
                                        <a class="dropdown-item" href="/kasir/pos"><i class="bi bi-calculator me-2"></i> Mesin POS Kasir</a>
                                    @endrole

                                    @role('konsumen')
                                        <a class="dropdown-item" href="/konsumen/profil"><i class="bi bi-person-lines-fill me-2"></i> Profil & Pesanan Saya</a>
                                    @endrole
                                    
                                    <hr class="dropdown-divider">

                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        @if(isset($isStoreOpen) && !$isStoreOpen && (!auth()->check() || auth()->user()->hasRole('konsumen')))
            <div class="alert alert-warning text-center rounded-0 mb-0 border-0 shadow-sm px-3" style="z-index: 1040; position: relative;">
                <i class="bi bi-info-circle-fill me-1"></i>
                <strong>Perhatian:</strong> Warung saat ini belum buka. Anda tetap dapat melihat menu & memesan, namun pesanan Anda akan diproses setelah kasir kami tiba.
            </div>
        @endif

        <main class="py-0">
            @yield('content')
        </main>
        
        <footer class="text-center py-4 mt-auto w-100 border-top" style="border-color: #21262d !important; padding-bottom: env(safe-area-inset-bottom, 120px) !important;">
            <p class="font-cursive text-primary fs-3 mb-0">Master Cafe</p>
            <small class="text-secondary">&copy; {{ date('Y') }} Master Cafe. Hak Cipta Dilindungi.</small>
        </footer>
    </div>
    
    @yield('scripts')
    @include('components.webpush')



    <!-- Global UI/UX Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast Notification System
            window.showToast = function(message, type = 'success') {
                const toastContainer = document.getElementById('toast-container') || (function() {
                    const div = document.createElement('div');
                    div.id = 'toast-container';
                    div.className = 'toast-container position-fixed bottom-0 end-0 p-3 z-modal';
                    document.body.appendChild(div);
                    return div;
                })();

                const toastId = 'toast-' + Date.now();
                const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
                const borderColor = type === 'success' ? '#986c43' : '#dc3545';
                
                const toastHtml = 
                    <div id=" + toastId + " class="toast toast-bronze align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-left: 4px solid  + borderColor +  !important; background-color: #161b22; color: #fff;">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center">
                                <i class="bi  + icon +  me-2" style="font-size: 20px; color:  + borderColor + ;"></i>
                                <span style="font-size: 16px;"> + message + </span>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto btn-touch" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                ;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });
            };
            
            // Override native alert (Optional but useful for catching unmigrated alerts)
            window.nativeAlert = window.alert;
            window.alert = function(msg) {
                window.showToast(msg, 'warning');
            };
        });
    </script>
</body>
</html>







