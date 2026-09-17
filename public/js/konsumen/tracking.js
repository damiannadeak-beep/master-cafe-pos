/**
 * Master Cafe POS - Consumer Tracking Engine
 * File: public/js/konsumen/tracking.js
 * Deskripsi: Menangani sinkronisasi realtime status pesanan konsumen, multi-order switcher,
 *            pembatalan pesanan belum lunas, panggil pelayan (call bell) dengan cooldown persisten,
 *            serta form penilaian ulasan rating & testimoni.
 */

(function () {
    'use strict';

    const config = window.TrackingConfig || {};
    const orderToken = config.orderToken || '';
    const pesananId = config.pesananId || 0;
    const trackingMejaId = config.trackingMejaId || 0;
    const orderLabel = config.orderLabel || 'Pesanan';
    const orderType = config.orderType || 'dine_in';
    const csrfToken = config.csrfToken || '';

    // Helper CSRF
    function getCsrfToken() {
        return csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    // --- 1. SINKRONISASI MULTI-ORDER DI LOCALSTORAGE ---
    window.syncMultiOrders = function () {
        const serverOrders = config.serverOrders || [];

        // 1. Sinkronisasi mutlak dari database server
        // Jika server memberikan daftar pesanan aktif, database adalah Single Source of Truth.
        // Bersihkan LocalStorage dari pesanan-pesanan lama yang sudah dihapus di database.
        let orders = [];
        if (Array.isArray(serverOrders) && serverOrders.length > 0) {
            orders = serverOrders;
            localStorage.setItem('active_guest_orders', JSON.stringify(orders));
            localStorage.setItem('active_guest_order', JSON.stringify(orders[0]));
        } else {
            try {
                const raw = localStorage.getItem('active_guest_orders');
                if (raw) orders = JSON.parse(raw);
                if (!Array.isArray(orders)) orders = [];
            } catch (e) { orders = []; }
        }

        // 2. Sinkronisasi status order saat ini
        const currentStat = config.orderStatus || '';
        if (currentStat === 'cancelled' || currentStat === 'void') {
            orders = orders.filter(o => o.token !== orderToken);
            localStorage.setItem('active_guest_orders', JSON.stringify(orders));
            if (orders.length > 0) {
                localStorage.setItem('active_guest_order', JSON.stringify(orders[0]));
            } else {
                localStorage.removeItem('active_guest_order');
            }
        } else if (orders.length === 0 && orderToken) {
            // Fallback jika serverOrders kosong
            const currentObj = {
                token: orderToken,
                id: pesananId,
                label: orderLabel,
                type: orderType,
                status: currentStat,
                time: Date.now()
            };
            orders = [currentObj];
            localStorage.setItem('active_guest_orders', JSON.stringify(orders));
            localStorage.setItem('active_guest_order', JSON.stringify(currentObj));
        }

        // Render Switcher Bar jika ada lebih dari 1 pesanan aktif
        const switcherBar = document.getElementById('multi-order-switcher-bar');
        const countBadge = document.getElementById('multi-order-count-badge');
        const pillsContainer = document.getElementById('multi-order-pills-container');

        if (!switcherBar || !pillsContainer) return;

        if (orders.length > 1) {
            switcherBar.style.display = 'block';
            if (countBadge) countBadge.innerText = orders.length + ' Pesanan';
            pillsContainer.innerHTML = '';

            orders.forEach(ord => {
                const isCurrent = (ord.token === orderToken);
                const pill = document.createElement('a');
                pill.href = '/tracking/' + ord.token;
                pill.className = 'btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1 btn-touch ' +
                    (isCurrent ? 'text-white' : 'btn-outline-secondary text-light');
                pill.style.fontSize = '0.78rem';
                if (isCurrent) {
                    pill.style.background = 'var(--gradient-bronze)';
                    pill.style.border = 'none';
                    pill.innerHTML = `<i class="bi bi-check-circle-fill"></i> Order #${ord.id} (${ord.label || 'Aktif'}) <span class="badge bg-dark bg-opacity-50 ms-1">Sedang Dilihat</span>`;
                } else {
                    pill.innerHTML = `<i class="bi bi-receipt"></i> Order #${ord.id} (${ord.label || 'Antrean'}) <i class="bi bi-arrow-right-short"></i>`;
                }
                pillsContainer.appendChild(pill);
            });
        } else {
            switcherBar.style.display = 'none';
        }
    };

    // --- 2. SELESAIKAN SESI PESANAN & KEMBALI KE MENU ---
    window.finishCustomerSession = function () {
        try {
            // Bersihkan sesi pesanan saat konsumen selesai makan/berkunjung
            localStorage.removeItem('active_guest_orders');
            localStorage.removeItem('active_guest_order');
        } catch (e) { }

        window.location.href = config.menuUrl || '/';
    };

    // --- 3. PEMBATALAN PESANAN (JIKA BELUM DIBAYAR) ---
    window.cancelCurrentOrder = function () {
        if (!confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Pesanan yang belum dibayar akan dihapus dan Anda dapat memilih menu baru.')) {
            return;
        }

        fetch(config.cancelUrl || (`/konsumen/order/${pesananId}/cancel`), {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json"
            },
            body: JSON.stringify({ token: orderToken })
        })
            .then(res => res.json())
            .then(data => {
                try {
                    let orders = [];
                    const raw = localStorage.getItem('active_guest_orders');
                    if (raw) orders = JSON.parse(raw);
                    if (Array.isArray(orders)) {
                        orders = orders.filter(o => o.token !== orderToken);
                        localStorage.setItem('active_guest_orders', JSON.stringify(orders));
                        if (orders.length > 0) {
                            localStorage.setItem('active_guest_order', JSON.stringify(orders[0]));
                        } else {
                            localStorage.removeItem('active_guest_order');
                        }
                    } else {
                        localStorage.removeItem('active_guest_order');
                    }
                } catch (e) { }

                alert(data.message || 'Pesanan berhasil dibatalkan.');
                window.location.href = config.menuUrl || '/';
            })
            .catch(err => {
                alert('Terjadi kesalahan saat membatalkan pesanan.');
            });
    };

    // --- 4. PANGGIL PELAYAN (CALL BELL) DENGAN COOLDOWN ---
    window.callWaiter = function (mejaId) {
        const btn = document.getElementById('btnCallBell');
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memanggil...';

        fetch(config.callBellUrl || '/call-bell', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json"
            },
            body: JSON.stringify({ id_meja: mejaId })
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    startCooldown(mejaId, 60);
                } else {
                    alert('🔔 ' + (data.message || 'Pelayan telah dipanggil dan segera menuju ke meja Anda.'));
                    startCooldown(mejaId, 120);
                }
            })
            .catch(err => {
                alert('Gagal memanggil pelayan. Silakan panggil pelayan di dekat meja.');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Panggil Pelayan';
            });
    };

    function startCooldown(mejaId, seconds) {
        const expireTime = Date.now() + (seconds * 1000);
        try {
            localStorage.setItem('call_bell_expire_' + mejaId, expireTime);
        } catch (e) { }
        checkCallBellCooldown(mejaId);
    }

    function checkCallBellCooldown(mejaId) {
        if (!mejaId) return;
        const btn = document.getElementById('btnCallBell');
        if (!btn) return;

        try {
            const expire = localStorage.getItem('call_bell_expire_' + mejaId);
            if (expire) {
                const remaining = Math.ceil((parseInt(expire) - Date.now()) / 1000);
                if (remaining > 0) {
                    btn.disabled = true;
                    btn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Tunggu (${remaining}s)`;

                    if (window.bellTimerInterval) clearInterval(window.bellTimerInterval);
                    window.bellTimerInterval = setInterval(() => {
                        const rem = Math.ceil((parseInt(expire) - Date.now()) / 1000);
                        if (rem <= 0) {
                            clearInterval(window.bellTimerInterval);
                            localStorage.removeItem('call_bell_expire_' + mejaId);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Panggil Pelayan';
                        } else {
                            btn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Tunggu (${rem}s)`;
                        }
                    }, 1000);
                } else {
                    localStorage.removeItem('call_bell_expire_' + mejaId);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Panggil Pelayan';
                }
            }
        } catch (e) { }
    }

    // --- 5. RATING BINTANG & ULASAN KONSUMEN ---
    window.setRating = function (val) {
        const ratingVal = document.getElementById('ratingValue');
        if (ratingVal) ratingVal.value = val;

        document.querySelectorAll('.rating-star').forEach(star => {
            const sVal = parseInt(star.getAttribute('data-val'));
            if (sVal <= val) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    };

    window.submitRating = function (e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitRating');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
        }

        const payload = {
            id_pesanan: pesananId,
            order_token: orderToken,
            rating: document.getElementById('ratingValue')?.value || 5,
            komentar: document.getElementById('ratingComment')?.value || ''
        };

        fetch(config.ratingUrl || '/rating/store', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken(),
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('rating-container');
                if (container) {
                    container.innerHTML = `
                        <div class="p-3 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                            <i class="bi bi-check-circle-fill text-success fs-2 mb-2 d-block"></i>
                            <h6 class="text-white fw-bold">Ulasan Terkirim!</h6>
                            <p class="text-secondary small mb-2">${data.message || 'Terima kasih banyak atas feedback Anda untuk Master Cafe.'}</p>
                            <button onclick="finishCustomerSession()" class="btn btn-sm btn-success rounded-pill px-4 fw-bold mt-2">
                                <i class="bi bi-check2-circle me-1"></i> Selesai & Kembali ke Menu
                            </button>
                        </div>`;
                }
            })
            .catch(err => {
                alert('Terjadi kesalahan saat mengirim ulasan.');
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'Kirim Ulasan <i class="bi bi-send-fill ms-1"></i>';
                }
            });
    };

    // --- 6. REALTIME STATUS SYNC (POLLING 2.5s + WEBSOCKET) ---
    let currentStatus = config.orderStatus || '';
    let currentPayStatus = config.paymentStatus || 'unpaid';

    function updateStatusUI(data) {
        if (data.status === 'cancelled' || data.status === 'void') {
            try {
                localStorage.removeItem('active_guest_order');
                localStorage.removeItem('master_cafe_guest_name');
                localStorage.removeItem('master_cafe_guest_phone');
            } catch (e) { }
            window.location.href = config.menuUrl || '/';
            return;
        }

        if (data.status !== currentStatus || data.payment_status !== currentPayStatus) {
            currentStatus = data.status;
            currentPayStatus = data.payment_status;
            window.location.reload();
        }
    }

    function pollOrderStatus() {
        if (!config.statusApiUrl) return;
        fetch(config.statusApiUrl + (config.statusApiUrl.includes('?') ? '&' : '?') + '_t=' + Date.now(), {
            cache: 'no-store',
            headers: { 'Accept': 'application/json' }
        })
            .then(res => res.json())
            .then(data => updateStatusUI(data))
            .catch(err => console.log('Polling sync error:', err));
    }

    // Fast real-time polling (2.5 detik)
    setInterval(pollOrderStatus, 2500);

    // Periksa status saat tab kembali aktif
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            pollOrderStatus();
        }
    });

    // Lifecycle On Ready
    document.addEventListener('DOMContentLoaded', () => {
        if (trackingMejaId) {
            checkCallBellCooldown(trackingMejaId);
        }

        window.syncMultiOrders();

        if (window.Echo) {
            window.Echo.channel('kasir-notifications')
                .listen('.PesananBaru', (e) => {
                    if (e.id == pesananId) pollOrderStatus();
                })
                .listen('.MejaStatusUpdated', () => {
                    pollOrderStatus();
                });
        }
    });

    if (trackingMejaId) {
        checkCallBellCooldown(trackingMejaId);
    }

    console.info('[Consumer Tracking] Tracking engine initialized.');

})();
