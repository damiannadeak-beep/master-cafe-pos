
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
            <button onclick="submitCustomerOrder()" class="btn px-4 py-2 btn-touch rounded-pill shadow-sm" style="background: var(--gradient-bronze); color: white; border: none; transition: transform 0.2s;">
                Pesan <i class="bi bi-cart-check-fill ms-1"></i>
            </button>
        </div>
    </div>
</div>
