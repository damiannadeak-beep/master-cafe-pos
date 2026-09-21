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

        // ⚡ OPTIMISTIC UPDATE: Langsung ubah badge & tombol di layar kasir dengan layout proporsional
        if (btnElement) {
            btnElement.disabled = true;
        }

        if (status === 'processing') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-success flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus('${idParam}', 'completed', this)">
                    <i class="bi bi-check2-all me-1"></i> Selesai Dimasak
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan ${idLabel} sedang dimasak!`, 'success');
        } else if (status === 'completed') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> SELESAI</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-outline-success flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.showOrderCompletedModal('${idParam}')">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan ${idLabel} selesai!`, 'success');

            // Buka modal dialog konfirmasi selesai & opsi cetak struk
            window.showOrderCompletedModal(idParam);
        }

        // Kirim permintaan ke server di background
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
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
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

    // Update Status Sub-Pesanan Tertentu Saja (Per-Order Granular)
    window.updateSingleOrderStatus = function (orderId, targetStatus, btnElement) {
        orderId = parseInt(orderId);
        if (!orderId) return;

        // Optimistic UI feedback
        if (btnElement) {
            btnElement.style.pointerEvents = 'none';
            if (targetStatus === 'processing') {
                btnElement.className = 'badge bg-primary text-white py-0.5 px-2 rounded-pill border-0 d-inline-flex align-items-center gap-1 text-decoration-none';
                btnElement.innerHTML = '<i class="bi bi-check2"></i> Selesai';
                btnElement.setAttribute('onclick', `event.stopPropagation(); window.updateSingleOrderStatus(${orderId}, 'completed', this)`);
                btnElement.setAttribute('title', `Tandai pesanan #${orderId} selesai dimasak`);
                btnElement.style.pointerEvents = '';
            } else if (targetStatus === 'completed') {
                btnElement.outerHTML = '<span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-0.5 px-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.62rem;"><i class="bi bi-check2-circle"></i> Selesai</span>';
            }
        }

        if (window.showToast) {
            const msg = targetStatus === 'completed'
                ? `Pesanan #${orderId} telah ditandai selesai dimasak!`
                : `Pesanan #${orderId} mulai dimasak!`;
            window.showToast(msg, 'success');
        }

        fetch(`/kasir/order/${orderId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: targetStatus, order_ids: [orderId] })
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
                if (typeof window.reloadActiveOrdersCards === 'function') {
                    window.reloadActiveOrdersCards(true);
                }
                if (targetStatus === 'completed' && typeof window.reloadCompletedOrders === 'function') {
                    window.reloadCompletedOrders(true);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal update status pesanan #' + orderId, 'danger');
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

    // --- FITUR CHECKLIST (CENTANG) PER-ITEM MENU ---
    window.toggleItemServed = function (itemId, btnElement) {
        if (!itemId || !btnElement) return;

        const row = document.getElementById('order-item-row-' + itemId);
        const cardId = btnElement.getAttribute('data-card-id');
        const card = cardId ? document.getElementById('order-card-' + cardId) : btnElement.closest('.order-card-item');

        // Optimistic toggle
        const isCurrentlyServed = btnElement.querySelector('.bi-check-lg') !== null;
        const newServedState = !isCurrentlyServed;

        updateItemRowUI(itemId, newServedState);
        updateCardProgressUI(card);

        fetch(`/kasir/order/item/${itemId}/toggle-served`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                // Rollback if failed
                updateItemRowUI(itemId, isCurrentlyServed);
                updateCardProgressUI(card);
                if (window.showToast) window.showToast('Gagal mengubah status menu item.', 'danger');
            } else {
                if (window.showToast) window.showToast(data.message, 'success');
            }
        })
        .catch(err => {
            console.error('Toggle served error:', err);
            updateItemRowUI(itemId, isCurrentlyServed);
            updateCardProgressUI(card);
        });
    };

    function updateItemRowUI(itemId, isServed) {
        const row = document.getElementById('order-item-row-' + itemId);
        const btn = document.getElementById('item-check-btn-' + itemId);
        if (!row || !btn) return;

        const nameSpan = row.querySelector('.item-name');
        const qtySpan = row.querySelector('.item-qty-badge');
        let statusBadge = row.querySelector('.item-status-badge');

        if (isServed) {
            btn.style.background = 'rgba(46, 160, 67, 0.25)';
            btn.style.borderColor = '#2ea043';
            btn.innerHTML = '<i class="bi bi-check-lg text-success" style="font-size: 1.1rem;"></i>';
            btn.title = 'Klik untuk batal centang';
            row.classList.add('item-served');
            row.style.opacity = '0.9';

            if (nameSpan) {
                nameSpan.classList.add('text-white-50', 'text-decoration-line-through');
                nameSpan.classList.remove('text-white');
            }
            if (qtySpan) qtySpan.classList.add('text-success', 'fw-bold');
            if (!statusBadge) {
                const nameContainer = row.querySelector('.fw-medium > div');
                if (nameContainer) {
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success bg-opacity-25 text-success py-0 px-1 rounded-pill item-status-badge';
                    badge.style.fontSize = '0.62rem';
                    badge.innerHTML = '<i class="bi bi-check2"></i> Siap';
                    nameContainer.appendChild(badge);
                }
            }
        } else {
            btn.style.background = 'rgba(255, 255, 255, 0.08)';
            btn.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            btn.innerHTML = '<i class="bi bi-circle text-white-50" style="font-size: 0.65rem;"></i>';
            btn.title = 'Klik untuk centang (Siap/Diantar)';
            row.classList.remove('item-served');
            row.style.opacity = '1';

            if (nameSpan) {
                nameSpan.classList.remove('text-white-50', 'text-decoration-line-through');
                nameSpan.classList.add('text-white');
            }
            if (qtySpan) qtySpan.classList.remove('text-success', 'fw-bold');
            if (statusBadge) statusBadge.remove();
        }
    }

    function updateCardProgressUI(card) {
        if (!card) return;

        const allRows = card.querySelectorAll('.order-item-row');
        const totalItems = allRows.length;
        const servedItems = card.querySelectorAll('.item-check-btn .bi-check-lg').length;
        const allServed = (totalItems > 0 && servedItems === totalItems);

        card.setAttribute('data-total-items', totalItems);
        card.setAttribute('data-served-items', servedItems);

        const completeBtn = card.querySelector('button[id^="btn-complete-order-"]');
        if (completeBtn) {
            completeBtn.setAttribute('data-total-items', totalItems);
            completeBtn.setAttribute('data-served-items', servedItems);
            completeBtn.setAttribute('data-all-served', allServed ? '1' : '0');

            const counterBadge = completeBtn.querySelector('.counter-badge');
            if (counterBadge) {
                counterBadge.innerText = `${servedItems}/${totalItems} Siap`;
                if (allServed) {
                    counterBadge.className = 'badge bg-white text-success rounded-pill py-0 px-1.5 ms-1 counter-badge';
                    completeBtn.classList.remove('btn-outline-success');
                    completeBtn.classList.add('btn-success');
                } else {
                    counterBadge.className = 'badge bg-secondary bg-opacity-50 text-white rounded-pill py-0 px-1.5 ms-1 counter-badge';
                    completeBtn.classList.remove('btn-success');
                    completeBtn.classList.add('btn-outline-success');
                }
            }
        }
    }

    window.confirmCompleteOrder = function (idParam, btnElement) {
        if (!btnElement) return;

        const total = parseInt(btnElement.getAttribute('data-total-items') || '0');
        const served = parseInt(btnElement.getAttribute('data-served-items') || '0');
        const allServed = (btnElement.getAttribute('data-all-served') === '1');

        if (!allServed && total > served) {
            const unserved = total - served;
            const confirmMsg = `Perhatian: Masih ada ${unserved} menu yang belum dicentang siap.\n\nApakah Anda yakin ingin menandai seluruh pesanan telah selesai dimasak sekarang?`;
            if (!confirm(confirmMsg)) {
                return;
            }
        }

        // Jalankan update status selesai
        window.updateOrderStatus(idParam, 'completed', btnElement);
    };

    if (window.WaitressApp) {
        window.WaitressApp.modules.status = {
            update: window.updateOrderStatus,
            updateSingle: window.updateSingleOrderStatus,
            showCompletedModal: window.showOrderCompletedModal,
            toggleItemServed: window.toggleItemServed,
            confirmCompleteOrder: window.confirmCompleteOrder
        };
    }

})();

