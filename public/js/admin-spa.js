/**
 * Admin Panel Turbo/SPA Instant Navigation Router
 * Menyediakan navigasi tanpa reload (smooth 60fps), instan prefetch saat hover,
 * dan animasi transisi halus tanpa flicker.
 */
(function() {
    'use strict';

    const pageCache = new Map();
    const CACHE_TTL = 60 * 1000; // 60 detik
    let isNavigating = false;

    // Helper: Validasi apakah URL adalah route internal admin yang aman untuk SPA
    function isEligibleAdminLink(anchor) {
        if (!anchor || anchor.tagName !== 'A') return false;
        const href = anchor.getAttribute('href');
        if (!href || href === '#' || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return false;
        if (anchor.target === '_blank' || anchor.hasAttribute('download') || anchor.hasAttribute('data-no-spa')) return false;

        try {
            const url = new URL(anchor.href, window.location.origin);
            if (url.origin !== window.location.origin) return false;
            // Hanya tangani route /admin/
            if (!url.pathname.startsWith('/admin')) return false;
            // Kecualikan route unduhan biner/ekspor file
            const bypassPaths = ['/export', '/pdf', '/csv', '/excel', '/download', '/backup'];
            for (const bp of bypassPaths) {
                if (url.pathname.includes(bp)) return false;
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    // Prefetch halaman ke memori
    function prefetchUrl(url) {
        if (!url || pageCache.has(url)) {
            const cached = pageCache.get(url);
            if (cached && (Date.now() - cached.time < CACHE_TTL)) return;
        }

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-Admin-SPA': '1'
            }
        })
        .then(response => {
            if (response.ok) return response.text();
            throw new Error('Prefetch failed');
        })
        .then(html => {
            pageCache.set(url, { html, time: Date.now() });
        })
        .catch(() => {
            // Fail silently on prefetch error
        });
    }

    // Progress bar controller
    const ProgressBar = {
        bar: null,
        timer: null,
        init() {
            this.bar = document.getElementById('adminProgressBar');
        },
        start() {
            if (!this.bar) return;
            clearTimeout(this.timer);
            // Hanya tampilkan jika proses memakan waktu > 70ms
            this.timer = setTimeout(() => {
                if (this.bar) {
                    this.bar.style.transition = 'width 0.2s ease, opacity 0.15s ease';
                    this.bar.style.opacity = '1';
                    this.bar.style.width = '65%';
                }
            }, 70);
        },
        done() {
            clearTimeout(this.timer);
            if (!this.bar) return;
            this.bar.style.width = '100%';
            setTimeout(() => {
                if (this.bar) {
                    this.bar.style.opacity = '0';
                    setTimeout(() => {
                        if (this.bar) {
                            this.bar.style.transition = 'none';
                            this.bar.style.width = '0%';
                        }
                    }, 200);
                }
            }, 120);
        }
    };

    // Eksekusi skrip dalam konten yang baru dimasukkan secara aman
    function executeScripts(container) {
        const scripts = container.querySelectorAll('script');
        scripts.forEach(oldScript => {
            // Hindari memuat ulang Chart.js jika sudah aktif di window
            if (oldScript.src && oldScript.src.includes('chart.js') && typeof Chart !== 'undefined') {
                oldScript.remove();
                return;
            }

            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });

            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                // Bungkus dalam IIFE try-catch agar variabel const/let tidak bertabrakan dengan navigasi sebelumnya
                newScript.textContent = '(function(){\ntry {\n' + oldScript.textContent + '\n} catch(err) { console.warn("[Admin SPA Script]", err); }\n})();';
            }
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    // Update active nav-link di sidebar
    function updateActiveSidebar(currentUrl) {
        const currentPath = new URL(currentUrl, window.location.origin).pathname;
        document.querySelectorAll('.admin-sidebar a.nav-link').forEach(link => {
            const linkPath = new URL(link.href, window.location.origin).pathname;
            if (linkPath === currentPath || (linkPath !== '/admin/dashboard' && currentPath.startsWith(linkPath))) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    // Swapping halaman dengan transisi semut (smooth cross-fade)
    async function navigateTo(url, pushState = true) {
        if (isNavigating) return;
        isNavigating = true;
        ProgressBar.start();

        const contentContainer = document.querySelector('.admin-content');
        let html = null;

        // Cek cache
        const cached = pageCache.get(url);
        if (cached && (Date.now() - cached.time < CACHE_TTL)) {
            html = cached.html;
        } else {
            try {
                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Admin-SPA': '1'
                    }
                });

                // Jika server merespons redirect (misal auth expired / error), fallback ke normal navigation
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }

                if (!res.ok) {
                    throw new Error('HTTP ' + res.status);
                }

                html = await res.text();
                pageCache.set(url, { html, time: Date.now() });
            } catch (err) {
                console.warn('[AdminSPA] Fetch error, falling back to full navigation:', err);
                ProgressBar.done();
                isNavigating = false;
                window.location.href = url;
                return;
            }
        }

        // Parse HTML yang diterima
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newTitle = doc.querySelector('title')?.innerText || document.title;
        const newContent = doc.querySelector('.admin-content');

        if (!newContent || !contentContainer) {
            // Jika format response tidak memiliki .admin-content, fallback ke full reload
            window.location.href = url;
            return;
        }

        // Micro-animasi keluar (sangat cepat 60ms)
        contentContainer.style.transition = 'opacity 0.08s ease-out, transform 0.08s ease-out';
        contentContainer.style.opacity = '0.7';
        contentContainer.style.transform = 'translateY(3px)';

        setTimeout(() => {
            // Update Title & URL
            document.title = newTitle;
            if (pushState) {
                history.pushState({ url }, newTitle, url);
            }

            // Ganti konten
            contentContainer.innerHTML = newContent.innerHTML;
            contentContainer.scrollTop = 0;

            // Update status link aktif di sidebar
            updateActiveSidebar(url);

            // Micro-animasi masuk (smooth 100ms)
            contentContainer.style.opacity = '1';
            contentContainer.style.transform = 'translateY(0)';

            // Eksekusi skrip bawaan konten baru
            executeScripts(contentContainer);

            // Re-init Bootstrap tooltips/popovers jika ada
            if (window.bootstrap) {
                const tooltipTriggerList = [].slice.call(contentContainer.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
            }

            // Dispatch global event
            window.dispatchEvent(new CustomEvent('admin:page-loaded', { detail: { url } }));

            ProgressBar.done();
            isNavigating = false;
        }, 80);
    }

    // Inisialisasi event listener navigasi & hover prefetching
    function initSpaNavigation() {
        ProgressBar.init();

        // 1. Delegasi Hover & Touchstart untuk Instan Prefetching
        document.body.addEventListener('pointerenter', function(e) {
            const link = e.target.closest('a');
            if (link && isEligibleAdminLink(link)) {
                prefetchUrl(link.href);
            }
        }, { capture: true, passive: true });

        document.body.addEventListener('touchstart', function(e) {
            const link = e.target.closest('a');
            if (link && isEligibleAdminLink(link)) {
                prefetchUrl(link.href);
            }
        }, { capture: true, passive: true });

        // 2. Delegasi Click untuk Transisi Mulus Tanpa Reload
        document.body.addEventListener('click', function(e) {
            // Jangan cegah jika klik dengan tombol Ctrl / Cmd / Shift / Scroll wheel
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;

            const link = e.target.closest('a');
            if (link && isEligibleAdminLink(link)) {
                e.preventDefault();
                const targetUrl = link.href;

                // Tutup sidebar di tampilan mobile jika terbuka
                const sidebar = document.getElementById('adminSidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                    if (overlay) overlay.classList.remove('show');
                }

                // Jika klik link yang sama persis, abaikan
                if (window.location.href === targetUrl) return;

                navigateTo(targetUrl, true);
            }
        });

        // 3. Tangani Tombol Back / Forward di Browser
        window.addEventListener('popstate', function(e) {
            if (isEligibleAdminLink({ href: window.location.href, tagName: 'A', getAttribute: () => window.location.href })) {
                navigateTo(window.location.href, false);
            } else {
                window.location.reload();
            }
        });

        // Prefetch seluruh menu sidebar utama saat admin pertama kali membuka dashboard
        setTimeout(() => {
            document.querySelectorAll('.admin-sidebar a.nav-link').forEach(link => {
                if (isEligibleAdminLink(link)) {
                    prefetchUrl(link.href);
                }
            });
        }, 1200);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSpaNavigation);
    } else {
        initSpaNavigation();
    }
})();
