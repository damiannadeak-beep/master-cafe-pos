/**
 * Master Cafe POS - Waitress Module: Realtime Sync & Tab Controller
 * File: public/js/waitress/modules/sync.js
 * Deskripsi: Mengatur perpindahan tab antrean vs riwayat, filter pencarian riwayat,
 *            serta pembaruan dinamis kartu pesanan aktif tanpa refresh halaman.
 */

(function () {
    'use strict';

    let currentOrderTab = 'active';
    let isReloadingCards = false;
    let isReloadingCompleted = false;
    let isReloadingVoided = false;

    function getPesananAktifUrl() {
        return window.WaitressHelper?.getPesananAktifUrl?.() ||
            window.WaitressConfig?.pesananAktifUrl ||
            '/kasir/pesanan-aktif';
    }

    // --- TAB CONTROLLER: Pesanan Aktif vs Riwayat Selesai vs Riwayat Dibatalkan ---
    window.switchOrderTab = function (tab) {
        currentOrderTab = tab;
        const paneActive = document.getElementById('pane-active-orders');
        const paneCompleted = document.getElementById('pane-completed-orders');
        const paneVoided = document.getElementById('pane-voided-orders');
        const btnActive = document.getElementById('tab-btn-active');
        const btnCompleted = document.getElementById('tab-btn-completed');
        const btnVoided = document.getElementById('tab-btn-voided');
        const pageTitle = document.getElementById('page-order-title');

        if (paneActive) paneActive.style.display = (tab === 'active') ? 'block' : 'none';
        if (paneCompleted) paneCompleted.style.display = (tab === 'completed') ? 'block' : 'none';
        if (paneVoided) paneVoided.style.display = (tab === 'voided') ? 'block' : 'none';

        if (btnActive) btnActive.classList.toggle('active', tab === 'active');
        if (btnCompleted) btnCompleted.classList.toggle('active', tab === 'completed');
        if (btnVoided) btnVoided.classList.toggle('active', tab === 'voided');

        if (tab === 'active') {
            if (pageTitle) pageTitle.innerHTML = '<i class="bi bi-receipt text-accent me-1.5" style="color: #c08e5c;"></i> Monitor Pesanan Waitress';
        } else if (tab === 'completed') {
            if (pageTitle) pageTitle.innerHTML = '<i class="bi bi-check2-all text-success me-1.5"></i> Riwayat Pesanan Selesai';
            window.reloadCompletedOrders(true);
        } else if (tab === 'voided') {
            if (pageTitle) pageTitle.innerHTML = '<i class="bi bi-trash3 text-danger me-1.5"></i> Riwayat Pesanan Dibatalkan / Void';
            window.reloadVoidedOrders(true);
        }
    };

    window.refreshCurrentTab = function () {
        if (currentOrderTab === 'active') {
            window.reloadActiveOrdersCards(false);
        } else if (currentOrderTab === 'completed') {
            window.reloadCompletedOrders(false);
        } else if (currentOrderTab === 'voided') {
            window.reloadVoidedOrders(false);
        }
    };

    window.reloadCompletedOrders = function (silent = false) {
        if (isReloadingCompleted) return;
        const container = document.getElementById('completed-orders-container');
        if (!container) return;

        isReloadingCompleted = true;
        const refreshBtn = document.getElementById('btn-refresh-orders');
        if (refreshBtn && !silent) {
            const icon = refreshBtn.querySelector('i');
            if (icon) icon.classList.add('spin-animation');
            refreshBtn.disabled = true;
        }

        fetch(getPesananAktifUrl() + '?history_only=1&_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml',
                'Cache-Control': 'no-cache'
            }
        })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                const items = container.querySelectorAll('.completed-order-item');
                const tabBadge = document.getElementById('tab-count-completed');
                if (tabBadge) tabBadge.innerText = items.length;

                const searchInput = document.getElementById('search-completed-input');
                if (searchInput && searchInput.value.trim() !== '') {
                    window.filterCompletedOrders(searchInput.value);
                } else {
                    const filterInfo = document.getElementById('completed-filter-info');
                    if (filterInfo) filterInfo.innerText = `Menampilkan ${items.length} pesanan selesai hari ini`;
                }
            })
            .catch(err => {
                console.error('[RiwayatPesanan] Gagal memuat riwayat:', err);
            })
            .finally(() => {
                isReloadingCompleted = false;
                if (refreshBtn) {
                    const icon = refreshBtn.querySelector('i');
                    if (icon) icon.classList.remove('spin-animation');
                    refreshBtn.disabled = false;
                }
            });
    };

    window.filterCompletedOrders = function (keyword) {
        const term = (keyword || '').toLowerCase().trim();
        const items = document.querySelectorAll('#completed-orders-container .completed-order-item');
        let visibleCount = 0;

        items.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            if (!term || searchData.includes(term)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const filterInfo = document.getElementById('completed-filter-info');
        if (filterInfo) {
            if (term) {
                filterInfo.innerText = `Ditemukan ${visibleCount} dari ${items.length} pesanan selesai`;
            } else {
                filterInfo.innerText = `Menampilkan ${items.length} pesanan selesai hari ini`;
            }
        }
    };

    // --- RIWAYAT VOID / DIBATALKAN: Reload & Filter ---
    window.reloadVoidedOrders = function (silent = false) {
        if (isReloadingVoided) return;
        const container = document.getElementById('voided-orders-container');
        if (!container) return;

        isReloadingVoided = true;
        const refreshBtn = document.getElementById('btn-refresh-orders');
        if (refreshBtn && !silent) {
            const icon = refreshBtn.querySelector('i');
            if (icon) icon.classList.add('spin-animation');
            refreshBtn.disabled = true;
        }

        fetch(getPesananAktifUrl() + '?voided_only=1&_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml',
                'Cache-Control': 'no-cache'
            }
        })
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                const items = container.querySelectorAll('.voided-order-item');
                const tabBadge = document.getElementById('tab-count-voided');
                if (tabBadge) tabBadge.innerText = items.length;

                const searchInput = document.getElementById('search-voided-input');
                if (searchInput && searchInput.value.trim() !== '') {
                    window.filterVoidedOrders(searchInput.value);
                } else {
                    const filterInfo = document.getElementById('voided-filter-info');
                    if (filterInfo) filterInfo.innerText = `Menampilkan ${items.length} pesanan dibatalkan`;
                }
            })
            .catch(err => {
                console.error('[RiwayatVoid] Gagal memuat riwayat void:', err);
            })
            .finally(() => {
                isReloadingVoided = false;
                if (refreshBtn) {
                    const icon = refreshBtn.querySelector('i');
                    if (icon) icon.classList.remove('spin-animation');
                    refreshBtn.disabled = false;
                }
            });
    };

    window.filterVoidedOrders = function (keyword) {
        const term = (keyword || '').toLowerCase().trim();
        const items = document.querySelectorAll('#voided-orders-container .voided-order-item');
        let visibleCount = 0;

        items.forEach(card => {
            const searchData = card.getAttribute('data-search') || '';
            if (!term || searchData.includes(term)) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const filterInfo = document.getElementById('voided-filter-info');
        if (filterInfo) {
            if (term) {
                filterInfo.innerText = `Ditemukan ${visibleCount} dari ${items.length} pesanan dibatalkan`;
            } else {
                filterInfo.innerText = `Menampilkan ${items.length} pesanan dibatalkan`;
            }
        }
    };

    // --- Realtime Dynamic Cards Loader (Tanpa Refresh Halaman & State Preserving) ---
    window.reloadActiveOrdersCards = function (silent = false) {
        if (isReloadingCards) return;
        const container = document.getElementById('active-orders-container');
        if (!container) return;

        isReloadingCards = true;
        const refreshBtn = document.getElementById('btn-refresh-orders');
        if (refreshBtn && !silent) {
            const icon = refreshBtn.querySelector('i');
            if (icon) icon.classList.add('spin-animation');
            refreshBtn.disabled = true;
        }

        // Simpan posisi scroll vertikal sebelum DOM diganti
        const prevScrollY = window.scrollY || document.documentElement.scrollTop;

        fetch(getPesananAktifUrl() + '?cards_only=1&_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml',
                'Cache-Control': 'no-cache'
            }
        })
            .then(res => res.text())
            .then(html => {
                // Hindari menimpa jika kasir sedang membuka modal apa pun atau sedang melihat dialog selesai
                const anyModalOpen = document.querySelector('.modal.show');
                if (!anyModalOpen && !window.activeCompletedOrderId) {
                    container.innerHTML = html;
                    
                    // Pulihkan state accordion yang dilipat
                    if (typeof window.restoreCollapsedSubOrders === 'function') {
                        window.restoreCollapsedSubOrders();
                    }

                    // Kembalikan posisi scroll secara mulus dan instan tanpa loncat
                    window.scrollTo({ top: prevScrollY, behavior: 'instant' });

                    let totalOrdersCount = 0;
                    const activeCards = container.querySelectorAll('.order-card-item, [data-order-ids]');
                    activeCards.forEach(card => {
                        const ids = card.getAttribute('data-order-ids');
                        if (ids) {
                            totalOrdersCount += ids.split(',').filter(Boolean).length;
                        } else {
                            totalOrdersCount++;
                        }
                    });
                    const badgeActive = document.getElementById('tab-count-active');
                    if (badgeActive) badgeActive.innerText = totalOrdersCount;
                }
            })
            .catch(err => {
                console.error('[PesananAktif] Gagal memuat pesanan aktif:', err);
            })
            .finally(() => {
                isReloadingCards = false;
                if (refreshBtn) {
                    const icon = refreshBtn.querySelector('i');
                    if (icon) icon.classList.remove('spin-animation');
                    refreshBtn.disabled = false;
                }
            });
    };

    // --- SMART HASH-BASED REALTIME SYNC (Enterprise Standard) ---
    // Memeriksa hash pembaruan data ringan (~100 bytes) alih-alih request render HTML penuh (~30 KB)
    let lastKnownHash = null;
    let isCheckingHash = false;

    window.resetSyncHash = function () {
        lastKnownHash = null;
    };

    function checkDataHashAndSync() {
        if (isCheckingHash || document.hidden || document.querySelector('.modal.show') || window.activeCompletedOrderId) {
            return;
        }

        const countUrl = window.WaitressHelper?.getOrdersCountUrl?.() || '/kasir/api/active-orders-count';
        isCheckingHash = true;

        fetch(countUrl + '?_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Cache-Control': 'no-cache'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.hash) return;

                // Perbarui badge jumlah pesanan secara langsung tanpa render ulang
                const badgeActive = document.getElementById('tab-count-active');
                if (badgeActive && typeof data.count !== 'undefined') {
                    badgeActive.innerText = data.count;
                }
                const badgeCompleted = document.getElementById('tab-count-completed');
                if (badgeCompleted && typeof data.completed_count !== 'undefined') {
                    badgeCompleted.innerText = data.completed_count;
                }

                // Inisialisasi hash pertama kali tanpa render ulang
                if (lastKnownHash === null) {
                    lastKnownHash = data.hash;
                    return;
                }

                // HANYA jika hash data berbeda (terjadi perubahan pesanan riil di kafe):
                if (data.hash !== lastKnownHash) {
                    lastKnownHash = data.hash;

                    if (currentOrderTab === 'active' && !isReloadingCards) {
                        window.reloadActiveOrdersCards(true);
                    } else if (currentOrderTab === 'completed' && !isReloadingCompleted) {
                        window.reloadCompletedOrders(true);
                    } else if (currentOrderTab === 'voided' && !isReloadingVoided) {
                        window.reloadVoidedOrders(true);
                    }
                }
            })
            .catch(err => {
                // Silent catch: Jangan ganggu kasir saat koneksi wifi berkedip sesaat
            })
            .finally(() => {
                isCheckingHash = false;
            });
    }

    // Jalankan Smart Polling setiap 5 detik (sangat ringan, hanya hitungan milidetik)
    setInterval(checkDataHashAndSync, 5000);

    // Registrasi ke WaitressApp namespace
    if (window.WaitressApp) {
        window.WaitressApp.modules.sync = {
            switchOrderTab: window.switchOrderTab,
            refreshCurrentTab: window.refreshCurrentTab,
            reloadActiveOrdersCards: window.reloadActiveOrdersCards,
            reloadCompletedOrders: window.reloadCompletedOrders,
            reloadVoidedOrders: window.reloadVoidedOrders,
            checkDataHashAndSync: checkDataHashAndSync,
            resetSyncHash: window.resetSyncHash
        };
    }

})();
