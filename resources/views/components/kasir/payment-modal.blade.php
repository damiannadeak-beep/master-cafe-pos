<!-- Modal Pilih Pembayaran -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <h5 class="fw-bold mb-3">Pilih Metode Pembayaran</h5>
                <div class="mb-4 text-start">
                    <label class="form-label small text-white-50 fw-bold">Kirim E-Receipt ke Email (Opsional)</label>
                    <input type="email" id="email_pelanggan" class="form-control text-white border-secondary  form-control-sm" placeholder="email@contoh.com">
                    <small class="text-white-50" style="font-size: 11px;">Kosongkan jika pelanggan tidak butuh struk digital.</small>
                </div>
                <button type="button" class="btn btn-success w-100 fw-bold mb-2 py-2" onclick="processPayment('cash')">
                    <i class="bi bi-cash"></i> Uang Tunai (Cash)
                </button>
                <button type="button" class="btn btn-primary w-100 fw-bold py-2" onclick="processPayment('qris')">
                    <i class="bi bi-qr-code-scan"></i> QRIS
                </button>
            </div>
        </div>
    </div>
</div>
