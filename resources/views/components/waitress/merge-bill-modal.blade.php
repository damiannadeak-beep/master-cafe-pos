<!-- Modal Gabung Bon (Merge Bill) -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 16px;">
            <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(168, 85, 247, 0.15); width: 34px; height: 34px; color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3);">
                        <i class="bi bi-intersect"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">Gabung Tagihan / Meja (Merge Bill)</h6>
                        <small class="text-secondary" style="font-size: 0.75rem;">Satukan dua pesanan yang belum dibayar</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="window.closeModalById('mergeModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 pt-3">
                <!-- Info Pesanan Penerima / Induk -->
                <div class="p-3 rounded-3 mb-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                    <div class="small text-white-50 mb-1" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px;">Pesanan Utama (Penerima):</div>
                    <div class="fw-bold text-white fs-6" id="merge-target-label">
                        <span class="placeholder-glow"><span class="placeholder col-6 bg-secondary"></span></span>
                    </div>
                </div>

                <!-- Pemilihan Pesanan yang Mau Dilebur -->
                <div class="mb-3">
                    <label class="form-label text-light small fw-semibold d-flex justify-content-between align-items-center">
                        <span>Pilih Pesanan yang Mau Dilebur Masuk:</span>
                        <span id="merge-candidates-count" class="badge rounded-pill bg-secondary text-white-50" style="font-size: 0.65rem;">Memuat...</span>
                    </label>
                    <div id="merge-loading-spinner" class="text-center py-3">
                        <div class="spinner-border spinner-border-sm text-purple" role="status" style="color: #c084fc;">
                            <span class="visually-hidden">Memuat...</span>
                        </div>
                        <span class="small text-white-50 ms-2">Mencari pesanan aktif lainnya...</span>
                    </div>
                    <select id="merge-source-select" class="form-select bg-dark text-white border-secondary rounded-3 py-2.5 small" style="display: none; border-color: #30363d !important;">
                        <option value="">-- Pilih Pesanan yang Ingin Digabungkan --</option>
                    </select>
                    <div id="merge-empty-alert" class="alert alert-warning py-2 px-3 small rounded-3 mt-2 mb-0" style="display: none; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); color: #fbbf24;">
                        <i class="bi bi-info-circle me-1"></i> Tidak ada pesanan aktif lain yang belum dibayar saat ini.
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-secondary py-2 px-3 small rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); font-size: 0.75rem; color: #8b949e;">
                    <i class="bi bi-shield-check text-info me-1"></i> Seluruh menu dari pesanan yang dilebur akan dipindahkan ke pesanan utama. Total tagihan akan dihitung ulang secara otomatis.
                </div>

                <!-- Tombol Eksekusi -->
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary flex-fill fw-medium rounded-pill py-2 text-white border-0" style="background-color: #21262d;" onclick="window.closeModalById('mergeModal')">Batal</button>
                    <button type="button" id="btn-execute-merge" class="btn flex-fill fw-bold rounded-pill py-2 text-white border-0 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="background-color: #9333ea;" onclick="window.executeMerge()">
                        <i class="bi bi-intersect"></i>
                        <span>Gabungkan Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
