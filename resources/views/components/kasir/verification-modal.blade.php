<div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-labelledby="verifyPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #161b22; border: 1px solid #21262d;">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white fw-bold" id="verifyPaymentModalLabel"><i class="bi bi-shield-check text-warning me-2"></i>Verifikasi Bukti Transfer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="text-white-50 mb-3">Pesanan #<span id="verify-order-id" class="text-white fw-bold"></span></p>
                <div class="mb-4" style="background: #0e1217; padding: 10px; border-radius: 10px; border: 1px dashed #21262d;">
                    <img id="verify-payment-image" src="" alt="Bukti Pembayaran" class="img-fluid rounded shadow-sm" style="max-height: 400px; width: auto; cursor: zoom-in;" onclick="window.open(this.src, '_blank')">
                    <p class="text-secondary small mt-2 mb-0"><i class="bi bi-zoom-in"></i> Klik gambar untuk memperbesar</p>
                </div>
                
                <form id="verifyPaymentForm" method="POST" action="">
                    @csrf
                    @method('PUT')
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <button type="button" class="btn btn-outline-danger px-4 fw-bold" onclick="rejectPayment()">
                            <i class="bi bi-x-circle me-1"></i> Tolak (Palsu)
                        </button>
                        <button type="submit" class="btn btn-success px-4 fw-bold" style="background: #2ea043; border: none;">
                            <i class="bi bi-check-circle me-1"></i> Valid & Terima Pembayaran
                        </button>
                    </div>
                </form>
                
                <!-- Hidden form for rejection -->
                <form id="rejectPaymentForm" method="POST" action="" class="d-none">
                    @csrf
                    @method('PUT')
                </form>
            </div>
        </div>
    </div>
</div>