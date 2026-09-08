<!-- Modal Pilih Varian & Detail Menu (True Bottom Sheet di Mobile, Centered Modal di Desktop) -->
<div class="modal fade modal-bottom-sheet" id="variantModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-bottom-sheet">
        <div class="modal-content border-0 shadow-lg overflow-hidden" id="variantModalContentBox" style="background-color: #161b22; display: flex; flex-direction: column;">
            
            <!-- Area Header & Drag Handle (Bisa ditarik ke bawah untuk menutup di HP) -->
            <div class="modal-drag-zone" id="variantModalDragZone" style="cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none;">
                <div class="bottom-sheet-drag-handle-wrapper pt-3 pb-1 text-center">
                    <div class="bottom-sheet-drag-handle" title="Tarik ke bawah untuk menutup"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center px-4 py-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill px-3 py-1 fw-semibold" id="variantModalCategoryBadge" style="background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3); color: #c08e5c; font-size: 0.75rem;">
                            Menu
                        </span>
                        <span id="variantModalStockBadge" class="badge rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                            Tersedia
                        </span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem;"></button>
                </div>
            </div>

            <!-- Body Tengah yang Dapat di-Scroll (Foto, Deskripsi, Varian, Catatan) -->
            <div class="modal-body px-4 py-2" id="variantModalScrollBody" style="overflow-y: auto; -webkit-overflow-scrolling: touch; max-height: calc(88vh - 175px);">
                <!-- Preview Foto Menu -->
                <div class="rounded-4 overflow-hidden mb-3 text-center position-relative" style="background-color: #14171c; border: 1px solid #21262d; max-height: 180px;">
                    <img id="variantModalImg" src="" alt="Menu Image" style="width: 100%; height: 180px; object-fit: contain; padding: 8px; filter: drop-shadow(0 6px 14px rgba(0,0,0,0.4));">
                </div>

                <!-- Info Nama & Harga -->
                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                    <h5 class="fw-bold text-white mb-0" id="variantModalMenuTitle" style="font-family: 'Outfit', sans-serif !important; font-size: 1.15rem;"></h5>
                    <span class="fw-bold fs-5 text-nowrap" id="variantModalMenuPrice" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;"></span>
                </div>

                <!-- Deskripsi Produk -->
                <div class="p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.03); border: 1px solid #21262d;">
                    <label class="text-secondary small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.05em; font-size: 0.7rem;">Deskripsi</label>
                    <p class="text-white-50 mb-0" id="variantModalMenuDesc" style="font-size: 0.85rem; line-height: 1.45;"></p>
                </div>

                <!-- Pilihan Varian / Topping (Dinamis dari JS) -->
                <div id="variantModalContent" class="mb-3"></div>
                <div id="variantModalAlertContainer"></div>

                <!-- Input Catatan Khusus Pesanan -->
                <div class="p-3 rounded-3 mb-2" style="background: rgba(255,255,255,0.03); border: 1px solid #21262d;">
                    <label class="text-secondary small fw-bold text-uppercase d-block mb-1" style="letter-spacing: 0.05em; font-size: 0.7rem;">
                        <i class="bi bi-pencil-square me-1" style="color: #c08e5c;"></i> Catatan Khusus (Opsional)
                    </label>
                    <input type="text" id="variantModalCatatan" class="form-control text-white border-0 rounded-pill px-3 py-2" 
                           style="background-color: #14171c; border: 1px solid #21262d !important; font-size: 0.85rem;" 
                           placeholder="Misal: jangan terlalu manis, kurangi es, pisah saus...">
                </div>
            </div>

            <!-- Footer Bawah Sticky (SELALU TERLIHAT DI LAYAR HP, TIDAK PERNAH TERPOTONG) -->
            <div class="modal-footer border-top px-4 py-3" style="background-color: #161b22; border-color: #21262d !important; flex-shrink: 0; padding-bottom: max(1rem, env(safe-area-inset-bottom, 16px));">
                <div class="w-100">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="text-muted d-block mb-0" style="font-size: 0.75rem;">Total Harga</small>
                            <h5 class="fw-bold mb-0 text-white" id="variantModalPrice" style="color: #c08e5c !important; font-family: 'Outfit', sans-serif !important;">Rp 0</h5>
                        </div>
                        <div class="d-flex align-items-center rounded-pill px-2 py-1" style="background-color: #14171c; border: 1px solid #21262d;">
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2" onclick="changeModalQty(-1)"><i class="bi bi-dash fs-5"></i></button>
                            <span id="modal-qty-display" class="fw-bold fs-5 px-2 text-white">1</span>
                            <button type="button" class="btn btn-sm btn-link text-white text-decoration-none px-2" onclick="changeModalQty(1)"><i class="bi bi-plus fs-5"></i></button>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary text-white fw-bold rounded-pill flex-fill btn-touch py-2" style="white-space: nowrap; border-color: #21262d; font-size: 0.85rem;" onclick="addAnotherVariantSelection()">
                            <i class="bi bi-plus-circle me-1"></i>Porsi Lain
                        </button>
                        <button type="button" class="btn btn-touch fw-bold rounded-pill flex-fill shadow-lg text-white py-2" style="background: var(--gradient-bronze); border: none; white-space: nowrap; font-size: 0.85rem;" onclick="confirmVariantSelection()">
                            <i class="bi bi-cart-plus me-1"></i>Tambahkan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    // Inisialisasi Swipe Down Bottom Sheet untuk variantModal
    (function() {
        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('variantModal');
            const dragZone = document.getElementById('variantModalDragZone');
            const contentBox = document.getElementById('variantModalContentBox');
            const scrollBody = document.getElementById('variantModalScrollBody');

            if (!modalEl || !dragZone || !contentBox) return;

            let startY = 0;
            let currentY = 0;
            let isDragging = false;
            let startTime = 0;

            function startDrag(y) {
                if (window.innerWidth >= 768) return;
                startY = y;
                currentY = y;
                startTime = Date.now();
                isDragging = true;
                contentBox.style.transition = 'none';
            }

            function moveDrag(y) {
                if (!isDragging) return;
                currentY = y;
                const deltaY = currentY - startY;
                if (deltaY > 0) {
                    contentBox.style.transform = `translate3d(0, ${deltaY}px, 0)`;
                } else {
                    // Slight upward resistance
                    contentBox.style.transform = `translate3d(0, ${deltaY * 0.2}px, 0)`;
                }
            }

            function endDrag() {
                if (!isDragging) return;
                isDragging = false;
                const deltaY = currentY - startY;
                const duration = Date.now() - startTime;
                const velocity = deltaY / Math.max(duration, 1);

                contentBox.style.transition = 'transform 0.25s cubic-bezier(0.16, 1, 0.3, 1)';

                // Tutup jika ditarik ke bawah > 65px ATAU dilempar cepat ke bawah (flick down)
                if (deltaY > 65 || (deltaY > 25 && velocity > 0.4)) {
                    contentBox.style.transform = 'translate3d(0, 100%, 0)';
                    setTimeout(function() {
                        const bsModal = bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) bsModal.hide();
                        contentBox.style.transform = '';
                        contentBox.style.transition = '';
                    }, 180);
                } else {
                    // Bouncing back ke posisi semula
                    contentBox.style.transform = 'translate3d(0, 0, 0)';
                    setTimeout(function() {
                        contentBox.style.transform = '';
                        contentBox.style.transition = '';
                    }, 250);
                }
            }

            // Drag lewat Header / Pill Handle (Touch HP)
            dragZone.addEventListener('touchstart', function(e) {
                startDrag(e.touches[0].clientY);
            }, { passive: true });

            dragZone.addEventListener('touchmove', function(e) {
                moveDrag(e.touches[0].clientY);
            }, { passive: true });

            dragZone.addEventListener('touchend', endDrag, { passive: true });
            dragZone.addEventListener('touchcancel', endDrag, { passive: true });

            // Drag lewat ScrollBody jika sedang di puncak scroll (scrollTop === 0)
            if (scrollBody) {
                let bodyTouchStartY = 0;
                scrollBody.addEventListener('touchstart', function(e) {
                    bodyTouchStartY = e.touches[0].clientY;
                }, { passive: true });

                scrollBody.addEventListener('touchmove', function(e) {
                    if (scrollBody.scrollTop <= 0) {
                        const touchY = e.touches[0].clientY;
                        if (!isDragging && touchY - bodyTouchStartY > 15) {
                            startDrag(touchY);
                        }
                        if (isDragging) {
                            moveDrag(touchY);
                        }
                    }
                }, { passive: true });

                scrollBody.addEventListener('touchend', function() {
                    if (isDragging) endDrag();
                }, { passive: true });
            }

            // Mouse Drag untuk testing DevTools
            dragZone.addEventListener('mousedown', function(e) {
                startDrag(e.clientY);
                function onMouseMove(ev) { moveDrag(ev.clientY); }
                function onMouseUp() {
                    endDrag();
                    window.removeEventListener('mousemove', onMouseMove);
                    window.removeEventListener('mouseup', onMouseUp);
                }
                window.addEventListener('mousemove', onMouseMove);
                window.addEventListener('mouseup', onMouseUp);
            });

            // Klik pada garis handle langsung menutup
            const handleEl = dragZone.querySelector('.bottom-sheet-drag-handle');
            if (handleEl) {
                handleEl.addEventListener('click', function() {
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    if (bsModal) bsModal.hide();
                });
            }

            // Bersihkan transform saat modal tersembunyi
            modalEl.addEventListener('hidden.bs.modal', function() {
                contentBox.style.transform = '';
                contentBox.style.transition = '';
            });
        });
    })();
</script>
