<!-- Modal Konfirmasi VOID Pesanan -->
<div class="modal fade" id="voidOrderModal" tabindex="-1" aria-labelledby="voidOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-lg" style="background-color: #161b22; border: 1px solid rgba(220, 53, 69, 0.4) !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-trash3-fill fs-5"></i>
                    </div>
                    <h6 class="modal-title fw-bold text-white mb-0" id="voidOrderModalLabel">Void Pesanan #<span id="void-order-id-display"></span></h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.closeModalById('voidOrderModal')" aria-label="Close"></button>
            </div>
            
            <form id="voidOrderForm" onsubmit="window.submitVoidOrder(event)">
                <div class="modal-body p-4 pt-3">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 text-danger small py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                        <span>Pesanan akan dibatalkan permanen.</span>
                    </div>

                    <div id="void-error-alert" class="alert alert-warning small py-2 px-3 rounded-3 mb-3 d-none"></div>

                    <div class="mb-3">
                        <label class="form-label text-white-50 small fw-bold mb-1">Alasan Pembatalan (Wajib)</label>
                        <select id="void-alasan-select" class="form-select form-select-sm text-white mb-2" style="background-color: #0e1217; border-color: #21262d;" onchange="window.handleVoidReasonChange(this)">
                            <option value="Pelanggan Membatalkan Pesanan">Pelanggan Membatalkan Pesanan</option>
                            <option value="Salah Input Menu / Meja">Salah Input Menu / Meja</option>
                            <option value="Pesanan Uji Coba (Testing Waitress)">Pesanan Uji Coba (Testing Waitress)</option>
                            <option value="Menu Habis / Batal Masak">Menu Habis / Batal Masak</option>
                            <option value="custom">-- Alasan Lainnya (Ketik Manual) --</option>
                        </select>
                        <textarea id="void-alasan-custom" class="form-control text-white form-control-sm d-none" rows="2" placeholder="Tuliskan alasan pembatalan..." style="background-color: #0e1217; border-color: #21262d;"></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label text-white-50 small fw-bold mb-1">Password Akun Waitress (Otorisasi)</label>
                        <div class="position-relative">
                            <input type="password" 
                                   id="void-password-input" 
                                   class="form-control text-white form-control-sm" 
                                   placeholder="Masukkan password Anda" 
                                   required 
                                   autocomplete="current-password" 
                                   style="background-color: #0e1217; border: 1px solid #30363d; border-radius: 8px; padding-right: 2.75rem !important; height: 38px;">
                            <button type="button" 
                                    class="btn p-0 position-absolute top-50 end-0 translate-middle-y me-3 text-secondary d-flex align-items-center justify-content-center void-toggle-eye" 
                                    style="border: none; background: transparent; z-index: 5; cursor: pointer; color: #8b949e !important;"
                                    onclick="window.toggleVoidPasswordVisibility(this)"
                                    tabindex="-1"
                                    title="Tampilkan / Sembunyikan Password">
                                <i class="bi bi-eye-slash fs-6"></i>
                            </button>
                        </div>
                        <small class="text-white-50" style="font-size: 11px;">Wajib diisi sebagai verifikasi otorisasi kasir.</small>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 px-4 pb-4 d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1 rounded-pill fw-bold" data-bs-dismiss="modal" onclick="window.closeModalById('voidOrderModal')">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-void" class="btn btn-sm btn-danger flex-grow-1 rounded-pill fw-bold d-flex align-items-center justify-content-center gap-1">
                        <i class="bi bi-trash3 me-1"></i> Konfirmasi VOID
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Hilangkan tombol reveal bawaan Edge / Chromium agar tidak dobel mata */
    #void-password-input::-ms-reveal,
    #void-password-input::-ms-clear {
        display: none !important;
    }
    #void-password-input:focus {
        border-color: #c08e5c !important;
        box-shadow: 0 0 0 2px rgba(192, 142, 92, 0.25) !important;
        outline: none;
    }
    .void-toggle-eye:hover {
        color: #ffffff !important;
    }
</style>
