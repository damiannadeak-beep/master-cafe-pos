/**
 * Master Cafe POS - Waitress Active Orders Monitor (Main Orchestrator)
 * File: public/js/waitress/pesanan-aktif.js
 * Deskripsi: Entry point & shared helper namespace untuk sub-modul antrean kasir.
 *            Sub-modul logika terbagi di: public/js/waitress/modules/
 */

(function () {
    'use strict';

    // Inisialisasi WaitressApp Architecture (Enterprise Namespace)
    window.WaitressApp = window.WaitressApp || {
        version: '2.0.0',
        state: {
            currentTab: 'active',
            lastHash: null,
            collapsedSubOrders: new Set(),
            isReloadingCards: false,
            isReloadingCompleted: false,
            isReloadingVoided: false,
            activeCompletedOrderId: null,
        },
        modules: {},
        helper: {
            getCsrfToken: function () {
                return window.WaitressConfig?.csrfToken ||
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                    '';
            },
            getPesananAktifUrl: function () {
                return window.WaitressConfig?.pesananAktifUrl || '/kasir/pesanan-aktif';
            },
            getOrdersCountUrl: function () {
                return window.WaitressConfig?.ordersCountUrl || '/kasir/api/active-orders-count';
            }
        }
    };

    // Backward Compatibility Aliases
    window.WaitressHelper = Object.assign(window.WaitressHelper || {}, window.WaitressApp.helper);
    window.collapsedSubOrders = window.WaitressApp.state.collapsedSubOrders;

    console.info('[Waitress POS] WaitressApp v2.0 Enterprise Engine initialized.');

    // Accordion / Collapse Handler untuk Sub-Pesanan Meja Aktif
    window.toggleSubOrderCollapse = function (orderId) {
        orderId = String(orderId);
        const rows = document.querySelectorAll('.suborder-items-' + orderId);
        const chevron = document.getElementById('suborder-chevron-' + orderId);
        if (!rows || rows.length === 0) return;

        const willHide = !rows[0].classList.contains('d-none');
        rows.forEach(r => {
            if (willHide) {
                r.classList.add('d-none');
            } else {
                r.classList.remove('d-none');
            }
        });

        if (willHide) {
            window.collapsedSubOrders.add(orderId);
        } else {
            window.collapsedSubOrders.delete(orderId);
        }

        if (chevron) {
            chevron.style.transform = willHide ? 'rotate(-90deg)' : 'rotate(0deg)';
        }
    };

    window.toggleAllSubOrders = function (cardId, orderIds) {
        if (!Array.isArray(orderIds) || orderIds.length === 0) return;

        let anyVisible = false;
        orderIds.forEach(id => {
            const rows = document.querySelectorAll('.suborder-items-' + id);
            rows.forEach(r => {
                if (!r.classList.contains('d-none')) {
                    anyVisible = true;
                }
            });
        });

        const shouldHide = anyVisible;
        orderIds.forEach(id => {
            const idStr = String(id);
            const rows = document.querySelectorAll('.suborder-items-' + idStr);
            rows.forEach(r => {
                if (shouldHide) {
                    r.classList.add('d-none');
                } else {
                    r.classList.remove('d-none');
                }
            });
            if (shouldHide) {
                window.collapsedSubOrders.add(idStr);
            } else {
                window.collapsedSubOrders.delete(idStr);
            }
            const chevron = document.getElementById('suborder-chevron-' + idStr);
            if (chevron) {
                chevron.style.transform = shouldHide ? 'rotate(-90deg)' : 'rotate(0deg)';
            }
        });
    };

    // Pulihkan status lipatan setelah kartu pesanan di-reload secara realtime (auto-sync)
    window.restoreCollapsedSubOrders = function () {
        if (!window.collapsedSubOrders || window.collapsedSubOrders.size === 0) return;
        window.collapsedSubOrders.forEach(idStr => {
            const rows = document.querySelectorAll('.suborder-items-' + idStr);
            rows.forEach(r => r.classList.add('d-none'));
            const chevron = document.getElementById('suborder-chevron-' + idStr);
            if (chevron) {
                chevron.style.transform = 'rotate(-90deg)';
            }
        });
    };

})();
