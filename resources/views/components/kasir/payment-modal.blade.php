<!-- Modal Pilih Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle" style="width: 54px; height: 54px;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-1 text-white" id="paymentModalLabel">Metode Pembayaran</h5>
                <p class="text-white-50 small mb-3">Pilih cara pembayaran pelanggan</p>

                <div class="mb-4 text-start">
                    <label class="form-label small text-white-50 fw-bold">E-Receipt Email (Opsional)</label>
                    <input type="email" id="email_pelanggan" class="form-control text-white border-secondary form-control-sm" placeholder="email@contoh.com" style="background-color: #0e1217;">
                    <small class="text-white-50" style="font-size: 11px;">Kosongkan jika tidak butuh struk email.</small>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success fw-bold py-2 rounded-pill d-flex align-items-center justify-content-center gap-2" onclick="processPayment('cash')">
                        <i class="bi bi-cash-stack fs-5"></i> Uang Tunai (Cash)
                    </button>
                    <button type="button" class="btn btn-primary fw-bold py-2 rounded-pill d-flex align-items-center justify-content-center gap-2" onclick="processPayment('qris')">
                        <i class="bi bi-qr-code-scan fs-5"></i> QRIS Dinamis
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
