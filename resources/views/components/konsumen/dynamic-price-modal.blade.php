<!-- Modal Peringatan Menu Timbangan Murni (Informasi Pemesanan di Lokasi) -->
<div class="modal fade modal-bottom-sheet" id="dynamicPriceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-bottom-sheet">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="background-color: #161b22; border-top: 3px solid #c08e5c !important;">
            
            <div class="modal-header border-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3); color: #c08e5c; font-size: 0.75rem;">
                        <i class="bi bi-speedometer2 me-1"></i> Informasi Timbangan
                    </span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3 text-center">
                <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3 p-3" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); width: 64px; height: 64px;">
                    <i class="bi bi-shop text-warning fs-1"></i>
                </div>
                
                <h5 class="fw-bold text-white mb-2" id="dynamicModalMenuTitle" style="font-family: 'Outfit', sans-serif !important;">Menu Timbangan</h5>
                
                <div class="p-3 rounded-4 mb-3 text-start" style="background: rgba(255,255,255,0.03); border: 1px solid #21262d;">
                    <p class="text-white-50 mb-0" style="font-size: 0.88rem; line-height: 1.55;">
                        <i class="bi bi-info-circle-fill text-warning me-2"></i>
                        Menu ini disajikan berdasarkan <strong>hasil timbangan/ukuran langsung di lokasi Master Cafe</strong>. Pemesanan menu ini dapat dilakukan langsung di tempat, baik untuk <strong>Makan di Tempat (Dine-In)</strong> maupun <strong>Bawa Pulang (Takeaway/Bungkus)</strong>.
                    </p>
                </div>
            </div>

            <div class="modal-footer border-top px-4 py-3 flex-column gap-2" style="background-color: #161b22; border-color: #21262d !important;">
                @if(isset($meja))
                    <!-- Tombol Panggil Waitress khusus Dine In -->
                    <button type="button" class="btn btn-warning w-100 fw-bold rounded-pill py-2 text-dark shadow-sm" onclick="triggerCallWaitressFromModal()">
                        <i class="bi bi-bell-fill me-1"></i> Panggil Waitress Ke Meja
                    </button>
                @endif
                
                <button type="button" class="btn btn-primary w-100 fw-bold rounded-pill py-2 text-white shadow-sm" data-bs-dismiss="modal" style="background: var(--gradient-bronze); border: none; font-size: 0.9rem;">
                    Saya Mengerti
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function openDynamicPriceNotice(id) {
        const menu = (typeof allMenus !== 'undefined') ? allMenus.find(m => m.id === id) : null;
        const menuName = menu ? menu.nama_menu : 'Menu Timbangan';
        
        const titleEl = document.getElementById('dynamicModalMenuTitle');
        if (titleEl) titleEl.innerText = menuName;

        const modalEl = document.getElementById('dynamicPriceModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else {
            alert(`Menu ${menuName} dipesan langsung di lokasi Master Cafe (Dine-In / Takeaway) untuk penimbangan.`);
        }
    }

    function triggerCallWaitressFromModal() {
        const modalEl = document.getElementById('dynamicPriceModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) bsModal.hide();
        }
        if (typeof panggilWaitress === 'function') {
            panggilWaitress();
        } else if (typeof triggerCallBell === 'function') {
            triggerCallBell();
        } else {
            alert('Lonceng panggil waitress telah dikirim!');
        }
    }
</script>
