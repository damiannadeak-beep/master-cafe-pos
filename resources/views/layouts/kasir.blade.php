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
        .kasir-main { flex: 1; display: flex; flex-direction: column; background: var(--bg-base); overflow: hidden; padding: 1.5rem; }
        .kasir-container { flex: 1; overflow: hidden; max-width: 1360px; margin: 0 auto; padding: 0; width: 100%; }
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
        function fetchActiveOrdersCount() {
            fetch('{{ route('kasir.active_orders_count') }}')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('badge-active-orders');
                    if (data.count > 0) {
                        badge.innerText = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(err => console.error(err));
        }

        // Dark Mode Logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const icon = darkModeToggle.querySelector('i');
        
        function applyDarkMode(isDark) {
            if (isDark) {
                document.body.classList.add('dark-mode');
                icon.classList.replace('bi-moon-stars', 'bi-sun');
                localStorage.setItem('kasirDarkMode', 'true');
            } else {
                document.body.classList.remove('dark-mode');
                icon.classList.replace('bi-sun', 'bi-moon-stars');
                localStorage.setItem('kasirDarkMode', 'false');
            }
        }

        // Initialize from LocalStorage
        if (localStorage.getItem('kasirDarkMode') === 'true') {
            applyDarkMode(true);
        }

        darkModeToggle.addEventListener('click', () => {
            const isCurrentlyDark = document.body.classList.contains('dark-mode');
            applyDarkMode(!isCurrentlyDark);
        });

        // Setup audio element for notification
        const notifSound = new Audio('https://actions.google.com/sounds/v1/alarms/beep_short.ogg');

        // Fetch Notifications
        function fetchNotifications() {
            fetch('{{ url("/kasir/api/notifications") }}')
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        // Play sound
                        notifSound.play().catch(e => console.log('Autoplay prevented:', e));
                        
                        data.forEach(notif => {
                            // Tampilkan alert dengan delay sedikit agar suara sempat diputar
                            setTimeout(() => {
                                alert('ÃƒÂ°Ã…Â¸Ã¢â‚¬ÂÃ¢â‚¬Â Notifikasi Baru:\n' + notif.message);
                            }, 500);
                            
                            // Tandai sudah dibaca
                            fetch('{{ url("/kasir/api/notifications") }}/' + notif.id + '/read', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            });
                        });
                    }
                })
                .catch(error => console.error('Error fetching notifications:', error));
        }

        // Cek setiap 10 detik
        setInterval(() => {
            fetchActiveOrdersCount();
            fetchNotifications();
        }, 10000);
        // Cek saat pertama load
        document.addEventListener('DOMContentLoaded', () => {
            fetchActiveOrdersCount();
            fetchNotifications();
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
                const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-circle';
                const borderColor = type === 'success' ? '#986c43' : '#dc3545';
                
                const toastHtml = 
                    <div id=" + toastId + " class="toast toast-bronze align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" style="border-left: 4px solid  + borderColor +  !important; background-color: #161b22; color: #fff;">
                        <div class="d-flex">
                            <div class="toast-body d-flex align-items-center">
                                <i class="bi  + icon +  me-2" style="font-size: 20px; color:  + borderColor + ;"></i>
                                <span style="font-size: 16px;"> + message + </span>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto btn-touch" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                ;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
                
                toastElement.addEventListener('hidden.bs.toast', function () {
                    toastElement.remove();
                });
            };
            
            // Override native alert (Optional but useful for catching unmigrated alerts)
            window.nativeAlert = window.alert;
            window.alert = function(msg) {
                window.showToast(msg, 'warning');
            };
        });
    </script>
</body>
</html>












