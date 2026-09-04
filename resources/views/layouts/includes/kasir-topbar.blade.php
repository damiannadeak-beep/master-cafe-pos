<nav class="navbar navbar-expand-lg kasir-navbar shadow-sm py-3 d-print-none">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="{{ route('kasir.pos') }}">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm me-2" style="height: 40px; width: 40px; object-fit: cover;">
                    Kasir Pos
                </a>
                
                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#kasirNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse justify-content-center mt-3 mt-lg-0" id="kasirNav">
                    <ul class="navbar-nav mb-2 mb-lg-0 gap-3 text-center text-lg-start">
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pos') ? 'active' : '' }}" href="{{ route('kasir.pos') }}">
                                <i class="bi bi-cart-plus me-1"></i> Transaksi Kasir
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ request()->routeIs('kasir.pesanan_aktif') ? 'active' : '' }}" href="{{ route('kasir.pesanan_aktif') }}">
                                <i class="bi bi-bell me-1"></i> Pesanan Aktif
                                <span class="badge bg-danger rounded-pill ms-1 shadow-sm" id="badge-active-orders" style="display: none;">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link fw-bold dropdown-toggle {{ request()->routeIs('kasir.shift_report', 'kasir.pengeluaran.*', 'kasir.stok.*', 'kasir.permintaan.*', 'kasir.meja.*', 'kasir.absensi.*') ? 'active' : '' }}" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-grid-fill me-1"></i> Menu Lainnya
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark shadow-sm border-0" aria-labelledby="navbarDropdown" style="background-color: var(--bg-surface); border: 1px solid var(--border-subtle) !important;">
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.stok.*') ? 'active' : '' }}" href="{{ route('kasir.stok.index') }}">
                                        <i class="bi bi-box-seam me-2"></i> Update Stok
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.permintaan.*') ? 'active' : '' }}" href="{{ route('kasir.permintaan.index') }}">
                                        <i class="bi bi-bag-plus me-2"></i> Permintaan Belanja
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.pengeluaran.*') ? 'active' : '' }}" href="{{ route('kasir.pengeluaran.index') }}">
                                        <i class="bi bi-wallet2 me-2"></i> Pengeluaran
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.shift_report') ? 'active' : '' }}" href="{{ route('kasir.shift_report') }}">
                                        <i class="bi bi-journal-text me-2"></i> Laporan Shift
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.meja.*') ? 'active' : '' }}" href="{{ route('kasir.meja.index') }}">
                                        <i class="bi bi-grid-3x3-gap-fill me-2"></i> Manajemen Meja
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item text-white d-flex align-items-center {{ request()->routeIs('kasir.absensi.*') ? 'active' : '' }}" href="{{ route('kasir.absensi.index') }}">
                                        <i class="bi bi-geo-alt-fill me-2"></i> Absensi Shift
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center gap-3">
                    
                    <div class="navbar-text small text-white">{{ auth()->user()->name ?? 'Kasir' }}</div>
                    <a class="btn btn-outline-light btn-sm rounded-pill d-inline-flex align-items-center" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                </div>
            </div>
        </nav>

