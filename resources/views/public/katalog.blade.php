@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="pt-5 pb-4 mb-5 position-relative overflow-hidden rounded-bottom-4 shadow-lg" style="background-color: #0e1217; border-bottom: 1px solid #21262d;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(178, 122, 77, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="position-absolute bottom-0 end-0 w-100 h-100" style="background: radial-gradient(circle at bottom left, rgba(178, 122, 77, 0.1) 0%, transparent 60%); pointer-events: none;"></div>
    
    <div class="container text-center position-relative z-index-1 mt-4">
        <h1 class="display-5 fw-bold mb-3 text-white" style="font-family: 'Rye', serif;">Katalog Menu</h1>
        <p class="fs-6 text-light opacity-75 mx-auto mb-4" style="max-width: 600px; font-weight: 300;">
            Jelajahi sajian lezat Master Cafe. Siap untuk memesan hidangan favorit Anda?
        </p>
        
        <div class="d-flex flex-wrap justify-content-center gap-2 gap-md-3 align-items-center">
            <a href="{{ url('/konsumen/menu-takeaway') }}" class="btn rounded-pill px-4 py-2 fw-bold text-white shadow-sm" style="background: var(--gradient-bronze); border: none; font-size: 0.9rem;">
                <i class="bi bi-bag-check me-2"></i> Pesan Bawa Pulang (Takeaway)
            </a>
        </div>
        <p class="text-white-50 small mt-3 mb-0">
            <i class="bi bi-qr-code-scan me-1 text-warning"></i> Untuk makan di tempat, silakan scan stiker QR di atas meja kafe Anda.
        </p>
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
            <div class="card h-100 shadow-lg border-0 rounded-4 overflow-hidden hover-lift" 
                 onclick="openKatalogDetail({{ $loop->index }})"
                 style="background-color: #161b22; border: 1px solid #21262d !important; cursor: pointer;"
                 title="Klik untuk melihat detail menu">
                <div class="position-relative">
                    <!-- Gambar -->
                    @if($menu->image)
                        <div class="text-center w-100 p-3" style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='/images/logo.png';" alt="{{ $menu->nama_menu }}" style="object-fit: contain; width: 100%; height: 100%; filter: drop-shadow(0 10px 15px rgba(0,0,0,0.3));">
                        </div>
                    @else
                        <div class="d-flex align-items-center justify-content-center text-secondary w-100" style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                            <div class="text-center w-100">
                                <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover; margin-bottom: 1rem;">
                            </div>
                        </div>
                    @endif
                    
                    <!-- Overlay Kategori Floating di Kiri Atas -->
                    <div class="position-absolute top-0 start-0 m-2">
                        @if(strtolower($menu->kategori) === 'minuman')
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.85); backdrop-filter: blur(4px); border: 1px solid rgba(178, 122, 77, 0.4); color: #c08e5c; font-size: 0.65rem;">
                                <i class="bi bi-cup-straw"></i> Minuman
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.85); backdrop-filter: blur(4px); border: 1px solid rgba(226, 232, 240, 0.2); color: #e2e8f0; font-size: 0.65rem;">
                                <i class="bi bi-egg-fried"></i> Makanan
                            </span>
                        @endif
                    </div>

                    <!-- Overlay Stok Floating di Kanan Atas -->
                    <div class="position-absolute top-0 end-0 m-2">
                        @if($menu->is_available)
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.85); backdrop-filter: blur(4px); border: 1px solid rgba(72, 187, 120, 0.4); color: #48bb78; font-size: 0.65rem;">
                                Tersedia
                            </span>
                        @else
                            <span class="badge rounded-pill px-2 py-1" style="background: rgba(17, 20, 24, 0.85); backdrop-filter: blur(4px); border: 1px solid rgba(245, 101, 101, 0.4); color: #f56565; font-size: 0.65rem;">
                                Habis
                            </span>
                        @endif
                    </div>

                    <!-- Promo Badge -->
                    @if(isset($promoMenuIds) && in_array($menu->id, $promoMenuIds))
                    <div class="position-absolute bottom-0 end-0 m-2" style="z-index: 2;">
                        <span class="badge shadow-sm px-2 py-1 rounded-pill" style="background-color: #c08e5c; font-size: 0.65rem;"><i class="bi bi-tag-fill me-1"></i> Promo</span>
                    </div>
                    @endif
                </div>
                
                <div class="card-body p-3 p-md-4 d-flex flex-column">
                    <h5 class="fw-bold mb-1 mb-md-2 text-white fs-6 fs-md-5" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-family: 'Outfit', sans-serif !important;" title="{{ $menu->nama_menu }}">{{ $menu->nama_menu }}</h5>
                    <div class="mb-2">
                        <span class="fw-bold fs-6 fs-md-5" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;">Rp {{ number_format($menu->harga, 0, ',', '.') }}</span>
                    </div>
                    <p class="flex-grow-1 mb-3 small" style="color: #a0aec0; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" title="Klik kartu untuk membaca selengkapnya">
                        {{ $menu->deskripsi ?? 'Hidangan istimewa racikan Master Cafe.' }}
                    </p>
                    
                    <div class="mt-auto">
                        <div class="text-center py-2 rounded-pill fw-bold" style="background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; font-size: 0.75rem; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-eye me-1"></i> Lihat Detail
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
            <h5 class="text-white fw-bold" style="font-family: 'Rye', serif;">Katalog Kosong</h5>
            <p class="text-secondary">Menu belum tersedia saat ini. Silakan kembali lagi nanti.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Modal Detail Menu (Bottom Sheet di HP, Centered Glassmorphic Modal di Desktop) -->
