/**
 * Master Cafe POS - Waitress Module: Order Status & Completion Flow
 * File: public/js/waitress/modules/status.js
 * Deskripsi: Menangani perubahan status siklus pesanan (Dimasak -> Selesai),
 *            optimistic update tanpa delay, dialog penyelesaian pesanan, dan cetak tiket dapur.
 */

(function () {
    'use strict';

    window.activeCompletedOrderId = null;

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    // --- Modal Selesai & Cetak Struk Controller ---
    window.showOrderCompletedModal = function (id) {
        window.activeCompletedOrderId = id;
        const ids = Array.isArray(id) ? id : (typeof id === 'string' && id.includes(',') ? id.split(',').map(s => s.trim()) : [id]);
        const primaryId = ids[0];
        const card = document.getElementById('order-card-' + primaryId);

        let customerName = 'Pelanggan';
        let orderType = 'Dine-In';
        let isPaid = true;

        if (card) {
            const customerEl = card.querySelector('.small.text-white.fw-bold span.text-white');
            if (customerEl) customerName = customerEl.innerText.trim();

            const typeEl = card.querySelector('.badge.rounded-pill.text-bg-warning, .badge.rounded-pill.text-bg-secondary');
            if (typeEl) orderType = typeEl.innerText.trim();

            const unpaidBadge = card.querySelector('.badge.bg-danger.border-danger');
            if (unpaidBadge && unpaidBadge.innerText.includes('Belum Lunas')) {
                isPaid = false;
            }
        }

        const idParam = ids.join(',');
        const idLabel = ids.length > 1 ? `#${ids.join(' & #')}` : `#${primaryId}`;

        const subTitle = document.getElementById('orderCompletedSubtitle');
        if (subTitle) subTitle.innerText = `Pesanan ${idLabel}`;

        const custEl = document.getElementById('orderCompletedCustomer');
        if (custEl) custEl.innerText = customerName;

        const typeEl = document.getElementById('orderCompletedType');
        if (typeEl) typeEl.innerText = orderType;

        const payBadge = document.getElementById('orderCompletedPayBadge');
        const promptText = document.getElementById('orderCompletedPromptText');
        const actionsContainer = document.getElementById('orderCompletedActions');

        if (!window.defaultOrderCompletedActionsHTML && actionsContainer) {
            window.defaultOrderCompletedActionsHTML = actionsContainer.innerHTML;
        }

        if (!isPaid) {
            if (payBadge) {
                payBadge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1';
                payBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i>Belum Lunas';
            }
            if (promptText) {
                promptText.innerHTML = '<span class="text-warning fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Pesanan telah selesai disiapkan, namun belum lunas.</span><br><small class="text-white-50">Silakan terima pembayaran terlebih dahulu dari konsumen.</small>';
            }
            if (actionsContainer) {
                actionsContainer.innerHTML = `
                    <button type="button" class="btn btn-warning text-dark fw-bold py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="window.closeModalById('orderCompletedModal'); window.payGroupOrders('${idParam}');">
                        <i class="bi bi-cash-stack fs-5"></i> Terima Pembayaran Sekarang
                    </button>
                    <button type="button" class="btn btn-secondary fw-semibold py-2 rounded-3 mt-1" data-bs-dismiss="modal">
                        Tutup
                    </button>
                `;
            }
        } else {
            if (payBadge) {
                payBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success px-2 py-1';
                payBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Lunas';
            }
            if (promptText) {
                promptText.innerText = 'Pesanan telah selesai disiapkan! Apakah ingin mencetak struk transaksi sekarang?';
            }
            if (actionsContainer && window.defaultOrderCompletedActionsHTML) {
                actionsContainer.innerHTML = window.defaultOrderCompletedActionsHTML;
            }

            const browserBtn = document.getElementById('btnOrderCompletedBrowser');
            if (browserBtn) {
                browserBtn.href = `/kasir/order/${idParam}/receipt`;
            }

            const thermalBtn = document.getElementById('btnOrderCompletedThermal');
            if (thermalBtn) {
                thermalBtn.onclick = function () {
                    if (typeof window.printThermal === 'function') {
                        window.printThermal(primaryId);
                    }
                };
            }
        }

        if (typeof window.openModalById === 'function') {
            window.openModalById('orderCompletedModal');
        }
    };

    // Cetak Tiket Dapur / Koki
    window.printKitchenTicket = function (id) {
        window.open(`/kasir/order/${id}/kitchen-receipt`, 'KitchenTicket_' + id, 'width=420,height=600,scrollbars=yes');
    };

    // Update Status Pesanan (Optimistic Update: 0 Detik Instan!)
    window.updateOrderStatus = function (id, status, btnElement) {
        const ids = Array.isArray(id) ? id : (typeof id === 'string' && id.includes(',') ? id.split(',').map(s => s.trim()) : [id]);
        const primaryId = ids[0];
        const card = document.getElementById('order-card-' + primaryId);
        const badgeContainer = card ? card.querySelector('.card-header .badge') : null;
        const idLabel = ids.length > 1 ? `#${ids.join(' & #')}` : `#${primaryId}`;
        const idParam = ids.join(',');

        // ⚡ OPTIMISTIC UPDATE: Langsung ubah badge & tombol di layar kasir dalam 0 milidetik!
        if (status === 'processing') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-success w-100 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus('${idParam}', 'completed', this)">
                    <i class="bi bi-check2-all me-1"></i> Selesai Dimasak
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan ${idLabel} sedang dimasak!`, 'success');
        } else if (status === 'completed') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> SELESAI</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-outline-success w-100 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.showOrderCompletedModal('${idParam}')">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan ${idLabel} selesai!`, 'success');

            // Buka modal dialog konfirmasi selesai & opsi cetak struk
            window.showOrderCompletedModal(idParam);
        }

        // Kirim permintaan ke server di background tanpa menghalangi kasir
        fetch(`/kasir/order/${primaryId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status, order_ids: ids.map(i => parseInt(i)) })
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    if (window.showToast) window.showToast(data.error, 'danger');
                    else alert(data.error);
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
                    }
                } else {
                    if (typeof fetchActiveOrdersCount === 'function') {
                        fetchActiveOrdersCount();
                    }
                    if (status === 'completed' && typeof window.reloadCompletedOrders === 'function') {
                        window.reloadCompletedOrders(true);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Gagal sinkron status pesanan ke server.', 'danger');
                if (typeof window.reloadActiveOrdersCards === 'function') {
                    window.reloadActiveOrdersCards(true);
                }
            });
    };

    // Inisialisasi event saat modal selesai ditutup
    document.addEventListener('DOMContentLoaded', function () {
        const completedModalEl = document.getElementById('orderCompletedModal');
        if (completedModalEl) {
            completedModalEl.addEventListener('hidden.bs.modal', function () {
                window.activeCompletedOrderId = null;
                if (typeof window.reloadActiveOrdersCards === 'function') {
                    window.reloadActiveOrdersCards(false);
                }
            });
        }
    });

})();
