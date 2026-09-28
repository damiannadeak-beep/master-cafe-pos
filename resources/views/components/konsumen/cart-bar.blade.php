<!-- Backdrop untuk Cart Drawer (Bottom Sheet) -->
<div id="cartDrawerBackdrop" 
     class="position-fixed top-0 start-0 w-100 h-100" 
     style="background: rgba(0,0,0,0.65); backdrop-filter: blur(3px); z-index: 1028; display: none; opacity: 0; transition: opacity 0.3s ease;" 
     onclick="closeCartDrawer()">
</div>

<style>
    #cartItemsList::-webkit-scrollbar {
        width: 4px;
    }
    #cartItemsList::-webkit-scrollbar-track {
        background: transparent;
    }
    #cartItemsList::-webkit-scrollbar-thumb {
        background: #30363d;
        border-radius: 4px;
    }
    #cartItemsList::-webkit-scrollbar-thumb:hover {
        background: #c08e5c;
    }
</style>

<div id="cart-bottom-bar" class="fixed-bottom shadow-lg" style="background-color: #161b22; border-top: 1px solid #21262d !important; z-index: 1030; border-radius: 22px 22px 0 0; transition: all 0.3s ease;">
    <!-- Pull Bar & Accordion Toggle Header -->
    <div class="px-3 pt-2 pb-2 cursor-pointer border-bottom border-secondary border-opacity-10" 
         onclick="toggleCartDrawer()" 
         style="cursor: pointer; user-select: none;"
         title="Klik untuk membuka / menutup rincian pesanan">
        <div class="d-flex justify-content-center mb-1">
            <div style="width: 38px; height: 4px; background: rgba(255,255,255,0.25); border-radius: 4px;"></div>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle p-1 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.18); width: 28px; height: 28px;">
                    <i class="bi bi-cart3" style="color: #c08e5c; font-size: 0.95rem;"></i>
                </div>
                <span class="fw-bold text-white small">Rincian Pesanan</span>
                <span id="cart-drawer-badge" class="badge rounded-2 px-2 py-1" style="background: rgba(192, 142, 92, 0.2); color: #c08e5c; font-size: 0.72rem; border: 1px solid rgba(192, 142, 92, 0.35);">0 Item</span>
            </div>
            <div class="d-flex align-items-center gap-1 text-white-50 small">
                <span id="cart-toggle-text" style="font-size: 0.8rem;">Lihat Rincian</span>
                <i class="bi bi-chevron-up ms-1" id="cart-chevron-icon" style="transition: transform 0.3s ease; font-size: 0.85rem;"></i>
            </div>
        </div>
    </div>

    <!-- Accordion Content / Drawer Daftar Menu Pesanan -->
    <div id="cartDrawerCollapse" style="max-height: 0; overflow: hidden; transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.25s ease; opacity: 0;">
        <div class="px-3 py-2" style="background-color: #0e1217; border-bottom: 1px solid #21262d;">
            <div class="d-flex justify-content-between align-items-center mb-2 pt-1">
                <span class="text-secondary small fw-semibold">
                    <i class="bi bi-receipt me-1 text-warning"></i> Menu yang Sedang Dipilih
                </span>
                <button type="button" onclick="clearCart()" class="btn btn-sm btn-outline-danger border-0 p-0 small" style="font-size: 0.75rem;">
                    <i class="bi bi-trash3 me-1"></i>Kosongkan Keranjang
                </button>
            </div>
            <!-- Scrollable Items List Container -->
            <div id="cartItemsList" style="max-height: 38vh; overflow-y: auto; padding-right: 2px;">
                <div class="text-center py-4 text-secondary small">
                    <i class="bi bi-basket2 fs-3 d-block mb-1 opacity-50"></i>
                    Belum ada menu yang dipilih
                </div>
            </div>
        </div>
    </div>

    <!-- Kontrol Utama Bawah: Promo & Checkout -->
    <div class="container px-3 py-2 py-md-3">
        <!-- Tombol Pemicu Modal Promo (Menggantikan Native Select Android/iOS yang Pop-up Bug di Layar) -->
        <div class="mb-2">
            <button type="button" class="btn w-100 py-2 px-3 rounded-pill d-flex align-items-center justify-content-between border border-primary border-opacity-25" 
                    id="btnPromoTrigger"
                    onclick="openPromoModal()" 
                    style="background: rgba(192, 142, 92, 0.12); color: #c08e5c; font-size: 0.88rem; font-weight: 600;">
                <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate">
                    <i class="bi bi-tag-fill fs-6" style="color: #c08e5c;"></i>
                    <span id="promoTriggerLabel" class="text-truncate">
                        @if(isset($promos) && count($promos) > 0)
                            Tambah Promo (Opsional)
                        @else
                            Promo Belum Tersedia
                        @endif
                    </span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span id="promoBadgeActive" class="badge rounded-pill bg-success text-white" style="display: none; font-size: 0.72rem;">Digunakan</span>
                    <i class="bi bi-chevron-right text-secondary small ms-1" id="promoChevron"></i>
                </div>
            </button>
            
            <!-- Hidden Select Element untuk kompatibilitas form submit & logic updateCartUI() -->
            <select name="promo_id" id="promo_id" class="d-none" onchange="updateCartUI()">
                <option value="">Tambah Promo (Opsional)</option>
                @if(isset($promos))
                    @foreach($promos as $promo)
                        <option value="{{ $promo->id }}" data-type="{{ $promo->type }}" data-value="{{ $promo->value }}" data-menus="{{ $promo->type == 'package' ? json_encode($promo->menus->map(function($m) { return ['id' => $m->id, 'jumlah' => $m->pivot->jumlah, 'harga' => $m->harga]; })) : '[]' }}">
                            {{ $promo->title }} 
                            @if($promo->type == 'discount')
                                ({{ $promo->value <= 100 ? $promo->value.'%' : 'Rp '.number_format($promo->value,0,',','.') }})
                            @endif
                        </option>
                    @endforeach
                @endif
            </select>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <div class="cursor-pointer" onclick="toggleCartDrawer()" style="cursor: pointer;" title="Klik untuk lihat rincian pesanan">
                <small class="text-muted fw-bold d-block mb-0" style="font-size: 0.75rem;">Total Tagihan</small>
                <div class="d-flex align-items-baseline gap-2">
                    <h4 class="fw-bold text-white mb-0" id="cart-total">Rp 0</h4>
                    <span id="cart-qty" class="badge rounded-2 px-2" style="background-color: rgba(178, 122, 77, 0.2); color: #c08e5c; border: 1px solid rgba(178, 122, 77, 0.4);">0 Item</span>
                </div>
            </div>
            <button onclick="openConfirmOrderModal()" class="btn px-4 py-2 btn-touch rounded-pill shadow-sm d-flex align-items-center gap-2" style="background: var(--gradient-bronze); color: white; border: none; font-weight: 600;">
                <span>Pesan</span>
                <i class="bi bi-cart-check-fill"></i>
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
                        <h5 class="modal-title text-white fw-bold mb-0" id="modalConfirmGuestOrderLabel">Konfirmasi Pesanan</h5>
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
                </div>

                @if(!isset($meja))
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="inputGuestPhone" class="form-label text-white small fw-bold mb-0">
                            No. WhatsApp / HP <span class="text-danger">* (Wajib)</span>
                        </label>
                        <span id="phoneDigitCounter" class="badge rounded-2 bg-dark border border-secondary text-secondary" style="font-size: 0.7rem; font-weight: 500;">0 digit</span>
                    </div>
                    
                    <div class="input-group">
                        <span class="input-group-text rounded-start-3 text-success fw-bold px-3" style="background-color: #161b22; border: 1px solid #30363d; border-right: none;">
                            <i class="bi bi-whatsapp"></i>
                        </span>
                        <input type="tel" id="inputGuestPhone" class="form-control form-control-lg rounded-end-3 text-white" 
                               placeholder="Contoh: 081234567890" 
                               inputmode="numeric" 
                               pattern="[0-9]*" 
                               maxlength="15" 
                               style="background-color: #0e1217; border: 1px solid #30363d; border-left: none; font-size: 1rem;" 
                               autocomplete="tel"
                               required>
                    </div>

                    <!-- Feedback Pesan Validasi Real-time -->
                    <div id="phoneValidationFeedback" class="mt-1" style="font-size: 0.78rem; display: none;"></div>

                    <small class="text-secondary d-block mt-1" style="font-size: 0.76rem; line-height: 1.35;">
                        <i class="bi bi-info-circle me-1 text-warning"></i> Digunakan kasir & dapur untuk konfirmasi via WhatsApp saat pesanan selesai dibungkus (diawali <strong>08</strong> atau <strong>628</strong>, 10-14 angka).
                    </small>
                </div>
                @endif

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

