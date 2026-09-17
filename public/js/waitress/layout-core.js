/**
 * Master Cafe POS - Waitress Layout Core Engine
 * File: public/js/waitress/layout-core.js
 * Deskripsi: Menangani sinkronisasi badge pesanan aktif, notifikasi panggilan meja (call bell),
 *            toggle tema gelap (Dark Mode), audio bell player (HTML5 + Web Audio API fallback),
 *            serta integrasi Laravel Echo / WebSocket.
 */

(function () {
    'use strict';

    const config = window.WaitressLayoutConfig || {};

    function getCsrfToken() {
        return config.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    let lastKnownOrderId = null;
    let lastKnownCount = null;
    let lastKnownHash = null;
    let isInitialSync = true;
    let lastNotifiedOrderId = null;

    window.triggerNewOrderNotification = function (orderId, message) {
        if (typeof window.reloadActiveOrdersCards === 'function') {
            window.reloadActiveOrdersCards(true);
        }
        if (typeof window.reloadMejaGrid === 'function') {
            window.reloadMejaGrid(true);
        }

        if (orderId && lastNotifiedOrderId === orderId) {
            return;
        }
        if (orderId) {
            lastNotifiedOrderId = orderId;
        }

        if (window.playDingSound) {
            window.playDingSound();
        }

        if (window.showToast) {
            window.showToast(message || 'Pesanan baru masuk!', 'success');
        }
    };

    window.fetchActiveOrdersCount = function () {
        if (document.hidden) return;
        const url = config.activeOrdersCountUrl || '/kasir/api/active-orders-count';

        fetch(url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        })
            .then(response => {
                if (!response.ok) return null;
                return response.json();
            })
            .then(data => {
                if (!data) return;
                const badge = document.getElementById('badge-active-orders');
                if (badge) {
                    if (data.count > 0) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }

                const tabBadge = document.getElementById('tab-count-active');
                if (tabBadge) {
                    tabBadge.innerText = data.count || 0;
                }

                const currentLatestId = parseInt(data.latest_id) || 0;
                const currentCount = parseInt(data.count) || 0;
                const currentHash = data.hash || null;

                if (!isInitialSync) {
                    const hasNewOrder = (currentLatestId > 0 && lastKnownOrderId > 0 && currentLatestId > lastKnownOrderId) ||
                        (lastKnownCount !== null && currentCount > lastKnownCount);
                    const hasStateChange = (lastKnownHash !== null && currentHash !== null && currentHash !== lastKnownHash);

                    if (hasStateChange || hasNewOrder) {
                        console.log('[Sync] Perubahan status/pembayaran pesanan terdeteksi!');
                        if (typeof window.reloadActiveOrdersCards === 'function') {
                            window.reloadActiveOrdersCards(true);
                        }
                        if (typeof window.reloadCompletedOrders === 'function') {
                            window.reloadCompletedOrders(true);
                        }
                        if (typeof window.reloadMejaGrid === 'function') {
                            window.reloadMejaGrid(true);
                        }
                        if (hasNewOrder) {
                            window.triggerNewOrderNotification(currentLatestId, 'Pesanan baru masuk!');
                        }
                    }
                }

                lastKnownOrderId = currentLatestId;
                lastKnownCount = currentCount;
                lastKnownHash = currentHash;
                isInitialSync = false;
            })
            .catch(() => { });
    };

    let knownNotifIds = new Set();
    let isInitialNotifSync = true;

    window.dismissCallBellNotif = function (notifId) {
        const notifBase = config.notificationsUrl || '/kasir/api/notifications';
        fetch(notifBase + '/' + notifId + '/read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(() => {
                if (window.showToast) window.showToast('Panggilan meja telah ditanggapi', 'success');
                window.fetchWaitressNotifications();
                if (typeof window.reloadMejaGrid === 'function') {
                    window.reloadMejaGrid(true);
                }
            })
            .catch(err => console.error('Gagal memproses tanggapan panggilan:', err));
    };

    window.fetchWaitressNotifications = function () {
        if (document.hidden) return;
        const notifBase = config.notificationsUrl || '/kasir/api/notifications';

        fetch(notifBase + (notifBase.includes('?') ? '&' : '?') + '_t=' + Date.now(), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
            .then(response => {
                if (!response.ok) return [];
                return response.json();
            })
            .then(notifications => {
                if (!Array.isArray(notifications)) return;

                const callBells = notifications.filter(n => n.type === 'call_bell');
                const badge = document.getElementById('badge-call-bells');
                const bellBtn = document.getElementById('dropdownCallBell');
                const listContainer = document.getElementById('dropdown-call-bells-list');

                if (badge) {
                    if (callBells.length > 0) {
                        badge.innerText = callBells.length;
                        badge.style.display = 'inline-block';
                        if (bellBtn) bellBtn.classList.add('text-warning');
                    } else {
                        badge.style.display = 'none';
                        if (bellBtn) bellBtn.classList.remove('text-warning');
                    }
                }

                if (listContainer) {
                    if (callBells.length > 0) {
                        let itemsHtml = '<li class="dropdown-header text-white-50 fw-bold small border-bottom border-secondary border-opacity-25 pb-2 mb-1"><i class="bi bi-bell-fill me-1 text-warning"></i> Panggilan Meja Aktif (' + callBells.length + ')</li>';
                        callBells.forEach(c => {
                            itemsHtml += '<li class="px-2 py-1"><div class="d-flex justify-content-between align-items-center bg-dark p-2 rounded-3 border border-secondary border-opacity-25"><div><strong class="text-warning small d-block"><i class="bi bi-bell-fill me-1"></i>' + c.message + '</strong><small class="text-white-50" style="font-size: 0.72rem;">Konsumen memanggil pelayan</small></div><button onclick="dismissCallBellNotif(' + c.id + ')" class="btn btn-sm btn-warning rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;"><i class="bi bi-check2"></i> Tanggapi</button></div></li>';
                        });
                        listContainer.innerHTML = itemsHtml;
                    } else {
                        listContainer.innerHTML = '<li class="dropdown-header text-white-50 fw-bold small border-bottom border-secondary border-opacity-25 pb-2 mb-1"><i class="bi bi-bell-fill me-1 text-warning"></i> Panggilan Meja Aktif</li><li><span class="dropdown-item small text-muted py-2">Tidak ada panggilan aktif</span></li>';
                    }
                }

                notifications.forEach(notif => {
                    if (!knownNotifIds.has(notif.id)) {
                        knownNotifIds.add(notif.id);
                        if (!isInitialNotifSync) {
                            if (window.playDingSound) {
                                window.playDingSound();
                            }
                            if (window.showToast) {
                                const isCallBell = notif.type === 'call_bell';
                                window.showToast((isCallBell ? '🔔 ' : '📦 ') + notif.message, isCallBell ? 'warning' : 'success');
                            }
                            if (typeof window.reloadMejaGrid === 'function') {
                                window.reloadMejaGrid(true);
                            }
                        }
                    }
                });
                isInitialNotifSync = false;
            })
            .catch(() => { });
    };

    // --- Audio Player (HTML5 Audio + Web Audio API Synthesizer Fallback) ---
    let audioCtx = null;

    function getAudioContext() {
        if (!audioCtx) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (AudioContextClass) audioCtx = new AudioContextClass();
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    function primeAudio() {
        const bellAudio = document.getElementById('kasirBellAudio');
        if (bellAudio) {
            bellAudio.load();
        }
        getAudioContext();
    }
    ['click', 'pointerdown', 'keydown', 'touchstart'].forEach(evt => {
        document.addEventListener(evt, primeAudio, { once: false, passive: true });
    });

    function playWebAudioChime() {
        try {
            const ctx = getAudioContext();
            if (ctx && ctx.state !== 'suspended') {
                const now = ctx.currentTime;
                const osc1 = ctx.createOscillator();
                const osc2 = ctx.createOscillator();
                const gain = ctx.createGain();

                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(1046.5, now);
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(2093.0, now);

                gain.gain.setValueAtTime(0.9, now);
                gain.gain.exponentialRampToValueAtTime(0.0001, now + 1.4);

                osc1.connect(gain);
                osc2.connect(gain);
                gain.connect(ctx.destination);

                osc1.start(now);
                osc2.start(now);
                osc1.stop(now + 1.4);
                osc2.stop(now + 1.4);
            }
        } catch (e) {
            console.warn('[Audio] WebAudio error:', e);
        }
    }

    window.playDingSound = function (isTest = false) {
        const bellAudio = document.getElementById('kasirBellAudio');
        try {
            if (bellAudio) {
                bellAudio.currentTime = 0;
                const p = bellAudio.play();
                if (p !== undefined) {
                    p.catch(() => {
                        playWebAudioChime();
                    });
                }
            } else {
                playWebAudioChime();
            }
        } catch (e) {
            playWebAudioChime();
        }

        if (isTest && window.showToast) {
            window.showToast('🔔 Bunyi "Ting" berhasil diputar!', 'success');
        }
    };

    // --- Lifecycle Init (DOMContentLoaded) ---
    document.addEventListener('DOMContentLoaded', () => {
        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            const dmIcon = darkModeToggle.querySelector('i');

            function applyDarkMode(isDark) {
                if (isDark) {
                    document.body.classList.add('dark-mode');
                    if (dmIcon) dmIcon.classList.replace('bi-moon-stars', 'bi-sun');
                    localStorage.setItem('kasirDarkMode', 'true');
                } else {
                    document.body.classList.remove('dark-mode');
                    if (dmIcon) dmIcon.classList.replace('bi-sun', 'bi-moon-stars');
                    localStorage.setItem('kasirDarkMode', 'false');
                }
            }

            if (localStorage.getItem('kasirDarkMode') === 'true') {
                applyDarkMode(true);
            }

            darkModeToggle.addEventListener('click', () => {
                const isCurrentlyDark = document.body.classList.contains('dark-mode');
                applyDarkMode(!isCurrentlyDark);
            });
        }

        window.fetchActiveOrdersCount();
        window.fetchWaitressNotifications();

        let syncInterval = setInterval(() => {
            window.fetchActiveOrdersCount();
            window.fetchWaitressNotifications();
        }, 3500);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                window.fetchActiveOrdersCount();
                window.fetchWaitressNotifications();
            }
        });

        // Setup WebSocket (Laravel Echo / Reverb)
        setTimeout(() => {
            if (window.Echo) {
                console.log('[WebSocket] Subscribing to kasir-notifications channel...');
                const kasirChannel = window.Echo.channel('kasir-notifications');

                const handlePesananBaru = (e) => {
                    window.fetchActiveOrdersCount();
                    window.fetchWaitressNotifications();
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
                    }
                    if (typeof window.reloadCompletedOrders === 'function') {
                        window.reloadCompletedOrders(true);
                    }
                    if (typeof window.reloadMejaGrid === 'function') {
                        window.reloadMejaGrid(true);
                    }
                    window.triggerNewOrderNotification(e.id, e.message || 'Pesanan baru masuk!');
                };

                const handleMejaStatus = (e) => {
                    window.fetchWaitressNotifications();
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
                    }
                    if (typeof window.reloadCompletedOrders === 'function') {
                        window.reloadCompletedOrders(true);
                    }
                    if (typeof window.reloadMejaGrid === 'function') {
                        window.reloadMejaGrid(true);
                    }
                    window.dispatchEvent(new CustomEvent('meja-status-updated', { detail: e }));
                };

                kasirChannel
                    .listen('.PesananBaru', handlePesananBaru)
                    .listen('PesananBaru', handlePesananBaru)
                    .listen('.MejaStatusUpdated', handleMejaStatus)
                    .listen('MejaStatusUpdated', handleMejaStatus);

                if (window.Echo.connector && window.Echo.connector.pusher) {
                    const pusherConn = window.Echo.connector.pusher.connection;
                    pusherConn.bind('connected', () => {
                        clearInterval(syncInterval);
                        syncInterval = setInterval(window.fetchActiveOrdersCount, 4000);
                    });
                    pusherConn.bind('unavailable', () => {
                        clearInterval(syncInterval);
                        syncInterval = setInterval(window.fetchActiveOrdersCount, 4000);
                    });
                    pusherConn.bind('failed', () => {
                        clearInterval(syncInterval);
                        syncInterval = setInterval(window.fetchActiveOrdersCount, 4000);
                    });
                    pusherConn.bind('disconnected', () => {
                        clearInterval(syncInterval);
                        syncInterval = setInterval(window.fetchActiveOrdersCount, 4000);
                    });
                }
            } else {
                console.warn('[WebSocket] Laravel Echo is not loaded. Using smart auto-sync 4s.');
            }
        }, 1000);
    });

    console.info('[Waitress Layout] Layout core initialized.');

})();
