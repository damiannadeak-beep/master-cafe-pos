/**
 * Master Cafe POS - Waitress Module: Split Bill
 * File: public/js/waitress/modules/split-bill.js
 * Deskripsi: Menangani pemisahan pesanan / pecah bon meja konsumen secara selektif
 *            berdasarkan item dan kuantitas porsi.
 */

(function () {
    'use strict';

    let splitOrderId = null;
    let splitDetails = [];

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    window.openSplitModal = function (orderId, btnElement) {
        splitOrderId = orderId;
        const btn = btnElement ? (btnElement.closest('button') || btnElement) : null;
        let details = [];
        if (btn) {
            let rawData = btn.getAttribute('data-details') || '';
            try {
                details = JSON.parse(rawData);
            } catch (e) {
                try {
                    // Fallback jika ada HTML entity decoding issue
                    const txt = document.createElement('textarea');
                    txt.innerHTML = rawData;
                    details = JSON.parse(txt.value);
                } catch (e2) {
                    console.error('Error parse data-details:', e2);
                    details = [];
                }
            }
        }
        splitDetails = details;

        const splitOrderSpan = document.getElementById('split-order-id');
        if (splitOrderSpan) splitOrderSpan.innerText = orderId;

        let html = '';
        if (details.length === 0) {
            html = '<div class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-circle me-1"></i> Data item pesanan tidak ditemukan atau gagal dimuat.</div>';
        } else {
            details.forEach(item => {
                const menuName = item.menu ? item.menu.nama_menu : (item.nama_menu || 'Menu');
                const subtotal = Number(item.subtotal || 0).toLocaleString('id-ID');
                html += `
                <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07);">
                    <div class="form-check flex-grow-1 mb-0">
                        <input class="form-check-input split-cb" type="checkbox" value="${item.id}" id="chk_${item.id}" style="cursor: pointer;">
                        <label class="form-check-label text-light fw-semibold" for="chk_${item.id}" style="cursor: pointer;">
                            ${menuName}
                            <div class="small text-warning fw-normal">Rp ${subtotal}</div>
                        </label>
                    </div>
                    <div style="width: 75px;">
                        <input type="number" class="form-control text-white border-secondary form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled style="background-color: #0d1117;">
                    </div>
                    <span class="ms-2 small text-white-50">/ ${item.jumlah}</span>
                </div>
                `;
            });
        }

        const splitContainer = document.getElementById('split-items-container');
        if (splitContainer) splitContainer.innerHTML = html;

        document.querySelectorAll('.split-cb').forEach(cb => {
            cb.addEventListener('change', function () {
                const qtyInput = document.getElementById('qty_' + this.value);
                if (qtyInput) qtyInput.disabled = !this.checked;
            });
        });

        if (typeof window.openModalById === 'function') {
            window.openModalById('splitModal');
        }
    };

    window.executeSplit = function () {
        let itemsToSplit = [];
        document.querySelectorAll('.split-cb:checked').forEach(cb => {
            let idDetail = cb.value;
            let qtyEl = document.getElementById('qty_' + idDetail);
            let qty = qtyEl ? qtyEl.value : 1;
            itemsToSplit.push({
                id_detail: idDetail,
                jumlah: parseInt(qty)
            });
        });

        if (itemsToSplit.length === 0) {
            if (window.showToast) window.showToast('Pilih minimal 1 item untuk dipisah.', 'warning');
            else alert('Pilih minimal 1 item untuk dipisah.');
            return;
        }

        let isAll = true;
        splitDetails.forEach(sd => {
            let found = itemsToSplit.find(i => i.id_detail == sd.id);
            if (!found || found.jumlah < sd.jumlah) {
                isAll = false;
            }
        });

        if (isAll) {
            if (window.showToast) window.showToast('Tidak bisa memisah semua item sekaligus.', 'warning');
            else alert('Anda tidak bisa memisah semua item.');
            return;
        }

        if (!confirm('Pisahkan item terpilih ke pesanan (Bon) baru?')) return;

        fetch(`/kasir/order/${splitOrderId}/split`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ split_items: itemsToSplit })
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    if (window.showToast) window.showToast(data.error, 'danger');
                    else alert(data.error);
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                } else {
                    if (typeof window.closeModalById === 'function') {
                        window.closeModalById('splitModal');
                    }
                    if (window.showToast) window.showToast(data.message || 'Bon berhasil dipisah!', 'success');
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Terjadi kesalahan saat memisah bon.', 'danger');
            });
    };

})();
