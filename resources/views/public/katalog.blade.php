@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="pt-5 pb-4 mb-5 position-relative overflow-hidden rounded-bottom-4 shadow-lg" style="background-color: #0e1217; border-bottom: 1px solid #21262d;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(178, 122, 77, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="position-absolute bottom-0 end-0 w-100 h-100" style="background: radial-gradient(circle at bottom left, rgba(178, 122, 77, 0.1) 0%, transparent 60%); pointer-events: none;"></div>
    
    <div class="container text-center position-relative z-index-1 mt-4">
        <h1 class="display-5 fw-bold mb-3 text-white" style="font-family: 'Rye', serif;">Katalog Menu</h1>
        <p class="fs-6 text-light opacity-75 mx-auto mb-4" style="max-width: 600px; font-weight: 300;">
            Temukan sajian istimewa yang dibuat dengan dedikasi tinggi. Silakan <strong>Scan QR Code</strong> di meja Anda untuk memulai pesanan.
        </p>
        
        @guest
            <div class="d-flex justify-content-center gap-3 mb-3">
                <a href="/login" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill shadow-sm" style="background-color: #c08e5c; border: none;">Login / Masuk</a>
                <a href="/register" class="btn btn-outline-light btn-sm fw-bold px-3 rounded-pill">Daftar Akun</a>
            </div>
            <p class="small text-light opacity-50 font-monospace">
                <i class="bi bi-info-circle me-1"></i> Login diperlukan untuk memesan mandiri
            </p>
        @else
            @role('konsumen')
                <div class="d-flex justify-content-center">
                    <a href="/konsumen/pilih-tipe" class="btn btn-primary fw-bold px-5 rounded-pill shadow-lg btn-lg btn-touch" style="background-color: #c08e5c; border: none; letter-spacing: 1px;">
                        <i class="bi bi-cart-plus me-2"></i> Mulai Pesan
                    </a>
                </div>
            @endrole
        @endguest
    </div>
</div>

