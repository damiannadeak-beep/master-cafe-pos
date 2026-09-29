@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="pt-2 pt-md-4 pb-3 pb-md-4 mb-3 mb-md-4 position-relative overflow-hidden rounded-bottom-4 shadow-lg" style="background-color: #0e1217; border-bottom: 1px solid #21262d;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(178, 122, 77, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="position-absolute bottom-0 end-0 w-100 h-100" style="background: radial-gradient(circle at bottom left, rgba(178, 122, 77, 0.1) 0%, transparent 60%); pointer-events: none;"></div>
    
    <div class="container text-center position-relative z-index-1 mt-0 mt-md-2">
        <h1 class="fs-2 fs-md-1 display-md-5 fw-bold mb-2 text-white">Katalog Menu</h1>
        <p class="small text-light opacity-75 mx-auto mb-3" style="max-width: 600px; font-weight: 300;">
            Jelajahi sajian lezat Master Cafe. Siap untuk memesan hidangan favorit Anda?
        </p>
        
        <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3 align-items-center">
            <a href="{{ url('/konsumen/menu-takeaway') }}" class="btn rounded-3 px-3 px-md-4 py-2 fw-bold text-white shadow-sm" style="background: var(--gradient-bronze); border: none; font-size: 0.88rem;">
                <i class="bi bi-bag-check me-2"></i> Pesan Bawa Pulang (Takeaway)
            </a>
        </div>
        <p class="text-white-50 small mt-2 mb-0" style="font-size: 0.78rem;">
            <i class="bi bi-qr-code-scan me-1 text-warning"></i> Untuk makan di tempat, silakan scan stiker QR di atas meja kafe Anda.
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    
    @if(isset($promos) && count($promos) > 0)
    <div class="alert border-0 shadow-lg rounded-4 mb-5 p-4" style="background: linear-gradient(135deg, #161b22 0%, #22262d 100%); border: 1px solid rgba(178, 122, 77, 0.3) !important;">
        <h5 class="fw-bold mb-3" style="color: #c08e5c;">
            <i class="bi bi-stars me-2"></i> Promo Spesial Hari Ini
        </h5>
        <ul class="mb-0 ps-3">
            @foreach($promos as $promo)
                <li class="mb-3 text-white">
                    <strong class="fs-5">{{ $promo->title }}</strong> 
                    @if($promo->type == 'discount')
                        <span class="text-warning small fw-semibold ms-2 align-middle">
                            (Diskon {{ $promo->discount_type == 'percentage' ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                        </span>
                    @elseif($promo->type == 'package')
                        <span class="text-warning small fw-semibold ms-2 align-middle">(Paket Khusus)</span>
                        <span class="text-white small ms-1 align-middle"><i class="bi bi-tag-fill text-warning me-1"></i>Cukup Rp {{ number_format($promo->value,0,',','.') }}</span>
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

    @php
        $hasSubKategoriCol = \Illuminate\Support\Facades\Schema::hasColumn('menu', 'sub_kategori');
        $makananSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'makanan')->pluck('sub_kategori')->filter()->unique()->values() : collect();
        $minumanSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'minuman')->pluck('sub_kategori')->filter()->unique()->values() : collect();
        $allSubs = $hasSubKategoriCol ? $menus->pluck('sub_kategori')->filter()->unique()->values() : collect();
    @endphp

    <!-- Search & Filter Controls (Simetris & Terpusat di Tengah) -->
    <!-- Search & Filter Controls (Simetris, Terpusat, & Elegan Minimalis) -->
    <div class="mb-4">
        <!-- Search Bar Terpusat -->
        <div class="mx-auto mb-3" style="max-width: 440px;">
            <div class="catalog-search-box input-group rounded-pill overflow-hidden shadow-sm" style="background-color: #14181f; border: 1px solid rgba(255, 255, 255, 0.12); transition: all 0.25s ease;">
                <span class="input-group-text bg-transparent border-0 text-secondary ps-3 pe-2">
                    <i class="bi bi-search search-icon" style="color: #8b949e; font-size: 0.9rem; transition: color 0.2s ease;"></i>
                </span>
                <input type="text" id="publicSearchInput" class="form-control bg-transparent border-0 text-white shadow-none ps-1 pe-2 py-2" placeholder="Cari hidangan atau minuman..." style="font-size: 0.875rem;" oninput="onPublicSearchInput(this.value)">
                <button type="button" id="publicSearchClearBtn" class="btn bg-transparent border-0 text-secondary pe-3" style="display: none; font-size: 0.85rem;" onclick="clearPublicSearch()">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </div>

        <!-- Filter Kategori Utama (Level 1 - Terpusat Simetris di Bawah Search Bar) -->
        <div class="d-flex justify-content-center">
            <div class="rounded-pill p-1 shadow-sm d-inline-flex gap-1" role="group" style="background-color: #14181f; border: 1px solid rgba(255, 255, 255, 0.1);">
                <button type="button" class="btn btn-sm rounded-pill px-4 py-1.5 fw-semibold filter-main-btn active-filter" onclick="filterPublicMain('semua', this)" style="font-size: 0.85rem;">
                    Semua
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-4 py-1.5 fw-semibold filter-main-btn" onclick="filterPublicMain('makanan', this)" style="font-size: 0.85rem; color: #8b949e;">
                    Makanan
                </button>
                <button type="button" class="btn btn-sm rounded-pill px-4 py-1.5 fw-semibold filter-main-btn" onclick="filterPublicMain('minuman', this)" style="font-size: 0.85rem; color: #8b949e;">
                    Minuman
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Sub-Kategori (Level 2 - Muncul dinamis jika Makanan / Minuman dipilih) -->
    <div id="public-sub-category-wrapper" class="w-100 mb-4" style="display: none !important;">
        <div id="public-sub-category-pills" class="d-flex justify-content-start justify-content-md-center gap-2 pb-2 px-2 overflow-auto subcat-touch-scroll align-items-center" style="white-space: nowrap; flex-wrap: nowrap; width: 100%; -webkit-overflow-scrolling: touch;">
            <!-- Rendered dynamically by JS -->
        </div>
    </div>

    <!-- Daftar Menu -->
    <div class="row g-3 g-md-4 align-items-start" id="menu-container">
        @forelse($menus as $menu)
        @php
            $isNewMenu = str_contains(strtolower($menu->sub_kategori ?? ''), 'baru') 
                || str_contains(strtolower($menu->sub_kategori ?? ''), 'spesial');
        @endphp
        <div class="col-6 col-md-4 col-lg-3 menu-item" 
             data-kategori="{{ strtolower($menu->kategori ?? 'makanan') }}" 
             data-subkategori="{{ strtolower($menu->sub_kategori ?? '') }}" 
             data-nama="{{ strtolower($menu->nama_menu) }}" 
             data-deskripsi="{{ strtolower($menu->deskripsi ?? '') }}"
             style="align-self: flex-start;">
            <div class="card shadow-lg border-0 rounded-4 overflow-hidden hover-lift katalog-menu-card d-flex flex-column position-relative w-100" 
                 id="menu-card-{{ $loop->index }}"
                 onclick="toggleMenuDetail({{ $loop->index }})"
                 style="background-color: #161b22; border: 1px solid #21262d !important; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); width: 100%; {{ !$menu->is_available ? 'opacity: 0.6;' : '' }}"
                 title="Sentuh untuk melihat / menutup detail deskripsi">
                
                @if($isNewMenu)
                <!-- Diagonal Corner Ribbon New (Persis seperti tangkapan layar) -->
                <div class="corner-ribbon-wrap">
                    <span class="corner-ribbon-tag">New</span>
                </div>
                @endif

                <div class="position-relative">
                    <!-- Gambar -->
                    @if($menu->image)
                        <div class="text-center w-100 p-2 p-md-3" style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='/images/logo.png';" alt="{{ $menu->nama_menu }}" style="object-fit: contain; width: 100%; height: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center w-100" style="background: radial-gradient(circle, #1c222b 0%, #12151a 100%); border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <div class="p-2 rounded-circle" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(192, 142, 92, 0.25);">
                                <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 44px; width: 44px; object-fit: cover;">
                            </div>
                        </div>
                    @endif
                    
                    <!-- Overlay Sub-Kategori Floating di Kiri Atas -->
                    @if($menu->sub_kategori)
                    <div class="position-absolute top-0 start-0 m-2">
                        <span class="badge rounded-2 px-2 py-1" style="background: rgba(17, 20, 24, 0.88); backdrop-filter: blur(6px); border: 1px solid rgba(192, 142, 92, 0.35); color: #c08e5c; font-size: 0.68rem; font-weight: 500;">
                            {{ $menu->sub_kategori }}
                        </span>
                    </div>
                    @endif

                    <!-- Overlay Habis (Hanya tampil jika menu HABIS, tidak membengkakkan tampilan jika tersedia) -->
                    @if(!$menu->is_available)
                    <div class="position-absolute top-0 end-0 m-2">
                        <span class="badge rounded-2 px-2 py-1" style="background: rgba(220, 38, 38, 0.92); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.65rem; font-weight: 600;">
                            Habis
                        </span>
                    </div>
                    @endif

                    <!-- Promo Badge -->
                    @if(isset($promoMenuIds) && in_array($menu->id, $promoMenuIds))
                    <div class="position-absolute bottom-0 end-0 m-2" style="z-index: 2;">
                        <span class="badge shadow-sm px-2 py-1 rounded-pill" style="background-color: #c08e5c; font-size: 0.65rem;"><i class="bi bi-tag-fill me-1"></i> Promo</span>
                    </div>
                    @endif
                </div>
                
                <div class="card-body p-3 p-md-4 d-flex flex-column flex-grow-1">
                    <div class="menu-title-price-wrap mb-2" style="min-height: 4.4rem;">
                        <h5 class="fw-bold mb-1 text-white fs-6 fs-md-5" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</h5>
                        <div>
                            <span class="fw-bold fs-6 fs-md-5" style="color: #c08e5c;">
                                {{ ($menu->is_dynamic_price || $menu->harga == 0) ? 'Sesuai Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Area Deskripsi: Default ringkas 2 baris, jika panjang ada see detail di kanan -->
                    <div class="menu-desc-container mt-auto">
                        <p class="menu-desc-text small mb-0" id="desc-{{ $loop->index }}" 
                           style="color: #a0aec0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-size: 0.8rem; line-height: 1.45; transition: color 0.3s ease;">
                            {{ $menu->deskripsi ?? 'Hidangan istimewa racikan Master Cafe.' }}
                        </p>

                        <!-- Toggle See Detail (Seperti pesan bawa pulang: soft text-secondary dengan chevron icon) -->
                        <div class="see-more-wrap pt-1 text-end" id="see-more-wrap-{{ $loop->index }}" style="display: none;">
                            <span class="see-detail-link text-secondary opacity-75 d-inline-flex align-items-center gap-1" id="btn-detail-{{ $loop->index }}"
                                  onclick="event.stopPropagation(); toggleMenuDetail({{ $loop->index }})"
                                  style="font-size: 0.72rem; cursor: pointer; transition: all 0.2s ease;">
                                <i class="bi bi-chevron-down" id="icon-detail-{{ $loop->index }}"></i>
                                <span id="text-detail-{{ $loop->index }}">See detail</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-center py-5">
            <div class="d-inline-block p-4 rounded-circle mb-3" style="background-color: #161b22; border: 1px solid #21262d;">
                <i class="bi bi-basket text-secondary" style="font-size: 3rem;"></i>
            </div>
            <h5 class="text-white fw-bold">Katalog Kosong</h5>
            <p class="text-secondary">Menu belum tersedia saat ini. Silakan kembali lagi nanti.</p>
        </div>
        @endforelse

        <!-- Filter Empty State jika pencarian/filter tidak ada yang cocok -->
        <div id="filter-empty-state" class="col-12 text-center py-5" style="display: none !important;">
            <div class="d-inline-block p-4 rounded-circle mb-3" style="background-color: #161b22; border: 1px solid #21262d;">
                <i class="bi bi-search text-secondary" style="font-size: 2.5rem;"></i>
            </div>
            <h5 class="text-white fw-bold">Menu Tidak Ditemukan</h5>
            <p class="text-secondary small mb-3">Tidak ditemukan menu yang sesuai dengan kata kunci pencarian atau filter yang dipilih.</p>
            <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 text-white shadow-sm" style="background-color: #c08e5c;" onclick="resetCatalogFilter()">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
            </button>
        </div>
    </div>
</div>

<style>
    .catalog-search-box:hover {
        border-color: rgba(255, 255, 255, 0.22) !important;
    }
    .catalog-search-box:focus-within {
        border-color: rgba(255, 255, 255, 0.45) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 0 3px rgba(255, 255, 255, 0.05) !important;
    }
    .catalog-search-box:focus-within .search-icon {
        color: #ffffff !important;
    }

    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 1.25rem 2.5rem rgba(0,0,0,0.4) !important;
        border-color: rgba(178, 122, 77, 0.5) !important;
    }
    
    .active-filter {
        background-color: #ffffff !important;
        color: #0e1217 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.35) !important;
    }

    /* Menu Card Expand (Hanya kartu yang ditekan yang memanjang ke bawah) */
    .katalog-menu-card {
        will-change: height, transform;
    }
    .katalog-menu-card.is-expanded {
        border-color: rgba(192, 142, 92, 0.7) !important;
        background: linear-gradient(180deg, #161b22 0%, #1c222b 100%) !important;
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.55) !important;
    }
    .katalog-menu-card.is-expanded .menu-desc-text {
        display: block !important;
        -webkit-line-clamp: unset !important;
        overflow: visible !important;
        color: #e2e8f0 !important;
        min-height: 0 !important;
        line-height: 1.6 !important;
    }
    .see-detail-link:hover {
        color: #c08e5c !important;
        opacity: 1 !important;
    }
    /* Diagonal Corner Ribbon "New" (Persis seperti tangkapan layar) */
    .corner-ribbon-wrap {
        position: absolute;
        top: 0;
        right: 0;
        width: 64px;
        height: 64px;
        overflow: hidden;
        z-index: 10;
        pointer-events: none;
    }
    .corner-ribbon-tag {
        position: absolute;
        top: 11px;
        right: -24px;
        width: 82px;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: #ffffff;
        text-align: center;
        font-size: 0.62rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        line-height: 18px;
        transform: rotate(45deg);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        text-transform: uppercase;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .see-detail-link {
        user-select: none;
    }
    .see-detail-link:hover {
        color: #c08e5c !important;
        opacity: 1 !important;
    }
    .subcat-touch-scroll {
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
    }
    .subcat-touch-scroll::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    window.publicSubCategoryData = {
        makanan: @json($makananSubs),
        minuman: @json($minumanSubs),
        semua: @json($allSubs)
    };

    let pubMainCat = 'semua';
    let pubSubCat = 'semua';
    let pubSearchQuery = '';

    window.onPublicSearchInput = function(val) {
        pubSearchQuery = (val || '').trim().toLowerCase();
        const clearBtn = document.getElementById('publicSearchClearBtn');
        if (clearBtn) {
            clearBtn.style.display = pubSearchQuery.length > 0 ? 'inline-block' : 'none';
        }
        applyPublicFilter();
    };

    window.clearPublicSearch = function() {
        const input = document.getElementById('publicSearchInput');
        if (input) {
            input.value = '';
            onPublicSearchInput('');
            input.focus();
        }
    };

    window.resetCatalogFilter = function() {
        const input = document.getElementById('publicSearchInput');
        if (input) input.value = '';
        pubSearchQuery = '';
        const clearBtn = document.getElementById('publicSearchClearBtn');
        if (clearBtn) clearBtn.style.display = 'none';

        const semuaBtn = document.querySelector('.filter-main-btn');
        filterPublicMain('semua', semuaBtn);
    };

    function renderPublicSubPills() {
        const wrapper = document.getElementById('public-sub-category-wrapper');
        const container = document.getElementById('public-sub-category-pills');
        if (!container || !wrapper) return;

        const data = window.publicSubCategoryData || {};
        let subs = [];
        if (pubMainCat === 'makanan') {
            subs = data.makanan || [];
            wrapper.style.setProperty('display', 'block', 'important');
        } else if (pubMainCat === 'minuman') {
            subs = data.minuman || [];
            wrapper.style.setProperty('display', 'block', 'important');
        } else {
            wrapper.style.setProperty('display', 'none', 'important');
            container.innerHTML = '';
            return;
        }

        let html = `
            <button type="button" class="btn btn-sm filter-sub-btn active-sub rounded-pill px-3 py-1 flex-shrink-0" onclick="filterPublicSub('semua', this)" style="background-color: #ffffff; color: #0e1217; border: 1px solid #ffffff; font-size: 0.8rem; font-weight: 600;">
                Semua ${pubMainCat === 'makanan' ? 'Makanan' : 'Minuman'}
            </button>
        `;

        subs.forEach(sub => {
            const safeSub = sub.replace(/'/g, "\\'");
            html += `
                <button type="button" class="btn btn-sm filter-sub-btn rounded-pill px-3 py-1 flex-shrink-0" onclick="filterPublicSub('${safeSub}', this)" style="background-color: rgba(255,255,255,0.06); color: #8b949e; border: 1px solid rgba(255,255,255,0.12); font-size: 0.8rem;">
                    ${sub}
                </button>
            `;
        });

        container.innerHTML = html;
        container.scrollLeft = 0;
    }

    window.filterPublicMain = function(mainCat, btn) {
        pubMainCat = (mainCat || 'semua').toLowerCase();
        pubSubCat = 'semua';

        document.querySelectorAll('.filter-main-btn').forEach(b => {
            b.classList.remove('active-filter');
            b.style.color = '#8b949e';
            b.style.backgroundColor = 'transparent';
        });

        if (btn) {
            btn.classList.add('active-filter');
            btn.style.color = '#0e1217';
            btn.style.backgroundColor = '#ffffff';
        }

        renderPublicSubPills();
        applyPublicFilter();
    };

    window.filterPublicSub = function(subCat, btn) {
        pubSubCat = (subCat || 'semua').toLowerCase();

        document.querySelectorAll('.filter-sub-btn').forEach(b => {
            b.classList.remove('active-sub');
            b.style.backgroundColor = 'rgba(255,255,255,0.06)';
            b.style.color = '#8b949e';
            b.style.borderColor = 'rgba(255,255,255,0.12)';
        });

        if (btn) {
            btn.classList.add('active-sub');
            btn.style.backgroundColor = '#ffffff';
            btn.style.color = '#0e1217';
            btn.style.borderColor = '#ffffff';
        }

        applyPublicFilter();
    };

    function applyPublicFilter() {
        const menuItems = document.querySelectorAll('.menu-item');
        let visibleCount = 0;

        menuItems.forEach(item => {
            const cat = (item.getAttribute('data-kategori') || '').toLowerCase();
            const sub = (item.getAttribute('data-subkategori') || '').toLowerCase();
            const nama = (item.getAttribute('data-nama') || '').toLowerCase();
            const desk = (item.getAttribute('data-deskripsi') || '').toLowerCase();

            const matchMain = (pubMainCat === 'semua' || cat === pubMainCat);
            const matchSub = (pubSubCat === 'semua' || sub === pubSubCat);
            const matchSearch = (!pubSearchQuery || nama.includes(pubSearchQuery) || desk.includes(pubSearchQuery) || sub.includes(pubSearchQuery));

            if (matchMain && matchSub && matchSearch) {
                item.style.removeProperty('display');
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.style.setProperty('display', 'none', 'important');
                item.classList.add('d-none');
            }
        });

        const emptyEl = document.getElementById('filter-empty-state');
        if (emptyEl) {
            if (visibleCount === 0 && menuItems.length > 0) {
                emptyEl.style.setProperty('display', 'block', 'important');
            } else {
                emptyEl.style.setProperty('display', 'none', 'important');
            }
        }

        checkAllSeeMoreTriggers();
    }

    function toggleMenuDetail(index) {
        const card = document.getElementById('menu-card-' + index);
        const wrap = document.getElementById('see-more-wrap-' + index);
        const text = document.getElementById('text-detail-' + index);
        const icon = document.getElementById('icon-detail-' + index);
        if (!card) return;

        // Jika teks pendek dan tidak ada overflow (see more tidak aktif), kartu tidak perlu expand
        if (wrap && wrap.style.display === 'none' && !card.classList.contains('is-expanded')) {
            return;
        }

        const isCurrentlyExpanded = card.classList.contains('is-expanded');

        // Tutup kartu lain yang sedang terbuka agar HANYA kartu yang diklik yang terbuka
        document.querySelectorAll('.katalog-menu-card.is-expanded').forEach((otherCard) => {
            if (otherCard !== card) {
                otherCard.classList.remove('is-expanded');
                const otherId = otherCard.id.replace('menu-card-', '');
                const otherText = document.getElementById('text-detail-' + otherId);
                const otherIcon = document.getElementById('icon-detail-' + otherId);
                if (otherText) otherText.innerText = 'See detail';
                if (otherIcon) otherIcon.className = 'bi bi-chevron-down';
            }
        });

        if (!isCurrentlyExpanded) {
            card.classList.add('is-expanded');
            if (text) text.innerText = 'See less';
            if (icon) icon.className = 'bi bi-chevron-up';
        } else {
            card.classList.remove('is-expanded');
            if (text) text.innerText = 'See detail';
            if (icon) icon.className = 'bi bi-chevron-down';
        }
    }

    function checkAllSeeMoreTriggers() {
        requestAnimationFrame(() => {
            document.querySelectorAll('.menu-item').forEach((item, index) => {
                const desc = document.getElementById('desc-' + index);
                const wrap = document.getElementById('see-more-wrap-' + index);
                const card = document.getElementById('menu-card-' + index);
                if (!desc || !wrap) return;

                if (card && card.classList.contains('is-expanded')) {
                    wrap.style.display = 'block';
                    return;
                }

                // Cek apakah teks deskripsi meluap (overflow) melebihi 2 baris
                if (desc.scrollHeight > (desc.clientHeight + 2)) {
                    wrap.style.display = 'block';
                } else {
                    wrap.style.display = 'none';
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderPublicSubPills();
        checkAllSeeMoreTriggers();
        setTimeout(checkAllSeeMoreTriggers, 150);
        setTimeout(checkAllSeeMoreTriggers, 600);

        window.addEventListener('resize', checkAllSeeMoreTriggers);

        const scrollContainer = document.getElementById('public-sub-category-pills');
        if (scrollContainer) {
            scrollContainer.addEventListener('wheel', function(e) {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    scrollContainer.scrollLeft += e.deltaY;
                }
            }, { passive: false });
        }
    });
</script>
@endsection

