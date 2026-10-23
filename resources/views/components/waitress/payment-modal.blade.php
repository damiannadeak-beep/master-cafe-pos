<!-- Modal Pilih Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 shadow-lg text-white" style="background-color: #161b22; border: 1px solid #30363d !important; border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 px-4 pt-3.5 pb-2 justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-white d-flex align-items-center" id="paymentModalLabel" style="font-size: 0.95rem;">
                    <span class="badge rounded-circle p-1.5 me-2 d-inline-flex align-items-center justify-content-center" style="background: rgba(234, 179, 8, 0.15); color: #eab308; width: 28px; height: 28px;">
                        <i class="bi bi-wallet2" style="font-size: 0.9rem;"></i>
                    </span>
                    <span>Terima Pembayaran #<span id="payment-order-id-display"></span></span>
                </h6>
                <button type="button" class="btn-close btn-close-white btn-sm" data-bs-dismiss="modal" onclick="window.closeModalById('paymentModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pt-2 pb-4">
                <!-- Checklist Pilihan Pesanan Gabungan (Split Bill) -->
                <div id="payment-selective-orders-container" class="rounded-3 p-3" style="display: none; background: #1c2128; border: 1px solid #30363d; margin-bottom: 16px;">
                    <div class="d-flex justify-content-between align-items-center mb-2.5 pb-2 border-bottom border-secondary border-opacity-25">
                        <div class="d-flex align-items-center text-truncate me-2 gap-1.5">
                            <i class="bi bi-ui-checks text-warning" style="font-size: 0.88rem;"></i>
                            <span class="fw-bold text-white text-truncate" style="font-size: 0.78rem;">Pilih Tagihan yang Dibayar:</span>
                        </div>
                        <div class="d-inline-flex align-items-center p-0.5 rounded-2" id="payment-toggle-all-group" style="background: rgba(255, 255, 255, 0.04); border: 1px solid #30363d;">
                            <button type="button" 
                                    id="btn-payment-select-all" 
                                    class="btn btn-sm border-0 py-0.5 px-2 rounded-1 fw-semibold d-inline-flex align-items-center" 
                                    style="font-size: 0.68rem; color: #38bdf8; background: rgba(56, 189, 248, 0.15); transition: all 0.15s ease;" 
                                    onclick="window.toggleAllPaymentCheckboxes(true)"
                                    title="Pilih semua pesanan">
                                <i class="bi bi-check2-all me-1" style="font-size: 0.78rem;"></i> Semua
                            </button>
                            <div style="width: 1px; height: 12px; background: #30363d; margin: 0 1px;"></div>
                            <button type="button" 
                                    id="btn-payment-deselect-all" 
                                    class="btn btn-sm border-0 py-0.5 px-2 rounded-1 fw-semibold d-inline-flex align-items-center text-white-50" 
                                    style="font-size: 0.68rem; background: transparent; transition: all 0.15s ease;" 
                                    onclick="window.toggleAllPaymentCheckboxes(false)"
                                    title="Lepas semua pilihan">
                                <i class="bi bi-dash-circle me-1" style="font-size: 0.72rem;"></i> Lepas
                            </button>
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-2" id="payment-selective-orders-list">
                        <!-- Item checklist di-inject dinamis oleh JS -->
                    </div>
                    <div id="payment-selective-orders-alert" class="alert alert-danger py-1.5 px-2.5 rounded-2 mt-2 mb-0 align-items-center small" style="display: none !important; font-size: 0.74rem; gap: 6px;">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 me-1"></i>
                        <span>Pilih minimal satu pesanan untuk melanjutkan pembayaran.</span>
                    </div>
                </div>

                <!-- Tagihan Box: Sleek, compact & moderate -->
                <div class="rounded-3 text-center" style="padding: 10px 16px; margin-bottom: 16px; background: rgba(234, 179, 8, 0.07); border: 1px solid rgba(234, 179, 8, 0.22);">
                    <div class="text-white-50 fw-semibold text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.6px; margin-bottom: 2px;">TOTAL TAGIHAN</div>
                    <div class="fw-bold" style="font-size: 1.18rem; color: #f59e0b; line-height: 1.25;" id="active-order-cash-total-display">Rp 0</div>
                </div>

                <!-- Metode Pembayaran Tabs/Buttons -->
                <div class="d-flex gap-1.5" style="margin-bottom: 16px;">
                    <button type="button" 
                            id="tab-btn-cash" 
                            class="btn flex-fill fw-bold rounded-3 pay-method-tab active" 
                            style="font-size: 0.78rem; padding: 7px 8px; background: rgba(46, 160, 67, 0.18); border: 1.5px solid #2ea043; color: #34d399 !important; transition: all 0.15s ease;" 
                            onclick="window.switchActiveOrderPayMethod('cash')">
                        <i class="bi bi-cash-stack me-1" style="color: #34d399;"></i> Tunai
                    </button>
                    <button type="button" 
                            id="tab-btn-qris" 
                            class="btn flex-fill fw-medium rounded-3 pay-method-tab" 
                            style="font-size: 0.78rem; padding: 7px 8px; background: rgba(255, 255, 255, 0.03); border: 1px solid #30363d; color: #8b949e !important; transition: all 0.15s ease;" 
                            onclick="window.switchActiveOrderPayMethod('qris')">
                        <i class="bi bi-qr-code-scan me-1" style="color: #8b949e;"></i> QRIS
                    </button>
                    <button type="button" 
                            id="tab-btn-split" 
                            class="btn flex-fill fw-medium rounded-3 pay-method-tab" 
                            style="font-size: 0.78rem; padding: 7px 8px; background: rgba(255, 255, 255, 0.03); border: 1px solid #30363d; color: #8b949e !important; transition: all 0.15s ease;" 
                            onclick="window.switchActiveOrderPayMethod('split')">
                        <i class="bi bi-pie-chart me-1" style="color: #8b949e;"></i> Campuran
                    </button>
                </div>

                <!-- Seksi Tunai -->
                <div id="active-order-cash-section" style="margin-bottom: 16px;">
                    <label class="form-label small text-secondary fw-semibold mb-2 d-flex align-items-center" style="font-size: 0.78rem; gap: 8px;">
                        <i class="bi bi-cash text-secondary" style="font-size: 0.95rem;"></i>
                        <span>Uang yang Diterima Kasir:</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2" id="active-order-cash-presets" style="margin-bottom: 14px;">
                        <!-- Chip dinamis di-inject JS -->
                    </div>

                    <!-- Custom Nominal Input -->
                    <div id="active-order-custom-nominal-container" style="display: none; margin-bottom: 14px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-dark border-secondary text-white-50" style="font-size: 0.78rem;">Rp</span>
                            <input type="number" id="active-order-custom-nominal" class="form-control bg-dark border-secondary text-white" placeholder="Contoh: 100000" min="0" step="1000" style="font-size: 0.78rem;" onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" oninput="window.onActiveOrderCustomNominalChange(this.value)">
                        </div>
                    </div>

                    <!-- Box Kembalian -->
                    <div id="active-order-kembalian-card" class="rounded-3" style="padding: 11px 14px; background: rgba(46, 160, 67, 0.08); border: 1px dashed rgba(46, 160, 67, 0.4);">
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <span class="text-white-50" style="font-size: 0.76rem;">Uang Diterima:</span>
                            <span id="active-order-label-uang" class="text-white fw-bold" style="font-size: 0.82rem;">Rp 0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-white-50" style="font-size: 0.76rem;">Uang Kembalian:</span>
                            <span id="active-order-label-kembalian" class="text-success fw-bold" style="font-size: 0.88rem;">Rp 0 (Uang Pas)</span>
                        </div>
                        <div id="active-order-kembalian-alert" class="mt-2 pt-2 border-top border-secondary border-opacity-25 text-white-50 d-flex align-items-center" style="font-size: 0.72rem;">
                            <i class="bi bi-check-circle text-success me-1.5 flex-shrink-0"></i>
                            <span>Uang pas, tidak ada kembalian.</span>
                        </div>
                    </div>
                </div>

                <!-- Seksi Pembayaran Campuran (Split: Tunai + Non-Tunai) -->
                <div id="active-order-split-section" style="display: none; margin-bottom: 16px;">
                    <div class="p-3 rounded-3" style="background: rgba(56, 189, 248, 0.06); border: 1px solid rgba(56, 189, 248, 0.25);">
                        <label class="form-label small text-info fw-semibold d-flex align-items-center" style="font-size: 0.78rem; margin-bottom: 8px; gap: 8px;">
                            <i class="bi bi-cash-coin" style="font-size: 1rem;"></i>
                            <span>1. Porsi Tunai (Cash):</span>
                        </label>
                        <div class="input-group input-group-sm" style="margin-bottom: 18px;">
                            <span class="input-group-text bg-dark border-secondary text-white-50" style="font-size: 0.78rem;">Rp</span>
                            <input type="number" id="split-cash-input" class="form-control bg-dark border-secondary text-white" placeholder="Contoh: 50000" min="0" step="1000" style="font-size: 0.85rem;" oninput="window.onSplitPaymentInputChange(this.value)">
                        </div>

                        <label class="form-label small text-warning fw-semibold d-flex align-items-center" style="font-size: 0.78rem; margin-bottom: 8px; gap: 8px;">
                            <i class="bi bi-qr-code" style="font-size: 1rem;"></i>
                            <span>2. Sisa Non-Tunai (QRIS / Transfer):</span>
                        </label>
                        <div class="rounded-2 d-flex justify-content-between align-items-center" style="background: rgba(0, 0, 0, 0.4); border: 1px dashed rgba(245, 158, 11, 0.4); padding: 10px 14px; margin-bottom: 8px;">
                            <span class="text-white-50" style="font-size: 0.78rem;">Sisa Tagihan:</span>
                            <span id="split-nontunai-display" class="fw-bold text-warning" style="font-size: 0.98rem;">Rp 0</span>
                        </div>
                        <small class="text-secondary d-flex align-items-center mt-2" style="font-size: 0.72rem; gap: 6px;">
                            <i class="bi bi-info-circle flex-shrink-0"></i>
                            <span>Pelanggan bayar sebagian tunai, sisanya via QRIS atau transfer.</span>
                        </small>
                        <div class="mt-2.5 pt-2 border-top border-secondary border-opacity-25 text-center">
                            <button type="button" class="btn btn-link btn-sm text-decoration-none text-white-50 p-0" style="font-size: 0.73rem;" onclick="window.confirmSplitPaymentManual()">
                                <i class="bi bi-check2-circle me-1 text-info"></i> Atau Konfirmasi Manual (Jika Scan Akrilik Toko / Transfer)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Email Opsional -->
                <div class="text-start" style="margin-bottom: 18px;">
                    <label class="form-label text-white-50 fw-semibold mb-1.5" style="font-size: 0.74rem;">E-Receipt Email (Opsional)</label>
                    <input type="email" id="email_pelanggan" class="form-control text-white border-secondary" placeholder="email@contoh.com" style="background-color: #0d1117; font-size: 0.78rem; height: 35px; border-radius: 8px;">
                </div>

                <!-- Tombol Submit -->
                <div class="d-grid">
                    <button type="button" id="btn-submit-active-order-pay" class="btn btn-success fw-bold rounded-3 shadow btn-touch" style="font-size: 0.86rem; padding: 9px 16px;" onclick="window.submitActiveOrderPayment()">
                        <i class="bi bi-check-circle-fill me-1.5"></i> Selesaikan Pembayaran Tunai
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
