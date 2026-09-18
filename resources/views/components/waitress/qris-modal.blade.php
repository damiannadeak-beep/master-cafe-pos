<!-- Modal QRIS Scan -->
<div class="modal fade" id="qrisScanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 shadow text-center rounded-4" style="background-color: #161b22; border: 1px solid rgba(255, 255, 255, 0.1) !important;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.closeModalById('qrisScanModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-2 text-center">
                <div class="mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 56px; height: 56px;">
                        <i class="bi bi-qr-code-scan fs-3"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-1 text-white">Pembayaran QRIS</h5>
                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2.5 py-1 small mb-3">
                    <i class="bi bi-shield-check me-1"></i> Midtrans Sandbox Mode
                </span>
                <p class="text-white-50 small mb-3">Pilih cara pemindaian QRIS untuk pesanan ini:</p>
                
                <!-- Tombol Utama: Dynamic QRIS Midtrans -->
                <button type="button" id="btn-active-order-midtrans-qris" onclick="window.openActiveOrderMidtransSnap()" class="btn btn-primary w-100 fw-bold py-2.5 rounded-pill shadow-sm mb-2 btn-touch">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Tampilkan QRIS Dinamis Midtrans
                </button>
                <small class="d-block text-white-50 mb-3" style="font-size: 0.75rem;">Pelanggan scan kode & nominal otomatis pas tanpa ketik manual.</small>

                <!-- Collapsible QRIS Statis Toko -->
                <div class="border-top border-secondary border-opacity-25 pt-3">
                    <button class="btn btn-sm btn-link text-decoration-none text-white-50 p-0 small" type="button" data-bs-toggle="collapse" data-bs-target="#activeStaticQrisCollapse" aria-expanded="false">
                        <i class="bi bi-image me-1"></i> Opsi Cadangan: QRIS Statis Toko
                    </button>
                    <div class="collapse mt-3 text-center" id="activeStaticQrisCollapse">
                        @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                        <div class="p-2 rounded-3 mx-auto mb-2 border shadow-sm position-relative d-flex align-items-center justify-content-center" style="max-width: 180px; aspect-ratio: 1/1; background: #fff;">
                            @if($qrisImage)
                                <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS Code" class="img-fluid rounded" style="max-height: 100%;" onerror="this.onerror=null; this.style.display='none'; document.getElementById('qris-fallback').style.display='block';">
                                <div id="qris-fallback" style="display: none; width: 100%;">
                                    <i class="bi bi-image text-muted fs-3"></i>
                                    <p class="small text-danger mt-1 fw-bold mb-0" style="font-size: 11px;">Gambar Rusak</p>
                                </div>
                            @else
                                <div style="width: 100%;">
                                    <i class="bi bi-exclamation-triangle text-warning fs-3"></i>
                                    <p class="small text-danger mt-1 fw-bold mb-0" style="font-size: 11px;">QRIS Statis Belum Diatur</p>
                                </div>
                            @endif
                        </div>
                        <p class="text-white-50 mb-2" style="font-size: 0.72rem;">Pastikan uang masuk di mutasi m-Banking/rekening sebelum konfirmasi.</p>
                        <button type="button" class="btn btn-outline-light btn-sm w-100 fw-bold py-2 rounded-pill shadow-sm btn-touch" onclick="window.executePayment('qris')">
                            <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Manual & Selesai
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>