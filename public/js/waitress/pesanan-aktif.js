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

    // Filter State & Logic untuk Pesanan Aktif (Search & Quick Filter Pills)
    window.activeOrderFilter = {
        search: '',
        status: 'all' // 'all' | 'unpaid' | 'paid' | 'cooking' | 'takeaway'
    };

    window.setActiveQuickFilter = function (filterType, btnEl) {
        window.activeOrderFilter.status = filterType;
        
        // Update active class on buttons
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        if (btnEl) {
            btnEl.classList.add('active');
        } else {
            const matchingBtn = document.querySelector(`.quick-filter-btn[data-filter="${filterType}"]`);
            if (matchingBtn) matchingBtn.classList.add('active');
        }

        window.filterActiveOrders();
    };

    window.filterActiveOrders = function () {
        const searchInput = document.getElementById('search-active-input');
        const term = searchInput ? (searchInput.value || '').toLowerCase().trim() : '';
        window.activeOrderFilter.search = term;

        const currentFilter = window.activeOrderFilter.status || 'all';
        const cards = document.querySelectorAll('#active-orders-container .order-card-item');
        let visibleCount = 0;

        cards.forEach(card => {
            const searchData = (card.getAttribute('data-search') || '').toLowerCase();
            const paidStatus = card.getAttribute('data-paid') || ''; // 'paid' or 'unpaid'
            const cookingStatus = card.getAttribute('data-cooking') || ''; // 'served', 'cooking', 'waiting'
            const tipe = card.getAttribute('data-tipe') || ''; // 'dine_in' or 'takeaway'

            // Cek kecocokan teks pencarian
            const matchesSearch = !term || searchData.includes(term);

            // Cek kecocokan pill filter
            let matchesFilter = true;
            if (currentFilter === 'unpaid') {
                matchesFilter = (paidStatus === 'unpaid');
            } else if (currentFilter === 'paid') {
                matchesFilter = (paidStatus === 'paid');
            } else if (currentFilter === 'cooking') {
                matchesFilter = (cookingStatus === 'cooking' || cookingStatus === 'waiting');
            } else if (currentFilter === 'takeaway') {
                matchesFilter = (tipe === 'takeaway');
            }

            if (matchesSearch && matchesFilter) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('active-visible-count');
        if (countEl) {
            countEl.innerText = visibleCount;
        }

        // Tampilkan pesan jika tidak ada pesanan yang cocok dengan filter
        let noMatchEl = document.getElementById('active-no-results-alert');
        if (visibleCount === 0 && cards.length > 0) {
            if (!noMatchEl) {
                noMatchEl = document.createElement('div');
                noMatchEl.id = 'active-no-results-alert';
                noMatchEl.className = 'col-12 text-center py-5';
                noMatchEl.innerHTML = `
                    <div class="d-inline-block p-3 rounded-circle mb-2" style="background: rgba(255, 255, 255, 0.05);">
                        <i class="bi bi-search text-white-50" style="font-size: 2.2rem;"></i>
                    </div>
                    <h6 class="fw-bold text-white-50 mb-1">Tidak Ada Pesanan yang Cocok</h6>
                    <p class="text-secondary small mb-0">Coba ubah kata kunci pencarian atau pilih filter pill lain.</p>
                `;
                const container = document.getElementById('active-orders-container');
                if (container) container.appendChild(noMatchEl);
            } else {
                noMatchEl.style.display = '';
            }
        } else if (noMatchEl) {
            noMatchEl.style.display = 'none';
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        if (typeof window.filterActiveOrders === 'function') {
            window.filterActiveOrders();
        }
    });

})();

