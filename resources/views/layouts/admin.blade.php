<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard Admin - Master Cafe POS</title>
    @include("layouts.includes.head-assets")
            <style>
        .admin-layout { display: flex; flex-direction: column; height: 100vh; overflow: hidden; margin: 0; padding: 0; }
        .admin-topbar { padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-subtle); background: var(--gradient-surface); z-index: 1050; flex-shrink: 0; }
        .admin-sidebar { width: 280px; flex-shrink: 0; background: var(--gradient-surface); border-right: 1px solid var(--border-subtle); height: 100%; overflow-y: auto; padding: 1.5rem 1rem; display: flex; flex-direction: column; }
        .admin-sidebar .nav-link { color: var(--text-muted); padding: 0.75rem 1rem; border-radius: 0.5rem; margin-bottom: 0.5rem; font-weight: 500; transition: all 0.2s ease; display: flex; align-items: center; }
        .admin-sidebar .nav-link.active { background: var(--gradient-bronze) !important; color: #ffffff !important; box-shadow: 0 4px 16px rgba(192, 142, 92, 0.25); font-weight: 600; }
        .admin-sidebar .nav-link i { font-size: 1.25rem; width: 24px; margin-right: 16px; }
        .admin-main-wrapper { display: flex; flex: 1; overflow: hidden; } 
        .admin-content { flex: 1; padding: 2rem; background: var(--bg-base); overflow-y: auto; }
        .nav-section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 700; margin: 1.5rem 0 0.5rem 1rem; opacity: 0.7; }
        @media (max-width: 991.98px) { .admin-sidebar { position: fixed; transform: translateX(-100%); z-index: 1040; transition: transform 0.3s ease; width: 280px; } .admin-sidebar.show { transform: translateX(0); } .admin-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1035; } .admin-overlay.show { display: block; } .admin-main-wrapper { display: flex; flex: 1; overflow: hidden; } .admin-content { padding: 1rem; } }
    </style>
</head>
<body>
    <div id="app" class="admin-layout">
        <div class="admin-topbar">
            <div class="brand">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 40px; width: 40px; object-fit: cover;"> Master Cafe POS
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Notification Bell -->
                <div class="dropdown">
                    <button class="btn btn-link text-white text-decoration-none position-relative p-0" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell-fill fs-5" style="color: #c08e5c;"></i>
                        @if(isset($stokMenipisCount) && $stokMenipisCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $stokMenipisCount }}
                            </span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0  text-white" aria-labelledby="notificationDropdown" style="width: 300px; max-height: 400px; overflow-y: auto;">
                        <li><h6 class="dropdown-header fw-bold text-white">Notifikasi Stok Menipis</h6></li>
                        @if(isset($stokMenipisCount) && $stokMenipisCount > 0)
                            @foreach($menuMenipis as $menu)
                                <li>
                                    <a class="dropdown-item d-flex text-white justify-content-between align-items-center py-2" href="{{ route('admin.menu.index') }}">
                                        <div>
                                            <i class="bi bi-box-seam text-warning me-2"></i> {{ $menu->nama_menu }}
                                        </div>
                                        <span class="badge bg-danger rounded-pill">{{ $menu->stok }}</span>
                                    </a>
                                </li>
                            @endforeach
                            @foreach($bahanMenipis as $bahan)
                                <li>
                                    <a class="dropdown-item d-flex text-white justify-content-between align-items-center py-2" href="{{ route('admin.stok.index') }}">
                                        <div>
                                            <i class="bi bi-layers text-warning me-2"></i> {{ $bahan->nama_bahan }}
                                        </div>
                                        <span class="badge bg-danger rounded-pill">{{ $bahan->stok }}</span>
                                    </a>
                                </li>
                            @endforeach
                        @else
                            <li><span class="dropdown-item text-secondary small text-center py-3">Semua stok aman</span></li>
                        @endif
                    </ul>
                </div>

                <button class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" onclick="document.getElementById('logout-form').submit();">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </button>
            </div>
        </div>

        <div class="admin-main-wrapper">
            <aside class="admin-sidebar">
                <div class="mb-4">
                    <div class="brand-title d-flex align-items-center mb-1">
                        <i class="bi bi-layout-text-sidebar-reverse me-2"></i>
                        Admin Panel
                    </div>
                    <div class="brand-subtitle">Admin Warung</div>
                </div>

                <nav class="nav flex-column gap-1 mb-3">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                        <i class="bi bi-house-door-fill me-2"></i> Dashboard
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.meja.*') ? 'active' : '' }}" href="{{ route('admin.meja.index') }}">
                        <i class="bi bi-display me-2"></i> Meja & QR Code
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.menu.*') ? 'active' : '' }}" href="{{ route('admin.menu.index') }}">
                        <i class="bi bi-list-ul me-2"></i> Produk
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.stok.*') ? 'active' : '' }}" href="{{ route('admin.stok.index') }}">
                        <i class="bi bi-box-seam me-2"></i> Stok
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.kasir.*') ? 'active' : '' }}" href="{{ route('admin.kasir.index') }}">
                        <i class="bi bi-people-fill me-2"></i> Kasir
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.permintaan.*') ? 'active' : '' }}" href="{{ route('admin.permintaan.index') }}">
                        <i class="bi bi-cart-check-fill me-2"></i> Permintaan Belanja
                        @php
                            $pendingReq = \App\Models\PermintaanBelanja::where('status', 'menunggu')->count();
                        @endphp
                        @if($pendingReq > 0)
                            <span class="badge bg-danger ms-auto rounded-pill">{{ $pendingReq }}</span>
                        @endif
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="bi bi-person-lines-fill me-2"></i> Pengguna (User)
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.void_logs.index') ? 'active' : '' }}" href="{{ route('admin.void_logs.index') }}">
                        <i class="bi bi-journal-x me-2"></i> Log Void
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.activity_logs.index') ? 'active' : '' }}" href="{{ route('admin.activity_logs.index') }}">
                        <i class="bi bi-clock-history me-2"></i> Log Aktivitas
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">
                        <i class="bi bi-graph-up-arrow me-2"></i> Laporan
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.absensi.*') ? 'active' : '' }}" href="{{ route('admin.absensi.index') }}">
                        <i class="bi bi-calendar-check-fill me-2"></i> Laporan Absensi
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.pengeluaran.*') ? 'active' : '' }}" href="{{ route('admin.pengeluaran.index') }}">
                        <i class="bi bi-wallet2 me-2"></i> Pengeluaran
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.promo.*') ? 'active' : '' }}" href="{{ route('admin.promo.index') }}">
                        <i class="bi bi-tag-fill me-2"></i> Promo
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                        <i class="bi bi-chat-left-text-fill me-2"></i> Ulasan
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                        <i class="bi bi-gear-fill me-2"></i> Pengaturan
                    </a>
                </nav>
            </aside>

            <main class="admin-content">
                @yield('content')
            </main>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>
    @include('components.webpush')
    @stack('scripts')

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