<div class="modal fade modal-bottom-sheet" id="katalogDetailModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-bottom-sheet" style="max-width: 420px; margin: auto;">
        <div class="modal-content border-0 shadow-lg overflow-hidden" id="katalogModalContentBox" style="background-color: #161b22; display: flex; flex-direction: column; border-radius: 20px;">
            
            <!-- Area Header & Drag Handle (Bisa ditarik ke bawah untuk menutup di HP) -->
            <div class="modal-drag-zone" id="katalogModalDragZone" style="cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none;">
                <div class="bottom-sheet-drag-handle-wrapper pt-3 pb-1 text-center">
                    <div class="bottom-sheet-drag-handle" title="Tarik ke bawah untuk menutup"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center px-4 pt-3 pt-md-4 pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-3 py-2 fw-semibold" id="katalogModalCategory" style="background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.35); color: #c08e5c; font-size: 0.75rem; letter-spacing: 0.05em; line-height: 1.2;">
                            Kategori
                        </span>
                        <div id="katalogModalStockContainer"></div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem;"></button>
                </div>
            </div>

            <div class="modal-body px-4 py-3" id="katalogModalScrollBody" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(88vh - 160px);">
                <!-- Foto Menu -->
                <div class="rounded-4 overflow-hidden mb-3 text-center position-relative" style="background: radial-gradient(circle at center, rgba(192, 142, 92, 0.08) 0%, #14171c 100%); border: 1px solid #21262d; max-height: 170px;">
                    <img id="katalogModalImg" src="" alt="Menu Image" onerror="this.onerror=null; this.src='/images/logo.png';" style="width: 100%; height: 170px; object-fit: contain; padding: 12px; filter: drop-shadow(0 6px 14px rgba(0,0,0,0.4));">
                    <div id="katalogModalPromoBadge" class="position-absolute top-0 end-0 m-2" style="display: none;">
                        <span class="badge shadow-sm px-3 py-1 rounded-pill" style="background-color: #c08e5c; font-size: 0.75rem;"><i class="bi bi-tag-fill me-1"></i> Promo Spesial</span>
                    </div>
                </div>

                <!-- Info Menu (Font Outfit Sans-Serif Modern) -->
                <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                    <h5 class="fw-bold text-white mb-0" id="katalogModalTitle" style="font-family: 'Outfit', sans-serif !important; font-size: 1.25rem;"></h5>
                    <span class="fw-bold fs-4 text-nowrap" id="katalogModalPrice" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;"></span>
                </div>

                <!-- Deskripsi Lengkap dengan Jarak Lega -->
                <div class="p-3 px-3 rounded-4 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); max-height: 130px; overflow-y: auto;">
                    <label class="text-secondary small fw-bold text-uppercase d-block mb-2" style="letter-spacing: 0.05em; font-size: 0.72rem;">Deskripsi Hidangan</label>
                    <p class="text-white-50 mb-0" id="katalogModalDesc" style="font-size: 0.875rem; line-height: 1.6;"></p>
                </div>
            </div>

            <!-- Footer Bawah Sticky -->
            <div class="modal-footer border-top px-4 py-3" style="background-color: #161b22; border-color: rgba(255,255,255,0.08) !important; flex-shrink: 0; padding-bottom: max(1.25rem, env(safe-area-inset-bottom, 16px));">
                <div class="w-100 text-center">
                    <a href="{{ url('/konsumen/menu-takeaway') }}" class="btn btn-sm rounded-pill px-4 py-2 fw-bold text-white w-100" style="background: var(--gradient-bronze);">
                        <i class="bi bi-bag-check me-1"></i> Pesan Takeaway
                    </a>
                    <p class="text-white-50 small mt-2 mb-0">
                        <i class="bi bi-qr-code-scan me-1 text-warning"></i> Makan di tempat? Scan QR di meja Anda.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>



