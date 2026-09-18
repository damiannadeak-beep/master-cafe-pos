@foreach($menus as $menu)
<div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-3 mb-md-4 menu-item" data-kategori="{{ strtolower($menu->kategori) }}" style="align-self: flex-start;">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column consumer-menu-card {{ $menu->is_available ? 'hover-lift' : '' }}" 
         id="consumer-menu-card-{{ $menu->id }}"
         style="background-color: #161b22; border: 1px solid #21262d !important; transition: all 0.25s ease; width: 100%; align-self: flex-start; {{ !$menu->is_available ? 'opacity: 0.65; filter: grayscale(40%);' : '' }}">
        
        <!-- Area Gambar dengan Overlay Badge -->
        <div class="position-relative cursor-pointer" 
             onclick="{{ !$menu->is_available ? 'alert(\'Mohon maaf, menu ' . addslashes($menu->nama_menu) . ' sedang habis.\')' : 'toggleConsumerMenuDesc(' . $menu->id . ')' }}"
             style="cursor: pointer;"
             title="Sentuh untuk melihat detail hidangan">
            
            @if($menu->image)
                <div class="text-center w-100 p-2 d-flex align-items-center justify-content-center" 
                     style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3; overflow: hidden;">
                    <img src="{{ $menu->image_url }}" class="card-img-top" alt="{{ $menu->nama_menu }}" 
                         style="max-height: 100%; max-width: 100%; object-fit: contain; filter: drop-shadow(0 6px 14px rgba(0,0,0,0.4));">
                </div>
            @else
                <div class="d-flex align-items-center justify-content-center text-secondary w-100" 
                     style="background-color: #14171c; border-bottom: 1px solid #21262d; aspect-ratio: 4/3;">
                    <div class="text-center w-100">
                        <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" 
                         style="height: 52px; width: 52px; object-fit: cover;">
                    </div>
                </div>
            @endif

            <!-- Badge Kategori Floating di Kiri Atas Gambar -->
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

            <!-- Badge Status Ketersediaan di Kanan Atas -->
            @if(!$menu->is_available)
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-danger rounded-pill px-2 py-1 shadow-sm" style="font-size: 0.65rem; font-weight: bold;">
                        <i class="bi bi-x-circle"></i> Habis
                    </span>
                </div>
            @endif
        </div>

        <!-- Body Kartu -->
        <div class="card-body p-2 p-md-3 d-flex flex-column" style="flex: 1;">
            <div class="cursor-pointer mb-2" 
                 onclick="{{ !$menu->is_available ? 'alert(\'Mohon maaf, menu ' . addslashes($menu->nama_menu) . ' sedang habis.\')' : 'toggleConsumerMenuDesc(' . $menu->id . ')' }}"
                 style="cursor: pointer;">
                <!-- Nama Menu -->
                <h6 class="fw-bold text-white mb-1" 
                    style="font-size: 0.95rem; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.5em;">
                    {{ $menu->nama_menu }}
                </h6>

                <!-- Deskripsi Pendek: Memanjang ke bawah saat diklik -->
                <p class="small text-secondary mb-2 consumer-desc-text" id="consumer-desc-{{ $menu->id }}"
                   style="font-size: 0.75rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2em; transition: color 0.3s ease;">
                    {{ $menu->deskripsi ?? 'Racikan istimewa Master Cafe.' }}
                </p>

                <!-- Input Catatan Khusus Pesanan (Tampil saat kartu ditekan / memanjang) -->
                <div class="consumer-note-box mb-2" id="consumer-note-box-{{ $menu->id }}" style="display: none;" onclick="event.stopPropagation();">
                    <label class="form-label text-secondary mb-1 d-flex align-items-center gap-1" style="font-size: 0.72rem; font-weight: 600;">
                        <i class="bi bi-pencil-square" style="color: #c08e5c;"></i> Catatan Khusus (Opsional)
                    </label>
                    <input type="text" 
                           id="menu-note-{{ $menu->id }}" 
                           class="form-control text-white border-0 rounded-pill px-3 py-1" 
                           style="background-color: #0e1217; border: 1px solid #30363d !important; font-size: 0.78rem;" 
                           placeholder="Misal: jangan manis, kurangi es, pisah saus..." 
                           onclick="event.stopPropagation();"
                           oninput="handleMenuNoteChange({{ $menu->id }}, this.value)">
                </div>

                <!-- Harga & Toggle Indikator -->
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold fs-6 text-nowrap" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;">
                        {{ ($menu->is_dynamic_price || $menu->harga == 0) ? 'Sesuai Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}
                    </span>
                    <small class="text-secondary opacity-75 d-flex align-items-center gap-1" style="font-size: 0.7rem;">
                        <i class="bi bi-chevron-down" id="desc-icon-{{ $menu->id }}"></i>
                        <span id="desc-text-{{ $menu->id }}">Detail</span>
                    </small>
                </div>
            </div>

            <!-- Bagian Aksi Tombol di Bawah -->
            <div class="mt-auto pt-2 border-top" style="border-color: rgba(255,255,255,0.06) !important;">
                <div class="d-flex align-items-center justify-content-between gap-1">
                    @if(!$menu->is_available)
                        <!-- Jika Menu Habis -->
                        <div class="w-100 py-1 text-center rounded-pill" style="background: rgba(220, 53, 69, 0.12); border: 1px solid rgba(220, 53, 69, 0.25);">
                            <span class="text-danger small fw-semibold" style="font-size: 0.8rem;">
                                <i class="bi bi-slash-circle me-1"></i> Sedang Habis
                            </span>
                        </div>
                    @elseif($menu->is_dynamic_price || $menu->harga == 0)
                        <!-- Jika Menu Timbangan Murni (Dipesan di Lokasi via Waitress) -->
                        <div class="w-100 py-1 text-center rounded-pill cursor-pointer" 
                             onclick="event.stopPropagation(); openDynamicPriceNotice({{ $menu->id }})"
                             style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.3); cursor: pointer;">
                            <span class="small fw-semibold" style="color: #c08e5c; font-size: 0.8rem;">
                                <i class="bi bi-info-circle me-1"></i> Pesan di Lokasi
                            </span>
                        </div>
                    @else
                        <!-- Stepper Tombol - / 0 / + jika Tersedia -->
                        <div class="d-flex align-items-center justify-content-center w-100 gap-2 p-1 rounded-pill" 
                             style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <button type="button" class="btn btn-outline-danger rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="event.stopPropagation(); removeFromCart({{ $menu->id }})"
                                    style="width: 28px; height: 28px; transition: all 0.2s; border-color: rgba(220, 53, 69, 0.4);"
                                    title="Kurangi porsi">
                                <i class="bi bi-dash fs-5"></i>
                            </button>
                            <span id="qty-{{ $menu->id }}" class="fw-bold mb-0 text-white" 
                                  style="font-size: 0.95rem; min-width: 20px; text-align: center; font-family: 'Outfit', sans-serif;">
                                0
                            </span>
                            <button type="button" class="btn btn-primary rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="event.stopPropagation(); handleAddToCart({{ $menu->id }})"
                                    style="width: 28px; height: 28px; transition: all 0.2s; background: var(--gradient-bronze); border: none;"
                                    title="Tambah porsi">
                                <i class="bi bi-plus fs-5"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Kontainer Catatan / Varian Aktif -->
                <div id="catatan-container-{{ $menu->id }}" class="mt-2 small" style="color: #c08e5c; display: none;">
                    <!-- variants shown here by JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach

<style>
    /* Menu Card Expand (Hanya kartu yang ditekan yang memanjang ke bawah) */
    .consumer-menu-card {
        will-change: height, transform;
    }
    .consumer-menu-card.is-expanded {
        border-color: rgba(192, 142, 92, 0.7) !important;
        background: linear-gradient(180deg, #161b22 0%, #1c222b 100%) !important;
        box-shadow: 0 12px 26px rgba(0, 0, 0, 0.5) !important;
    }
    .consumer-menu-card.is-expanded .consumer-desc-text {
        display: block !important;
        -webkit-line-clamp: unset !important;
        overflow: visible !important;
        color: #e2e8f0 !important;
        min-height: 0 !important;
        line-height: 1.5 !important;
    }
    .consumer-menu-card.is-expanded .consumer-note-box {
        display: block !important;
    }
</style>

<script>
    if (typeof window.toggleConsumerMenuDesc === 'undefined') {
        window.toggleConsumerMenuDesc = function(id) {
            const card = document.getElementById('consumer-menu-card-' + id);
            const icon = document.getElementById('desc-icon-' + id);
            const text = document.getElementById('desc-text-' + id);
            if (!card) return;

            const isExpanded = card.classList.toggle('is-expanded');
            if (isExpanded) {
                if (icon) icon.className = 'bi bi-chevron-up';
                if (text) text.innerText = 'Tutup';
            } else {
                if (icon) icon.className = 'bi bi-chevron-down';
                if (text) text.innerText = 'Detail';
            }
        };
    }

    if (typeof window.handleMenuNoteChange === 'undefined') {
        window.handleMenuNoteChange = function(id, val) {
            if (typeof window.updateCatatan === 'function') {
                window.updateCatatan(id, val);
                if (typeof window.updateCartUI === 'function') {
                    window.updateCartUI();
                }
            }
        };
    }

    if (typeof window.handleAddToCart === 'undefined') {
        window.handleAddToCart = function(id) {
            const config = window.ConsumerMenuConfig || {};
            const allMenus = config.allMenus || [];
            const menu = allMenus.find(m => m.id === id);
            if (!menu) return;

            if (!menu.is_available) {
                alert('Mohon maaf, menu ' + menu.nama_menu + ' sedang habis.');
                return;
            }

            if (menu.is_dynamic_price || Number(menu.harga) === 0) {
                if (typeof window.openDynamicPriceNotice === 'function') {
                    window.openDynamicPriceNotice(id);
                }
                return;
            }

            const noteInput = document.getElementById('menu-note-' + id);
            const note = noteInput ? noteInput.value.trim() : '';

            // Jika menu punya varian/topping khusus yang wajib dipilih, buka modal varian
            if (menu.variants && menu.variants.length > 0) {
                if (typeof window.openVariantModal === 'function') {
                    window.openVariantModal(id);
                    setTimeout(() => {
                        const modalCatatanEl = document.getElementById('variantModalCatatan');
                        if (modalCatatanEl && note) {
                            modalCatatanEl.value = note;
                        }
                    }, 50);
                }
            } else {
                // Jika menu standar tanpa varian khusus (seperti Lemon Tea), langsung tambah ke keranjang dengan catatan!
                if (typeof window.addToCart === 'function') {
                    window.addToCart(menu.id, menu.nama_menu, Number(menu.harga), [], 1, note);
                }
            }
        };
    }
</script>
