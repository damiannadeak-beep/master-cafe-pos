@foreach($menus as $menu)
<div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-3 mb-md-4 menu-item" data-kategori="{{ strtolower($menu->kategori) }}">
    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden d-flex flex-column {{ $menu->is_available ? 'hover-lift' : '' }}" 
         style="background-color: #161b22; border: 1px solid #21262d !important; transition: all 0.25s ease; {{ !$menu->is_available ? 'opacity: 0.65; filter: grayscale(40%);' : '' }}">
        
        <!-- Area Gambar dengan Overlay Badge -->
        <div class="position-relative cursor-pointer" 
             onclick="{{ !$menu->is_available ? 'alert(\'Mohon maaf, menu ' . addslashes($menu->nama_menu) . ' sedang habis.\')' : ($menu->is_dynamic_price ? 'alert(\'Menu ini harus dipesan langsung melalui Waitress karena harga menyesuaikan timbangan/ukuran.\')' : 'openVariantModal(' . $menu->id . ')') }}"
             style="cursor: pointer;"
             title="Sentuh untuk melihat detail & varian">
            
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
                 onclick="{{ !$menu->is_available ? 'alert(\'Mohon maaf, menu ' . addslashes($menu->nama_menu) . ' sedang habis.\')' : ($menu->is_dynamic_price ? 'alert(\'Menu ini harus dipesan langsung melalui Waitress karena harga menyesuaikan timbangan/ukuran.\')' : 'openVariantModal(' . $menu->id . ')') }}"
                 style="cursor: pointer;">
                <!-- Nama Menu -->
                <h6 class="fw-bold text-white mb-1" 
                    style="font-size: 0.95rem; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2.5em;">
                    {{ $menu->nama_menu }}
                </h6>

                <!-- Deskripsi Pendek -->
                <p class="small text-secondary mb-2" 
                   style="font-size: 0.75rem; line-height: 1.35; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 2em;">
                    {{ $menu->deskripsi ?? 'Racikan istimewa Master Cafe.' }}
                </p>

                <!-- Harga -->
                <div class="d-flex align-items-center justify-content-between">
                    <span class="fw-bold fs-6 text-nowrap" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;">
                        {{ $menu->is_dynamic_price ? 'Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}
                    </span>
                    <small class="text-secondary opacity-75" style="font-size: 0.7rem;">
                        <i class="bi bi-info-circle"></i>
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
                    @else
                        <!-- Stepper Tombol - / 0 / + jika Tersedia -->
                        <div class="d-flex align-items-center justify-content-center w-100 gap-2 p-1 rounded-pill" 
                             style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                            <button class="btn btn-outline-danger rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="removeFromCart({{ $menu->id }})"
                                    style="width: 28px; height: 28px; transition: all 0.2s; border-color: rgba(220, 53, 69, 0.4);"
                                    title="Kurangi porsi">
                                <i class="bi bi-dash fs-5"></i>
                            </button>
                            <span id="qty-{{ $menu->id }}" class="fw-bold mb-0 text-white" 
                                  style="font-size: 0.95rem; min-width: 20px; text-align: center; font-family: 'Outfit', sans-serif;">
                                0
                            </span>
                            <button class="btn btn-primary rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                                    onclick="{{ $menu->is_dynamic_price ? 'alert(\'Menu ini harus dipesan langsung melalui Waitress karena harga menyesuaikan timbangan/ukuran.\')' : 'openVariantModal(' . $menu->id . ')' }}"
                                    style="width: 28px; height: 28px; transition: all 0.2s; background: var(--gradient-bronze); border: none;"
                                    title="Tambah porsi / varian">
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
