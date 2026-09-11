@php
    $waRaw = \App\Models\Setting::getVal('kontak_wa') ?? \App\Models\Setting::getVal('store_phone') ?? '081234567890';
    $waClean = preg_replace('/[^0-9]/', '', $waRaw);
    if (str_starts_with($waClean, '0')) {
        $waClean = '62' . substr($waClean, 1);
    }
@endphp

<!-- Modal Peringatan Menu Timbangan Murni (Dine In & Takeaway Online) -->
<div class="modal fade modal-bottom-sheet" id="dynamicPriceModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-bottom-sheet">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="background-color: #161b22; border-top: 3px solid #c08e5c !important;">
            
            <div class="modal-header border-0 pb-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3); color: #c08e5c; font-size: 0.75rem;">
                        <i class="bi bi-speedometer2 me-1"></i> Harga Timbangan
                    </span>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body px-4 py-3 text-center">
                <div class="rounded-circle d-inline-flex justify-content-center align-items-center mb-3 p-3" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); width: 64px; height: 64px;">
                    <i class="bi bi-speedometer2 text-warning fs-1"></i>
                </div>
                
                <h5 class="fw-bold text-white mb-2" id="dynamicModalMenuTitle" style="font-family: 'Outfit', sans-serif !important;">Menu Timbangan</h5>
                
                <div class="p-3 rounded-4 mb-3 text-start" style="background: rgba(255,255,255,0.03); border: 1px solid #21262d;">
                    <p class="text-white-50 mb-0" style="font-size: 0.88rem; line-height: 1.5;">
                        <i class="bi bi-info-circle-fill text-warning me-1"></i>
                        Menu timbangan hidup ini disarankan dipesan langsung melalui <strong>Waitress</strong> (untuk makan di tempat) atau silakan konfirmasi ketersediaan berat via <strong>WhatsApp Kafe</strong> sebelum memesan.
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
                
                <!-- Tombol Hubungi WhatsApp Kafe (Dine In & Takeaway Online) -->
                <a id="btnDynamicModalWa" href="https://wa.me/{{ $waClean }}" target="_blank" class="btn btn-success w-100 fw-bold rounded-pill py-2 shadow-sm text-white">
                    <i class="bi bi-whatsapp me-1"></i> Chat WhatsApp Kafe
                </a>
                
                <button type="button" class="btn btn-outline-secondary w-100 rounded-pill py-2 text-white-50 border-0" data-bs-dismiss="modal" style="font-size: 0.85rem;">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    window.defaultWaCleanNumber = "{{ $waClean }}";

    function openDynamicPriceNotice(id) {
        const menu = (typeof allMenus !== 'undefined') ? allMenus.find(m => m.id === id) : null;
        const menuName = menu ? menu.nama_menu : 'Menu Timbangan';
        
        const titleEl = document.getElementById('dynamicModalMenuTitle');
        if (titleEl) titleEl.innerText = menuName;

        const waBtn = document.getElementById('btnDynamicModalWa');
        if (waBtn) {
            const encodedText = encodeURIComponent(`Halo Master Cafe, saya ingin memesan & konfirmasi berat untuk menu timbangan: *${menuName}*`);
            waBtn.href = `https://wa.me/${window.defaultWaCleanNumber}?text=${encodedText}`;
        }

        const modalEl = document.getElementById('dynamicPriceModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        } else {
            alert(`Menu ${menuName} adalah menu timbangan hidup. Disarankan dipesan melalui Waitress atau WhatsApp Kafe.`);
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
