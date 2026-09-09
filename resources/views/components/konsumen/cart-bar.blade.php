
<div class="fixed-bottom shadow-lg" style="background-color: #161b22; border-top: 1px solid #21262d !important; z-index: 1030; border-radius: 24px 24px 0 0;">
    <div class="container px-3 py-3">
        <div class="mb-3">
            <select name="promo_id" id="promo_id" class="form-select form-select-sm border-primary bg-primary bg-opacity-10 fw-bold rounded-pill px-3 py-2" style="color: #c08e5c;" onchange="updateCartUI()">
                <option value="">🎟️ Tambah Promo (Opsional)</option>
                @foreach($promos as $promo)
                    <option value="{{ $promo->id }}" data-type="{{ $promo->type }}" data-value="{{ $promo->value }}" data-menus="{{ $promo->type == 'package' ? json_encode($promo->menus->map(function($m) { return ['id' => $m->id, 'jumlah' => $m->pivot->jumlah, 'harga' => $m->harga]; })) : '[]' }}">
                        {{ $promo->title }} 
                        @if($promo->type == 'discount')
                            ({{ $promo->value <= 100 ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="text-muted fw-bold d-block mb-0" style="font-size: 0.75rem;">Total Tagihan</small>
                <div class="d-flex align-items-baseline gap-2">
                    <h4 class="fw-bold text-white mb-0" id="cart-total">Rp 0</h4>
                    <span id="cart-qty" class="badge rounded-pill px-2" style="background-color: rgba(178, 122, 77, 0.2); color: #c08e5c; border: 1px solid rgba(178, 122, 77, 0.4);">0 Item</span>
                </div>
            </div>
            <button onclick="openConfirmOrderModal()" class="btn px-4 py-2 btn-touch rounded-pill shadow-sm" style="background: var(--gradient-bronze); color: white; border: none; transition: transform 0.2s;">
                Pesan <i class="bi bi-cart-check-fill ms-1"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Pesanan Tamu -->
<div class="modal fade" id="modalConfirmGuestOrder" tabindex="-1" aria-labelledby="modalConfirmGuestOrderLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.15); width: 42px; height: 42px;">
                        <i class="bi bi-bag-check-fill" style="color: #c08e5c; font-size: 1.25rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" id="modalConfirmGuestOrderLabel" style="font-family: 'Outfit', sans-serif;">Konfirmasi Pesanan</h5>
                        <small class="text-secondary">
                            @if(isset($meja))
                                Meja {{ $meja->nama_meja_atau_nomor }} &bull; Dine-In
                            @else
                                Pesanan Cafe
                            @endif
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="mb-3">
                    <label for="inputGuestName" class="form-label text-white small fw-bold">
                        Nama Pemesan / Panggilan <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="inputGuestName" class="form-control form-control-lg rounded-3 text-white" 
                           placeholder="Contoh: Budi / Sarah" 
                           style="background-color: #0e1217; border: 1px solid #30363d; font-size: 1rem;" 
                           value="{{ auth()->check() ? auth()->user()->name : '' }}" required maxlength="50">
                    <small class="text-muted" style="font-size: 0.78rem;">
                        <i class="bi bi-info-circle me-1"></i>Nama ini digunakan pelayan & kasir untuk mengantar pesanan ke meja Anda.
                    </small>
                </div>

                <div class="p-3 rounded-3 mt-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small">Total Item</span>
                        <span class="text-white fw-semibold small" id="modal-summary-qty">0 Item</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small">Total Pembayaran</span>
                        <span class="fw-bold fs-5" style="color: #c08e5c;" id="modal-summary-total">Rp 0</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Kembali</button>
                <button type="button" id="btnSubmitFinalOrder" onclick="submitCustomerOrder()" class="btn rounded-pill px-4 fw-bold shadow-sm" 
                        style="background: var(--gradient-bronze); color: white; border: none;">
                    Kirim Pesanan <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </div>
    </div>
</div>
