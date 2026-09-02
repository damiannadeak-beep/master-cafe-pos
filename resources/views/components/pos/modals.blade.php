<!-- Modal QRIS -->
<div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <h5 class="fw-bold mb-3">Scan QRIS</h5>
                @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                @if($qrisImage)
                    <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS" class="img-fluid rounded mb-3 border p-2">
                    <p class="small text-muted mb-4">Silakan arahkan pelanggan untuk scan Barcode di atas. Pastikan saldo sudah masuk sebelum menekan tombol Selesai.</p>
                    <button type="button" onclick="confirmQrisPayment()" class="btn btn-primary fw-bold w-100 rounded-pill">Selesai & Cetak Struk</button>
                @else
                    <div class="bg-light p-4 rounded mb-3">
                        <i class="bi bi-qr-code text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-danger small fw-bold mb-0">Admin belum mengatur gambar QRIS.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Varian -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0" id="variantModalTitle">Pilih Varian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div id="variantModalContent"></div>
                <div id="variantModalAlertContainer"></div>
                <div class="d-flex flex-column mt-4 pt-3 border-top">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted d-block mb-1">Total Harga</small>
                            <h5 class="fw-bold mb-0 text-accent" id="variantModalPrice">Rp 0</h5>
                        </div>
                        <div class="d-flex align-items-center bg-light rounded-pill border px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-dark text-decoration-none px-2" onclick="changeModalQty(-1)"><i class="bi bi-dash fs-5"></i></button>
                            <span id="modal-qty-display" class="fw-bold fs-5 px-2">1</span>
                            <button type="button" class="btn btn-sm btn-link text-dark text-decoration-none px-2" onclick="changeModalQty(1)"><i class="bi bi-plus fs-5"></i></button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary fw-bold rounded-pill flex-fill" style="white-space: nowrap;" onclick="addAnotherVariantSelection()"><i class="bi bi-plus-circle me-1"></i>Porsi Lain</button>
                        <button type="button" class="btn btn-primary fw-bold rounded-pill flex-fill" style="white-space: nowrap;" onclick="confirmVariantSelection()">Tambahkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