<style>
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-6px);
        box-shadow: 0 1.25rem 2.5rem rgba(0,0,0,0.4) !important;
        border-color: rgba(178, 122, 77, 0.5) !important;
    }
    
    .active-filter {
        background-color: #c08e5c !important;
        color: #ffffff !important;
    }
</style>

<script>
    const katalogMenus = @json($menus);
    const promoIds = @json($promoMenuIds ?? []);

    function openKatalogDetail(index) {
        const menu = katalogMenus[index];
        if (!menu) return;

        document.getElementById('katalogModalTitle').innerText = menu.nama_menu;
        document.getElementById('katalogModalPrice').innerText = 'Rp ' + Number(menu.harga).toLocaleString('id-ID');
        document.getElementById('katalogModalDesc').innerText = menu.deskripsi || 'Hidangan istimewa racikan Master Cafe dengan kualitas bahan pilihan terbaik.';
        
        // Kategori
        const catEl = document.getElementById('katalogModalCategory');
        catEl.innerText = (menu.kategori ? menu.kategori.toUpperCase() : 'MENU');

        // Gambar
        const imgEl = document.getElementById('katalogModalImg');
        imgEl.onerror = function() { this.onerror = null; this.src = '/images/logo.png'; };
        imgEl.src = menu.image_url || (menu.image ? (menu.image.startsWith('http') ? menu.image : '/storage/' + menu.image) : '/images/logo.png');

        // Promo badge
        const promoEl = document.getElementById('katalogModalPromoBadge');
        if (promoIds.includes(menu.id)) {
            promoEl.style.display = 'block';
        } else {
            promoEl.style.display = 'none';
        }

        // Stok
        const stockContainer = document.getElementById('katalogModalStockContainer');
        if (menu.is_available) {
            stockContainer.innerHTML = `<span class="badge py-2 px-3 rounded-pill fw-semibold" style="background-color: rgba(72, 187, 120, 0.15); color: #48bb78; border: 1px solid rgba(72, 187, 120, 0.3); font-size: 0.75rem; line-height: 1.2;"><i class="bi bi-check-circle me-1"></i> Tersedia</span>`;
        } else {
            stockContainer.innerHTML = `<span class="badge py-2 px-3 rounded-pill fw-semibold" style="background-color: rgba(245, 101, 101, 0.15); color: #f56565; border: 1px solid rgba(245, 101, 101, 0.3); font-size: 0.75rem; line-height: 1.2;"><i class="bi bi-x-circle me-1"></i> Habis</span>`;
        }

        const modalEl = document.getElementById('katalogDetailModal');
        let modal = bootstrap.Modal.getInstance(modalEl);
        if (!modal) {
            modal = new bootstrap.Modal(modalEl);
        }
        modal.show();
    }

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

        // Swipe Down gesture to close katalogDetailModal on mobile
        const katalogModalEl = document.getElementById('katalogDetailModal');
        const katalogDragZone = document.getElementById('katalogModalDragZone');
        const katalogContentBox = document.getElementById('katalogModalContentBox');
        const katalogScrollBody = document.getElementById('katalogModalScrollBody');

        if (katalogModalEl && katalogDragZone && katalogContentBox) {
            let startY = 0, currentY = 0, isDragging = false, startTime = 0;

            function startDrag(y) {
                if (window.innerWidth >= 768) return;
                startY = y;
                currentY = y;
                startTime = Date.now();
                isDragging = true;
                katalogContentBox.style.transition = 'none';
            }

            function moveDrag(y) {
                if (!isDragging) return;
                currentY = y;
                const deltaY = currentY - startY;
                if (deltaY > 0) {
                    katalogContentBox.style.transform = `translate3d(0, ${deltaY}px, 0)`;
                } else {
                    katalogContentBox.style.transform = `translate3d(0, ${deltaY * 0.2}px, 0)`;
                }
            }

            function endDrag() {
                if (!isDragging) return;
                isDragging = false;
                const deltaY = currentY - startY;
                const duration = Date.now() - startTime;
                const velocity = deltaY / Math.max(duration, 1);

                katalogContentBox.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';

                if (deltaY > 65 || (deltaY > 25 && velocity > 0.4)) {
                    katalogContentBox.style.transform = 'translate3d(0, 100%, 0)';
                    setTimeout(function() {
                        const bsModal = bootstrap.Modal.getInstance(katalogModalEl);
                        if (bsModal) bsModal.hide();
                        katalogContentBox.style.transform = '';
                        katalogContentBox.style.transition = '';
                    }, 180);
                } else {
                    katalogContentBox.style.transform = 'translate3d(0, 0, 0)';
                    setTimeout(function() {
                        katalogContentBox.style.transform = '';
                        katalogContentBox.style.transition = '';
                    }, 250);
                }
            }

            katalogDragZone.addEventListener('touchstart', (e) => startDrag(e.touches[0].clientY), { passive: true });
            katalogDragZone.addEventListener('touchmove', (e) => moveDrag(e.touches[0].clientY), { passive: true });
            katalogDragZone.addEventListener('touchend', endDrag, { passive: true });
            katalogDragZone.addEventListener('touchcancel', endDrag, { passive: true });

            if (katalogScrollBody) {
                let bodyTouchStartY = 0;
                katalogScrollBody.addEventListener('touchstart', (e) => { bodyTouchStartY = e.touches[0].clientY; }, { passive: true });
                katalogScrollBody.addEventListener('touchmove', (e) => {
                    if (katalogScrollBody.scrollTop <= 0) {
                        const touchY = e.touches[0].clientY;
                        if (!isDragging && touchY - bodyTouchStartY > 15) startDrag(touchY);
                        if (isDragging) moveDrag(touchY);
                    }
                }, { passive: true });
                katalogScrollBody.addEventListener('touchend', () => { if (isDragging) endDrag(); }, { passive: true });
            }

            katalogDragZone.addEventListener('mousedown', (e) => {
                startDrag(e.clientY);
                function onMouseMove(ev) { moveDrag(ev.clientY); }
                function onMouseUp() {
                    endDrag();
                    window.removeEventListener('mousemove', onMouseMove);
                    window.removeEventListener('mouseup', onMouseUp);
                }
                window.addEventListener('mousemove', onMouseMove);
                window.addEventListener('mouseup', onMouseUp);
            });

            const handleEl = katalogDragZone.querySelector('.bottom-sheet-drag-handle');
            if (handleEl) {
                handleEl.addEventListener('click', () => {
                    const bsModal = bootstrap.Modal.getInstance(katalogModalEl);
                    if (bsModal) bsModal.hide();
                });
            }

            katalogModalEl.addEventListener('hidden.bs.modal', () => {
                katalogContentBox.style.transform = '';
                katalogContentBox.style.transition = '';
            });
        }
    });
</script>
@endsection

