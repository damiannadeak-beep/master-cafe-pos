<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir POS - Master Cafe</title>
    @include("layouts.includes.head-assets")
    <style>
        .kasir-layout { height: 100vh; display: flex; flex-direction: column; margin: 0; padding: 0; overflow: hidden; }
        .kasir-main { flex: 1; display: flex; flex-direction: column; background: var(--bg-base); overflow: hidden; padding: 1.5rem; min-height: 0; }
        .kasir-container { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; max-width: 1360px; margin: 0 auto; padding: 0 0.5rem 2rem 0.5rem; width: 100%; }
        .kasir-navbar { background: var(--gradient-surface) !important; border-bottom: 1px solid var(--border-subtle); position: relative; z-index: 1050; box-shadow: 0 4px 20px rgba(45, 26, 17, 0.25); flex-shrink: 0; }
        .kasir-navbar .navbar-brand { color: #f0e9dd; font-weight: 700; font-size: 1.25rem; letter-spacing: 0.02em; }
        .kasir-navbar .navbar-text, .kasir-navbar .nav-link { color: #f0e9dd !important; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
        .kasir-navbar .nav-link.active { color: #c08e5c !important; font-weight: 700; }
        .kasir-navbar .nav-link:hover { color: #ffffff !important; transform: translateY(-1px); }
        .offcanvas.offcanvas-start { width: 320px; background-color: #0e1217; border-right: 1px solid #21262d; }
        
        /* Custom Scrollbar for inner containers */
        .kasir-container::-webkit-scrollbar { width: 8px; }
        .kasir-container::-webkit-scrollbar-track { background: transparent; }
        .kasir-container::-webkit-scrollbar-thumb { background: #21262d; border-radius: 4px; }
        .kasir-container::-webkit-scrollbar-thumb:hover { background: rgba(152, 108, 67, 0.5); }
    </style>
</head>
<body>
    <div id="app" class="kasir-layout">
        @include("layouts.includes.kasir-topbar")

        <main class="kasir-main">
            <div class="kasir-container">
                @yield('content')
            </div>
        </main>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script>
        // --- Utility: Fetch Active Orders Count Badge & Smart Auto-Sync ---
        let lastKnownOrderId = null;
        let lastKnownCount = null;
        let isInitialSync = true;
        let lastNotifiedOrderId = null;

        function triggerNewOrderNotification(orderId, message) {
            if (orderId && lastNotifiedOrderId === orderId) {
                return; // Hindari duplikasi jika WebSocket dan Sync mendeteksi bersamaan
            }
            if (orderId) {
                lastNotifiedOrderId = orderId;
            }

            // 1. Play crisp counter bell "Ting" INSTANTLY
            if (window.playDingSound) {
                window.playDingSound();
            }

            // 2. Toast alert INSTANTLY
            if (window.showToast) {
                window.showToast(message || 'Pesanan baru masuk!', 'success');
            }

            // 3. Auto-reload daftar pesanan aktif
            if (window.location.pathname.includes('pesanan-aktif')) {
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }

        function fetchActiveOrdersCount() {
            fetch('{{ route("kasir.active_orders_count") }}?_t=' + Date.now(), {
                headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
            })
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('badge-active-orders');
                    if (data.count > 0) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }

                    const currentLatestId = parseInt(data.latest_id) || 0;
                    const currentCount = parseInt(data.count) || 0;

                    if (!isInitialSync) {
                        // Deteksi jika ada pesanan baru: ID lebih tinggi ATAU jumlah pesanan bertambah
                        const hasNewOrder = (currentLatestId > 0 && lastKnownOrderId > 0 && currentLatestId > lastKnownOrderId) ||
                                           (lastKnownCount !== null && currentCount > lastKnownCount);

                        if (hasNewOrder) {
                            console.log('[Sync] Pesanan baru terdeteksi! ID:', currentLatestId, 'Count:', currentCount);
                            triggerNewOrderNotification(currentLatestId, 'Pesanan baru masuk!');
                        }
                    }

                    lastKnownOrderId = currentLatestId;
                    lastKnownCount = currentCount;
                    isInitialSync = false;
                })
                .catch(err => console.error(err));
        }

        // --- Dark Mode Logic ---
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
    </script>

    <!-- Local Audio Element (Honors Chrome Site Settings 'Sound: Allow') -->
    <audio id="kasirBellAudio" src="{{ asset('sounds/bell.wav') }}" preload="auto"></audio>

    <script>
        // --- High-Reliability Local Bell Audio Player ---
        const bellAudio = document.getElementById('kasirBellAudio');
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

        // Auto-unlock audio on any user interaction
        function primeAudio() {
            if (bellAudio) {
                bellAudio.load();
            }
            getAudioContext();
        }
        ['click', 'pointerdown', 'keydown', 'touchstart'].forEach(evt => {
            document.addEventListener(evt, primeAudio, { once: false, passive: true });
        });

        // Web Audio Synthesizer Fallback
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

        // Primary Ding Player (Plays local bell.wav, fallback to Web Audio)
        window.playDingSound = function(isTest = false) {
            let played = false;
            try {
                if (bellAudio) {
                    bellAudio.currentTime = 0;
                    const p = bellAudio.play();
                    if (p !== undefined) {
                        p.then(() => {
                            played = true;
                        }).catch(err => {
                            console.warn('[Audio] HTML5 play error, trying WebAudio:', err);
                            playWebAudioChime();
                        });
                    } else {
                        played = true;
                    }
                } else {
                    playWebAudioChime();
                }
            } catch (e) {
                console.warn('[Audio] General error, trying WebAudio:', e);
                playWebAudioChime();
            }

            if (isTest && window.showToast) {
                window.showToast('🔔 Bunyi "Ting" berhasil diputar!', 'success');
            }
        };

        // --- Real-Time Hybrid Engine (Laravel Echo / Reverb + Smart Auto-Sync) ---
        document.addEventListener('DOMContentLoaded', () => {
            fetchActiveOrdersCount();

            // Default sync interval: 1 second for ultra-fast responsiveness without reload lag
            let syncInterval = setInterval(fetchActiveOrdersCount, 1000);

            // Wait for Vite modules (Echo) to load
            setTimeout(() => {
                if (window.Echo) {
                    console.log('[WebSocket] Subscribing to kasir-notifications channel...');
                    const kasirChannel = window.Echo.channel('kasir-notifications');

                    const handlePesananBaru = (e) => {
                        console.log('[WebSocket] Pesanan baru diterima via Reverb:', e);
                        fetchActiveOrdersCount();
                        triggerNewOrderNotification(e.id, e.message || 'Pesanan baru masuk!');
                    };

                    const handleMejaStatus = (e) => {
                        console.log('[WebSocket] Status meja diupdate:', e);
                        window.dispatchEvent(new CustomEvent('meja-status-updated', { detail: e }));
                    };

                    kasirChannel
                        .listen('.PesananBaru', handlePesananBaru)
                        .listen('PesananBaru', handlePesananBaru)
                        .listen('.MejaStatusUpdated', handleMejaStatus)
                        .listen('MejaStatusUpdated', handleMejaStatus);

                    // Dynamic sync optimization: slow down polling when WebSocket is connected
                    if (window.Echo.connector && window.Echo.connector.pusher) {
                        const pusherConn = window.Echo.connector.pusher.connection;
                        pusherConn.bind('connected', () => {
                            console.log('[WebSocket] Terhubung secara real-time! Mengurangi frekuensi background polling ke 30s.');
                            clearInterval(syncInterval);
                            syncInterval = setInterval(fetchActiveOrdersCount, 30000);
                        });
                        pusherConn.bind('unavailable', () => {
                            console.warn('[WebSocket] Tidak tersedia, beralih ke smart auto-sync 4s.');
                            clearInterval(syncInterval);
                            syncInterval = setInterval(fetchActiveOrdersCount, 4000);
                        });
                        pusherConn.bind('failed', () => {
                            console.warn('[WebSocket] Gagal koneksi, beralih ke smart auto-sync 4s.');
                            clearInterval(syncInterval);
                            syncInterval = setInterval(fetchActiveOrdersCount, 4000);
                        });
                        pusherConn.bind('disconnected', () => {
                            clearInterval(syncInterval);
                            syncInterval = setInterval(fetchActiveOrdersCount, 4000);
                        });
                    }
                } else {
                    console.warn('[WebSocket] Laravel Echo is not loaded. Using smart auto-sync 4s.');
                }
            }, 1000);
        });
    </script>
    
    @include('components.webpush')

    <!-- Global UI/UX Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toast Notification System
            window.showToast = function(message, type = 'success') {
                const toastContainer = document.getElementById('toast-container') || (function() {
                    const div = document.createElement('div');
                    div.id = 'toast-container';
                    div.className = 'toast-container position-fixed bottom-0 end-0 p-3 z-modal';
                    document.body.appendChild(div);
                    return div;
                })();

                const toastId = 'toast-' + Date.now();
                const iconCls = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
                const borderClr = type === 'success' ? '#986c43' : '#dc3545';
                
                const toastHtml = '<div id="' + toastId + '" class="toast toast-bronze align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-left: 4px solid ' + borderClr + ' !important; background-color: #161b22; color: #fff;"><div class="d-flex"><div class="toast-body d-flex align-items-center"><i class="bi ' + iconCls + ' me-2" style="font-size: 20px; color: ' + borderClr + ';"></i><span style="font-size: 16px;">' + message + '</span></div><button type="button" class="btn-close btn-close-white me-2 m-auto btn-touch" data-bs-dismiss="toast" aria-label="Close"></button></div></div>';
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });
            };
            
            // Override native alert
            window.nativeAlert = window.alert;
            window.alert = function(msg) {
                window.showToast(msg, 'warning');
            };
        });
    </script>
</body>
</html>