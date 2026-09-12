<!-- Modal QRIS -->
<div class="modal fade" id="qrisModal" tabindex="-1" aria-labelledby="qrisModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-touch" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <h5 class="fw-bold mb-3">Scan QRIS</h5>
                @php $qrisImage = \App\Models\Setting::getVal('qris_image'); @endphp
                @if($qrisImage)
                    <img src="{{ asset('storage/'.$qrisImage) }}" alt="QRIS" class="img-fluid rounded mb-3 border p-2">
                    <p class="small text-muted mb-4">Silakan arahkan pelanggan untuk scan Barcode di atas. Pastikan saldo sudah masuk sebelum menekan tombol Selesai.</p>
                    <button type="button" onclick="confirmQrisPayment()" class="btn btn-primary fw-bold w-100 rounded-pill btn-touch">Selesai & Cetak Struk</button>
                @else
                    <div class="p-4 rounded mb-3">
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
                <button type="button" class="btn-close btn-touch" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        <div class="d-flex align-items-center  rounded-pill border px-2 py-1">
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2 btn-touch" onclick="changeModalQty(-1)"><i class="bi bi-dash fs-5"></i></button>
                            <span id="modal-qty-display" class="fw-bold fs-5 px-2">1</span>
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2 btn-touch" onclick="changeModalQty(1)"><i class="bi bi-plus fs-5"></i></button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary fw-bold rounded-pill flex-fill btn-touch" style="white-space: nowrap;" onclick="addAnotherVariantSelection()"><i class="bi bi-plus-circle me-1"></i>Porsi Lain</button>
                        <button type="button" class="btn btn-primary fw-bold rounded-pill flex-fill btn-touch" style="white-space: nowrap;" onclick="confirmVariantSelection()">Tambahkan</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Hitung Pembayaran Tunai (Cash POS) -->
<div class="modal fade" id="cashPaymentModal" tabindex="-1" aria-labelledby="cashPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg text-white" style="background-color: #161b22; border: 1px solid #30363d !important;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center bg-success bg-opacity-25 text-success" style="width: 40px; height: 40px;">
                        <i class="bi bi-cash-stack fs-5"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-white" id="cashPaymentModalLabel">Bayar Tunai (Cash)</h5>
                        <small class="text-secondary">Kalkulator pecahan uang & kembalian</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white btn-touch" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Box Total Tagihan -->
                <div class="p-3 rounded-3 mb-3 text-center" style="background: rgba(192, 142, 92, 0.1); border: 1px solid rgba(192, 142, 92, 0.3);">
                    <small class="text-secondary d-block mb-1">TOTAL TAGIHAN</small>
                    <h3 class="fw-bold mb-0 text-accent" id="pos-cash-total-display">Rp 0</h3>
                </div>

                <!-- Pilihan Pecahan Cepat -->
                <div class="mb-3">
                    <label class="form-label small text-secondary fw-bold mb-2">
                        <i class="bi bi-wallet2 me-1"></i> Uang yang Diserahkan Tamu:
                    </label>
                    <div class="d-flex flex-wrap gap-2 mb-2" id="pos-cash-presets-container">
                        <!-- Tombol chip dinamis akan di-inject oleh JS -->
                    </div>

                    <!-- Input Custom Nominal -->
                    <div id="pos-custom-nominal-container" class="mt-2" style="display: none;">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary text-white-50">Rp</span>
                            <input type="number" id="pos-custom-nominal-input" class="form-control bg-dark border-secondary text-white form-control-lg" placeholder="Contoh: 100000" step="1000" oninput="onPosCustomNominalChange(this.value)">
                        </div>
                    </div>
                </div>

                <!-- Box Live Kembalian -->
                <div id="pos-kembalian-card" class="p-3 rounded-3 mb-3" style="background: rgba(34, 197, 94, 0.08); border: 1px dashed rgba(34, 197, 94, 0.4);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-white-50 small">Uang Diterima:</span>
                        <span id="pos-label-uang-diterima" class="text-white fw-bold fs-6">Rp 0</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-white-50 small">Uang Kembalian:</span>
                        <span id="pos-label-kembalian" class="text-success fw-bold fs-5">Rp 0 (Uang Pas)</span>
                    </div>
                    <div id="pos-kembalian-alert-msg" class="mt-2 pt-2 border-top border-secondary border-opacity-25 small text-white-50" style="font-size: 0.8rem;">
                        <i class="bi bi-check-circle text-success me-1"></i> Uang pas, tidak ada kembalian.
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary rounded-pill fw-bold px-4 btn-touch" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="button" id="btn-confirm-pos-cash" onclick="confirmCashPayment()" class="btn btn-success rounded-pill fw-bold flex-fill py-2.5 shadow btn-touch" style="font-size: 1rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Bayar & Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