<!-- Modal Pilih Promo (Modern Bottom-Sheet / Dialog Khusus Konsumen) -->
<div class="modal fade" id="modalPromoSelector" tabindex="-1" aria-labelledby="modalPromoSelectorLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
            <div class="modal-header border-0 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.15); width: 38px; height: 38px;">
                        <i class="bi bi-tags-fill" style="color: #c08e5c; font-size: 1.1rem;"></i>
                    </div>
                    <div>
                        <h6 class="modal-title text-white fw-bold mb-0" id="modalPromoSelectorLabel">Pilih Promo Spesial</h6>
                        <small class="text-secondary" style="font-size: 0.76rem;">Gunakan voucher hemat untuk pesanan Anda</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                @if(isset($promos) && count($promos) > 0)
                    <div class="d-flex flex-column gap-2">
                        <!-- Opsi Tanpa Promo -->
                        <div class="p-3 rounded-3 promo-option-card" 
                             id="promo-card-none"
                             onclick="selectPromoOption('', 'Tambah Promo (Opsional)')"
                             style="background-color: #0e1217; border: 1.5px solid #21262d; cursor: pointer; transition: all 0.2s ease;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-x-circle text-secondary fs-5"></i>
                                    <div>
                                        <div class="text-white fw-semibold small">Tanpa Promo</div>
                                        <small class="text-secondary" style="font-size: 0.74rem;">Bayar sesuai harga normal menu</small>
                                    </div>
                                </div>
                                <span class="badge rounded-pill bg-secondary bg-opacity-25 text-secondary px-2 py-1" style="font-size: 0.72rem;">Batal Gunakan</span>
                            </div>
                        </div>

                        <!-- Daftar Promo Aktif -->
                        @foreach($promos as $promo)
                            @php
                                $valText = $promo->type == 'discount' 
                                    ? ($promo->value <= 100 ? 'Diskon '.$promo->value.'%' : 'Hemat Rp '.number_format($promo->value,0,',','.'))
                                    : 'Paket Rp '.number_format($promo->value,0,',','.');
                            @endphp
                            <div class="p-3 rounded-3 promo-option-card position-relative" 
                                 id="promo-card-{{ $promo->id }}"
                                 onclick="selectPromoOption('{{ $promo->id }}', '{{ addslashes($promo->title) }} ({{ $valText }})')"
                                 style="background: linear-gradient(135deg, #1c2128 0%, #161b22 100%); border: 1.5px solid rgba(192, 142, 92, 0.3); cursor: pointer; transition: all 0.2s ease;">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge rounded-pill px-2.5 py-1 text-white fw-bold" style="background: var(--gradient-bronze); font-size: 0.72rem;">
                                                <i class="bi bi-lightning-fill me-0.5"></i> {{ $valText }}
                                            </span>
                                            <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 0.68rem; text-transform: uppercase;">
                                                {{ $promo->type == 'package' ? 'Paket Menu' : 'Diskon' }}
                                            </span>
                                        </div>
                                        <h6 class="text-white fw-bold mb-1" style="font-size: 0.92rem;">{{ $promo->title }}</h6>
                                        <p class="text-secondary small mb-0 lh-sm" style="font-size: 0.78rem;">
                                            @if($promo->type == 'package')
                                                Kombinasi menu hemat khusus pilihan Master Cafe.
                                            @else
                                                Potongan otomatis langsung mengurangi total tagihan.
                                            @endif
                                        </p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 flex-shrink-0 fw-semibold" style="font-size: 0.76rem;">
                                        Pilih
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Tampilan Jika Belum Ada Promo Aktif -->
                    <div class="text-center py-4 px-2">
                        <div class="rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" 
                             style="width: 58px; height: 58px; background: rgba(192, 142, 92, 0.1); color: #c08e5c;">
                            <i class="bi bi-tag-fill fs-3"></i>
                        </div>
                        <h6 class="text-white fw-bold mb-1">Belum Ada Promo Aktif</h6>
                        <p class="text-secondary small mb-3" style="font-size: 0.82rem; max-width: 320px; margin: 0 auto; line-height: 1.5;">
                            Saat ini belum ada voucher atau penawaran diskon yang tersedia. Nikmati sajian nikmat Master Cafe dengan kualitas terbaik kami!
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                            Tutup
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    window.openPromoModal = function() {
        const modalEl = document.getElementById('modalPromoSelector');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    };

    window.selectPromoOption = function(promoId, promoLabel) {
        const select = document.getElementById('promo_id');
        const labelEl = document.getElementById('promoTriggerLabel');
        const badgeEl = document.getElementById('promoBadgeActive');
        const triggerBtn = document.getElementById('btnPromoTrigger');

        if (select) {
            select.value = promoId;
            if (typeof window.updateCartUI === 'function') {
                window.updateCartUI();
            } else {
                select.dispatchEvent(new Event('change'));
            }
        }

        if (labelEl) {
            if (promoId) {
                labelEl.innerText = promoLabel;
                if (badgeEl) badgeEl.style.display = 'inline-block';
                if (triggerBtn) {
                    triggerBtn.style.background = 'rgba(192, 142, 92, 0.22)';
                    triggerBtn.style.borderColor = '#c08e5c';
                }
            } else {
                labelEl.innerText = 'Tambah Promo (Opsional)';
                if (badgeEl) badgeEl.style.display = 'none';
                if (triggerBtn) {
                    triggerBtn.style.background = 'rgba(192, 142, 92, 0.12)';
                    triggerBtn.style.borderColor = 'rgba(192, 142, 92, 0.25)';
                }
            }
        }

        const modalEl = document.getElementById('modalPromoSelector');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    };
</script>
