<!-- Modal Konfirmasi Pesanan Selesai & Cetak Struk -->
<div class="modal fade" id="orderCompletedModal" tabindex="-1" aria-labelledby="orderCompletedModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content text-white shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 42px; height: 42px; background: rgba(46, 160, 67, 0.15); color: #2ea043; border: 1px solid rgba(46, 160, 67, 0.3);">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="orderCompletedModalLabel">Pesanan Selesai!</h5>
                        <small class="text-white-50" id="orderCompletedSubtitle">Pesanan #...</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <div class="p-3 rounded-3 mb-3 text-start" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-white-50 small">Pemesan:</span>
                        <strong class="text-white small" id="orderCompletedCustomer">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-white-50 small">Layanan:</span>
                        <span class="badge rounded-pill text-bg-secondary" id="orderCompletedType">-</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white-50 small">Status Pembayaran:</span>
                        <span id="orderCompletedPayBadge" class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Lunas</span>
                    </div>
                </div>

                <p class="text-white small mb-4" id="orderCompletedPromptText">
                    Pesanan telah selesai disiapkan! Apakah ingin mencetak struk transaksi sekarang?
                </p>

                <div class="d-grid gap-2" id="orderCompletedActions">
                    @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                    @if($printerActive)
                    <button type="button" class="btn btn-info text-white fw-bold py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2" id="btnOrderCompletedThermal">
                        <i class="bi bi-printer-fill fs-5"></i> Cetak Struk Thermal
                    </button>
                    @endif
                    <a href="#" target="_blank" class="btn btn-outline-primary fw-bold py-2 rounded-3 d-flex align-items-center justify-content-center gap-2" id="btnOrderCompletedBrowser">
                        <i class="bi bi-file-earmark-text-fill fs-5"></i> Cetak Struk Kasir (Browser)
                    </a>
                    <button type="button" class="btn btn-secondary fw-semibold py-2 rounded-3 mt-1" data-bs-dismiss="modal">
                        Selesai Tanpa Struk
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
