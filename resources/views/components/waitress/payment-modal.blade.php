<!-- Modal Pilih Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-white" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0 justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-white" id="paymentModalLabel">
                    <i class="bi bi-wallet2 text-warning me-2"></i>Terima Pembayaran #<span id="payment-order-id-display"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white btn-touch" data-bs-dismiss="modal" onclick="window.closeModalById('paymentModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <!-- Tagihan -->
                <div class="p-3 rounded-3 mb-3 text-center" style="background: rgba(192, 142, 92, 0.1); border: 1px solid rgba(192, 142, 92, 0.3);">
                    <small class="text-secondary d-block mb-1">TOTAL TAGIHAN</small>
                    <h3 class="fw-bold mb-0 text-accent" id="active-order-cash-total-display">Rp 0</h3>
                </div>

                <!-- Metode Pembayaran Tabs/Buttons -->
                <div class="d-flex gap-2 mb-3">
                    <button type="button" id="tab-btn-cash" class="btn btn-outline-success flex-fill fw-bold py-2 rounded-pill active" onclick="window.switchActiveOrderPayMethod('cash')">
                        <i class="bi bi-cash-stack me-1"></i> Tunai (Cash)
                    </button>
                    <button type="button" id="tab-btn-qris" class="btn btn-outline-primary flex-fill fw-bold py-2 rounded-pill" onclick="window.switchActiveOrderPayMethod('qris')">
                        <i class="bi bi-qr-code-scan me-1"></i> QRIS Dinamis
                    </button>
                </div>

                <!-- Seksi Tunai -->
                <div id="active-order-cash-section">
                    <label class="form-label small text-secondary fw-bold mb-2">
                        <i class="bi bi-cash me-1"></i> Uang yang Diterima Kasir:
                    </label>
                    <div class="d-flex flex-wrap gap-2 mb-2" id="active-order-cash-presets">
                        <!-- Chip dinamis di-inject JS -->
                    </div>

                    <!-- Custom Nominal Input -->
                    <div id="active-order-custom-nominal-container" class="mt-2 mb-3" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50">Rp</span>
                            <input type="number" id="active-order-custom-nominal" class="form-control bg-dark border-secondary text-white form-control-lg" placeholder="Contoh: 100000" step="1000" oninput="window.onActiveOrderCustomNominalChange(this.value)">
                        </div>
                    </div>

                    <!-- Box Kembalian -->
                    <div id="active-order-kembalian-card" class="p-3 rounded-3 mb-3" style="background: rgba(34, 197, 94, 0.08); border: 1px dashed rgba(34, 197, 94, 0.4);">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 small">Uang Diterima:</span>
                            <span id="active-order-label-uang" class="text-white fw-bold fs-6">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white-50 small">Uang Kembalian:</span>
                            <span id="active-order-label-kembalian" class="text-success fw-bold fs-5">Rp 0 (Uang Pas)</span>
                        </div>
                        <div id="active-order-kembalian-alert" class="mt-2 pt-2 border-top border-secondary border-opacity-25 small text-white-50" style="font-size: 0.8rem;">
                            <i class="bi bi-check-circle text-success me-1"></i> Uang pas, tanpa kembalian.
                        </div>
                    </div>
                </div>

                <!-- Email Opsional -->
                <div class="mb-3 text-start">
                    <label class="form-label small text-white-50 fw-bold">E-Receipt Email (Opsional)</label>
                    <input type="email" id="email_pelanggan" class="form-control text-white border-secondary form-control-sm" placeholder="email@contoh.com" style="background-color: #0e1217;">
                </div>

                <div class="d-grid gap-2">
                    <button type="button" id="btn-submit-active-order-pay" class="btn btn-success fw-bold py-2.5 rounded-pill shadow btn-touch" onclick="window.submitActiveOrderPayment()">
                        <i class="bi bi-check-circle-fill me-1"></i> Selesaikan Pembayaran
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
