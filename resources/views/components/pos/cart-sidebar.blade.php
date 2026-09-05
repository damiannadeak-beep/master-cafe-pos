@props(['mejas', 'promos'])

<!-- Kolom Kanan: Detail Pesanan (Keranjang) -->
<div>
    <form id="order-form">
        @csrf
        <div class="kasir-card hover-lift card bg-transparent">
            <div class="card-header border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="mb-0 fw-bold text-accent"><i class="bi bi-cart3 me-2"></i>Detail Pesanan</h5>
            </div>
            <div class="card-body p-4">
                
                <div class="row g-3 mb-4 p-3 rounded-4 order-config-box">
                    <div class="col-12">
                        <label class="small fw-bold mb-1 text-accent">Nomor Meja</label>
                        <select name="id_meja" style="background-color: #161b22; border: 1px solid #21262d !important;" class="form-select border-0 shadow-sm text-white" required>
                            @foreach($mejas as $meja)
                                <option value="{{ $meja->id }}">{{ $meja->nama_meja_atau_nomor }} {{ !$meja->is_available ? '(Terisi)' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="small fw-bold mb-1 text-accent">Tipe Pesanan</label>
                        <select name="tipe_pesanan" style="background-color: #161b22; border: 1px solid #21262d !important;" class="form-select border-0 shadow-sm text-white" onchange="toggleMeja()">
                            <option value="dine_in">Dine In (Makan di Tempat)</option>
                            <option value="takeaway">Takeaway (Bawa Pulang)</option>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <label class="small fw-bold mb-1 text-accent">Promo Diskon</label>
                        <select name="promo_id" style="background-color: #161b22; border: 1px solid #21262d !important;" class="form-select border-0 shadow-sm text-white" onchange="renderCart()">
                            <option value="">-- Tanpa Promo --</option>
                            @foreach($promos as $promo)
                                <option value="{{ $promo->id }}" data-type="{{ $promo->type }}" data-value="{{ $promo->value }}">
                                    {{ $promo->title }} 
                                    @if($promo->type == 'discount')
                                        ({{ $promo->value <= 100 ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Daftar Item Keranjang -->
                <div id="cart-list" class="cart-list mb-4 overflow-auto pe-2" style="max-height: 300px;">
                    <div class="d-flex flex-column justify-content-center align-items-center h-100 text-white-50">
                        <i class="bi bi-basket2 text-opacity-25 text-accent" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">Keranjang masih kosong</p>
                    </div>
                </div>

                <hr style="border-color: #21262d;">
                
                <!-- Ringkasan Harga -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold mb-0 text-white-50">Total Tagihan</h5>
                    <h3 class="fw-bold mb-0 price text-accent" id="grand-total">Rp 0</h3>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-grid gap-2">
                    <button type="button" onclick="submitOrder(1, 'cash')" class="btn  fw-bold rounded-pill shadow-sm text-white btn-touch" style="background: var(--gradient-bronze); border: none;"">
                        <i class="bi bi-cash"></i> BAYAR LUNAS (CASH)
                    </button>
                    <button type="button" onclick="showQrisModal()" class="btn  fw-bold rounded-pill shadow-sm text-white btn-touch" style="background: var(--gradient-bronze); border: none;"">
                        <i class="bi bi-qr-code-scan"></i> BAYAR LUNAS (QRIS)
                    </button>
                    <button type="button" onclick="submitOrder(0, 'pending')" class="btn btn-outline-warning  fw-bold rounded-pill text-white border-2 shadow-sm btn-touch">
                        <i class="bi bi-clock-history"></i> SIMPAN PESANAN
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>




