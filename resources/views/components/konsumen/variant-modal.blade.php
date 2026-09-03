<!-- Modal Pilih Varian -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow" style="background-color: #161b22; border: 1px solid #21262d !important;">
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
                            <h5 class="fw-bold mb-0 text-white" id="variantModalPrice">Rp 0</h5>
                        </div>
                        <div class="d-flex align-items-center  rounded-pill border px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2" onclick="changeModalQty(-1)"><i class="bi bi-dash fs-5"></i></button>
                            <span id="modal-qty-display" class="fw-bold fs-5 px-2">1</span>
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2" onclick="changeModalQty(1)"><i class="bi bi-plus fs-5"></i></button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary text-white fw-bold rounded-pill flex-fill btn-touch" style="white-space: nowrap;" onclick="addAnotherVariantSelection()"><i class="bi bi-plus-circle me-1"></i>Porsi Lain</button>
                        <button type="button" class="btn btn-auth-primary btn-touch" style="background: var(--gradient-bronze); color: white; border: none;" fw-bold rounded-pill flex-fill" style="white-space: nowrap;" onclick="confirmVariantSelection()">Tambahkan</button>
                    </div>
                </div>
            </div>
        </div>