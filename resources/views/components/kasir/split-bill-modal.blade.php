<!-- Modal Pisah Bon -->
<div class="modal fade" id="splitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Pisah Tagihan (Pesanan #<span id="split-order-id"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <p class="small text-white-50 mb-3">Pilih item mana dan jumlahnya yang ingin dipisah menjadi bon baru.</p>
                <div id="split-items-container" class="mb-3">
                    <!-- Checkboxes will be rendered here -->
                </div>
                <button type="button" class="btn btn-warning w-100 fw-bold" onclick="executeSplit()">Proses Pisah Bon</button>
            </div>
        </div>
    </div>
</div>