
@foreach($menus as $menu)
<div class="col-6 col-sm-6 col-md-4 col-lg-3 mb-4 menu-item" data-kategori="{{ strtolower($menu->kategori) }}">
    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important; display: flex; flex-direction: column;">
        @if($menu->image)
            <img src="{{ $menu->image_url }}" class="card-img-top" alt="{{ $menu->nama_menu }}" style="height: 180px; object-fit: contain; width: 100%;">
        @else
            <div class="d-flex align-items-center justify-content-center" style="height: 180px; background-color: #14171c;">
                <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover;">
            </div>
        @endif
        <div class="card-body p-3 d-flex flex-column" style="flex: 1;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif;">{{ $menu->nama_menu }}</h5>
                <span class="badge rounded-pill fw-normal" style="background-color: rgba(226, 232, 240, 0.1); color: #e2e8f0; font-size: 0.75rem;">{{ $menu->kategori }}</span>
            </div>
            <p class="small mb-3" style="color: #8b949e;">{{ Str::limit($menu->deskripsi, 60) }}</p>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0" style="color: #c08e5c;">{{ $menu->is_dynamic_price ? 'Harga Sesuai Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}</h6>
                <small class="text-secondary" style="font-size: 0.75rem;">Sisa: {{ $menu->stok }}</small>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-auto">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-outline-danger rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                            onclick="removeFromCart({{ $menu->id }})"
                            style="width: 32px; height: 32px; transition: all 0.2s;">
                        <i class="bi bi-dash fs-5"></i>
                    </button>
                    <span id="qty-{{ $menu->id }}" class="fw-bold fs-5 mb-0" style="min-width: 15px; text-align: center;">0</span>
                    <button class="btn btn-primary rounded-circle p-0 d-flex justify-content-center align-items-center shadow-sm" 
                            onclick="{{ $menu->is_dynamic_price ? 'alert(\'Menu ini harus dipesan langsung melalui Kasir karena harga menyesuaikan timbangan/ukuran.\')' : 'openVariantModal(' . $menu->id . ')' }}"
                            style="width: 32px; height: 32px; transition: all 0.2s;">
                        <i class="bi bi-plus fs-5"></i>
                    </button>
                </div>
            </div>
            
            <div id="catatan-container-{{ $menu->id }}" class="mt-3 small" style="color: #c08e5c; display: none;">
                <!-- variants shown here by JS -->
            </div>
        </div>
    </div>
</div>
@endforeach


