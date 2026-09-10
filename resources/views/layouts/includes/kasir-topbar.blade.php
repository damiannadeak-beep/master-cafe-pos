<nav class="navbar navbar-expand-lg kasir-navbar shadow-sm py-2 px-1 d-print-none">
    <div class="container-fluid">
        <!-- Logo & Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('kasir.pos') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 38px; width: 38px; object-fit: cover;">
            <span class="fw-bold tracking-tight">Tablet Waitress</span>
        </a>
        
        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 shadow-none text-light" type="button" data-bs-toggle="collapse" data-bs-target="#kasirNav" aria-controls="kasirNav" aria-expanded="false">
            <i class="bi bi-list fs-2"></i>
        </button>
        
        <!-- Navigation Center -->
        <div class="collapse navbar-collapse justify-content-center mt-3 mt-lg-0" id="kasirNav">
            <div class="kasir-nav-wrapper d-flex flex-column flex-lg-row align-items-center gap-2">
                <!-- Layanan Utama (Akses Langsung 1-Klik) -->
                <div class="d-flex align-items-center gap-1 p-1 bg-surface-dark rounded-pill border border-subtle">
                    <a class="nav-pill-btn {{ request()->routeIs('kasir.pos') ? 'active' : '' }}" href="{{ route('kasir.pos') }}">
                        <i class="bi bi-cart-plus me-1"></i> Pesanan Manual
                    </a>
                    <a class="nav-pill-btn {{ request()->routeIs('kasir.pesanan_aktif') ? 'active' : '' }}" href="{{ route('kasir.pesanan_aktif') }}">
                        <i class="bi bi-bell me-1"></i> Pesanan Aktif
                        <span class="badge bg-danger rounded-pill ms-1 shadow-sm" id="badge-active-orders" style="display: none;">0</span>
                    </a>
                    <a class="nav-pill-btn {{ request()->routeIs('kasir.meja.*') ? 'active' : '' }}" href="{{ route('kasir.meja.index') }}">
                        <i class="bi bi-grid-3x3-gap-fill me-1"></i> Monitor Meja
                    </a>
                </div>

                <!-- Dropdown Kelompok Operasional Kas & Alat -->
                <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                    <!-- Dropdown Kas & Shift -->
                    <div class="dropdown">
                        <button class="nav-pill-btn dropdown-toggle {{ request()->routeIs('kasir.shift_report', 'kasir.pengeluaran.*') ? 'active' : '' }}" type="button" id="dropdownKasShift" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-wallet2 me-1"></i> Kas & Shift
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark kasir-dropdown shadow-lg border-0" aria-labelledby="dropdownKasShift">
                            <li>
                                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('kasir.pengeluaran.*') ? 'active' : '' }}" href="{{ route('kasir.pengeluaran.index') }}">
                                    <i class="bi bi-cash-stack me-2 text-warning"></i> Catat Pengeluaran
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('kasir.shift_report') ? 'active' : '' }}" href="{{ route('kasir.shift_report') }}">
                                    <i class="bi bi-journal-text me-2 text-info"></i> Laporan Tutup Shift
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Dropdown Alat & Stok -->
                    <div class="dropdown">
                        <button class="nav-pill-btn dropdown-toggle {{ request()->routeIs('kasir.stok.*', 'kasir.permintaan.*', 'kasir.absensi.*') ? 'active' : '' }}" type="button" id="dropdownAlatStok" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-sliders me-1"></i> Alat & Stok
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark kasir-dropdown shadow-lg border-0" aria-labelledby="dropdownAlatStok">
                            <li>
                                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('kasir.stok.*') ? 'active' : '' }}" href="{{ route('kasir.stok.index') }}">
                                    <i class="bi bi-box-seam me-2 text-success"></i> Update Stok
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('kasir.permintaan.*') ? 'active' : '' }}" href="{{ route('kasir.permintaan.index') }}">
                                    <i class="bi bi-bag-plus me-2 text-primary"></i> Permintaan Belanja
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center {{ request()->routeIs('kasir.absensi.*') ? 'active' : '' }}" href="{{ route('kasir.absensi.index') }}">
                                    <i class="bi bi-geo-alt-fill me-2 text-danger"></i> Absensi Shift
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Kasir Status & Logout -->
        <div class="d-flex align-items-center gap-2">
            <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-1 bg-surface-dark border border-subtle rounded-pill">
                <span class="kasir-status-dot"></span>
                <span class="small fw-semibold text-light text-truncate" style="max-width: 140px;">
                    {{ auth()->user()->name ?? 'Kasir' }}
                </span>
            </div>
            <a class="btn btn-outline-danger btn-sm rounded-pill px-3 d-inline-flex align-items-center shadow-sm" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" title="Keluar dari sesi Kasir">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</nav>

