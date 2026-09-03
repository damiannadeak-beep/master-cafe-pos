<!-- Modal QRIS Scan -->
<div class="modal fade" id="qrisScanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow text-center">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3 text-center">
                <div class="mb-3 mt-2">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle" style="width: 60px; height: 60px;">
                        <i class="bi bi-qr-code-scan fs-2"></i>
                    </div>
                </div>
                <h5 class="fw-bold mb-2">Pembayaran QRIS</h5>
                <p class="text-white-50 small mb-4">Minta pelanggan memindai kode QR di bawah ini menggunakan aplikasi e-Wallet / M-Banking mereka.</p>
                
                @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                <div class="text-white p-3 rounded-4 mx-auto mb-4 border shadow-sm position-relative d-flex align-items-center justify-content-center" style="max-width: 220px; aspect-ratio: 1/1;">
                    @if($qrisImage)
                        <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS Code" class="img-fluid rounded" style="max-height: 100%;" onerror="this.onerror=null; this.style.display='none'; document.getElementById('qris-fallback').style.display='block';">
                        <div id="qris-fallback" style="display: none; width: 100%;">
                            <i class="bi bi-image text-white-50" style="font-size: 3rem;"></i>
                            <p class="small text-danger mt-2 fw-bold mb-0">Gambar QRIS Rusak/Hilang</p>
                            <small class="text-white-50" style="font-size: 10px;">Harap upload ulang di menu Admin</small>
                        </div>
                    @else
                        <div style="width: 100%;">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                            <p class="small text-danger mt-2 fw-bold mb-0">QRIS Belum Diatur</p>
                            <small class="text-white-50" style="font-size: 10px;">Hubungi Admin</small>
                        </div>
                    @endif
                </div>

                <button type="button" class="btn btn-primary w-100 fw-bold py-3 rounded-pill shadow-sm" onclick="executePayment('qris')">
                    <i class="bi bi-check-circle-fill me-2"></i> Sudah Dibayar & Selesai
                </button>
            </div>
        </div>
    </div>
</div>