<div class="container mb-5 pb-5">
    
    @if(isset($promos) && count($promos) > 0)
    <div class="alert border-0 shadow-lg rounded-4 mb-5 p-4" style="background: linear-gradient(135deg, #161b22 0%, #22262d 100%); border: 1px solid rgba(178, 122, 77, 0.3) !important;">
        <h5 class="fw-bold mb-3" style="color: #c08e5c; font-family: 'Rye', serif;">
            <i class="bi bi-stars me-2"></i> Promo Spesial Hari Ini
        </h5>
        <ul class="mb-0 ps-3">
            @foreach($promos as $promo)
                <li class="mb-3 text-white">
                    <strong class="fs-5">{{ $promo->title }}</strong> 
                    @if($promo->type == 'discount')
                        <span class="badge rounded-pill ms-2 align-middle" style="background-color: #c08e5c;">
                        Diskon {{ $promo->discount_type == 'percentage' ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }}
                        </span>
                    @elseif($promo->type == 'package')
                        <span class="badge rounded-pill ms-2 align-middle" style="background-color: #c08e5c;">Paket Khusus</span>
                        <span class="badge  text-white rounded-pill ms-1 align-middle"><i class="bi bi-tag-fill text-warning"></i> Cukup Rp {{ number_format($promo->value,0,',','.') }}</span>
                        <div class="mt-2 small">
                            <span class="text-secondary">Termasuk:</span> 
                            @foreach($promo->menus as $pm)
                                <span class="badge  text-light border border-secondary fw-normal px-2 py-1">{{ $pm->nama_menu }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if($promo->description)
                        <p class="mt-2 mb-0 text-secondary" style="font-size: 0.875rem;">{{ $promo->description }}</p>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Filter Kategori -->
    <div class="d-flex justify-content-center mb-5">
        <div class="rounded-pill p-1 shadow-sm d-inline-flex" role="group" style="background-color: #161b22; border: 1px solid #21262d;">
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold filter-btn active-filter" data-filter="semua" style="transition: all 0.3s;">Semua</button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary filter-btn" data-filter="makanan" style="transition: all 0.3s;">Makanan</button>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold text-secondary filter-btn" data-filter="minuman" style="transition: all 0.3s;">Minuman</button>
        </div>
    </div>

    <!-- Daftar Menu -->
    <div class="row g-4 align-items-stretch" id="menu-container">
        @forelse($menus as $menu)
        <div class="col-6 col-md-4 col-lg-3 menu-item" data-kategori="{{ strtolower($menu->kategori ?? 'makanan') }}">
            <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden hover-lift" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="position-relative">
                    <!-- Gambar -->
                    @if($menu->image)
                        <div class="text-center w-100 p-3" style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='https://placehold.co/600x450/14171c/4a5568?text=MASTER+CAFE';" alt="{{ $menu->nama_menu }}" style="object-fit: contain; width: 100%; height: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center text-secondary w-100" style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <div class="text-center w-100">
                                <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover; margin-bottom: 1rem;">
                            </div>
                        </div>
                    @endif
                    
                    <!-- Overlay Kategori -->
                    <div class="position-absolute top-0 start-0 m-2 m-md-3">
                        @if(strtolower($menu->kategori) === 'minuman')
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.7); backdrop-filter: blur(4px); border: 1px solid rgba(178, 122, 77, 0.4); color: #c08e5c;">
                                <i class="bi bi-cup-straw"></i> <span class="d-none d-md-inline">Minuman</span>
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.7); backdrop-filter: blur(4px); border: 1px solid rgba(226, 232, 240, 0.2); color: #e2e8f0;">
                                <i class="bi bi-egg-fried"></i> <span class="d-none d-md-inline">Makanan</span>
                            </span>
                        @endif
                    </div>

                    <!-- Promo Badge -->
                    @if(isset($promoMenuIds) && in_array($menu->id, $promoMenuIds))
                    <div class="position-absolute top-0 end-0 m-2 m-md-3" style="z-index: 2;">
                        <span class="badge shadow-sm px-2 py-1 rounded-pill" style="background-color: #c08e5c;"><i class="bi bi-tag-fill me-1"></i> Promo</span>
                    </div>
                    @endif
                </div>
                
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <h5 class="fw-bold mb-1 mb-md-2 text-white fs-6 fs-md-5" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-family: 'Outfit', sans-serif;" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</h5>
                    <div class="mb-3">
                        <span class="fw-bold fs-6 fs-md-5" style="color: #c08e5c;">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                    </div>
                    <p class="flex-grow-1 mb-4 small" style="color: #a0aec0; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; cursor: pointer;" onclick="this.style.webkitLineClamp = this.style.webkitLineClamp === '3' || this.style.webkitLineClamp === '' ? 'unset' : '3';" title="Klik untuk membaca selengkapnya">
                        {{ $menu->deskripsi ?? 'Hidangan istimewa racikan Master Cafe.' }}
                    </p>
                    
                    <div class="mt-auto">
                        @if($menu->stok > 0)
                            <div class="text-center py-2 rounded-3 fw-bold" style="background-color: rgba(72, 187, 120, 0.1); color: #48bb78; font-size: 0.75rem; border: 1px solid rgba(72, 187, 120, 0.2);">
                                <i class="bi bi-check-circle"></i> <span class="d-none d-md-inline">Tersedia</span> (Sisa: {{ $menu->stok }})
                            </div>
                        @else
                            <div class="text-center py-2 rounded-3 fw-bold" style="background-color: rgba(245, 101, 101, 0.1); color: #f56565; font-size: 0.75rem; border: 1px solid rgba(245, 101, 101, 0.2);">
                                <i class="bi bi-x-circle"></i> Habis
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="d-inline-block p-4 rounded-circle mb-3" style="background-color: #161b22; border: 1px solid #21262d;">
                <i class="bi bi-basket text-secondary" style="font-size: 3rem;"></i>
            </div>
            <h5 class="text-white fw-bold" style="font-family: 'Rye', serif;">Katalog Kosong</h5>
            <p class="text-secondary">Menu belum tersedia saat ini. Silakan kembali lagi nanti.</p>
        </div>
        @endforelse
    </div>
</div>

@role('konsumen')
<!-- Floating Action Button Mobile -->
<div class="position-fixed bottom-0 start-50 translate-middle-x w-100 p-3 d-md-none" style="z-index: 1050;">
    <a href="/konsumen/pilih-tipe" class="btn fw-bold w-100 rounded-pill shadow-lg py-3 fs-5 text-white btn-touch" style="background: var(--gradient-bronze); border: none;">
        <i class="bi bi-cart-plus me-2"></i> Mulai Pesan Sekarang
    </a>
</div>
@endrole

<style>
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.4) !important;
        border-color: rgba(178, 122, 77, 0.5) !important;
    }
    
    .active-filter {
        background-color: #c08e5c !important;
        color: #ffffff !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtns = document.querySelectorAll('.filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Update active state of buttons
                filterBtns.forEach(b => {
                    b.classList.remove('active-filter');
                    b.classList.add('text-secondary');
                    b.style.backgroundColor = 'transparent';
                });
                
                this.classList.remove('text-secondary');
                this.classList.add('active-filter');

                // Filter items
                const target = this.getAttribute('data-filter');
                
                menuItems.forEach(item => {
                    if (target === 'semua' || item.getAttribute('data-kategori') === target) {
                        item.style.display = 'block';
                        // Add a small animation effect
                        item.animate([
                            { opacity: 0, transform: 'scale(0.95)' },
                            { opacity: 1, transform: 'scale(1)' }
                        ], {
                            duration: 300,
                            easing: 'ease-out'
                        });
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endsection

