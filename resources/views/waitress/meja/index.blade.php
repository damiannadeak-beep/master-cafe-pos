@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="mb-0 fw-bold text-accent"><i class="bi bi-grid-3x3-gap-fill me-2"></i>Live Monitor Meja</h4>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 small d-inline-flex align-items-center">
                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Realtime Auto-Sync
                </span>
            </div>
            <p class="text-white-50 mb-0">Pantau aktivitas pesanan dan tujuan pengantaran makanan per meja secara realtime</p>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('kasir.pesanan_aktif') }}" class="btn rounded-pill px-3 fw-bold d-inline-flex align-items-center justify-content-center btn-touch shadow-sm" style="height: 40px; min-height: 40px; font-size: 0.875rem; background: var(--gradient-bronze, linear-gradient(135deg, #986c43 0%, #c08e5c 100%)); color: #ffffff; border: 1px solid rgba(192, 142, 92, 0.6); box-shadow: 0 2px 10px rgba(192, 142, 92, 0.25); transition: all 0.2s ease;">
                <i class="bi bi-receipt me-2" style="font-size: 1.1rem;"></i> Buka Pesanan Aktif
            </a>
        </div>
    </div>

    <!-- Container Dinamis yang Di-update Secara Real-Time Tanpa Reload Halaman -->
    <div id="meja-monitor-container">
        @include('kasir.meja.grid')
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
    }
    .hover-text-white:hover {
        color: #fff !important;
    }
    @keyframes spinAnimation {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .spin-animation {
        display: inline-block;
        animation: spinAnimation 0.8s linear infinite;
    }
</style>

<script>
    let isReloadingMeja = false;

    // --- Dynamic Meja Grid Loader (Real-Time Tanpa Reload Halaman) ---
    window.reloadMejaGrid = function(silent = false) {
        if (isReloadingMeja) return;
        const container = document.getElementById('meja-monitor-container');
        if (!container) return;

        isReloadingMeja = true;
        const refreshBtn = document.getElementById('btn-refresh-meja');
        if (refreshBtn && !silent) {
            const icon = refreshBtn.querySelector('i');
            if (icon) icon.classList.add('spin-animation');
            refreshBtn.disabled = true;
        }

        fetch('{{ route("kasir.meja.index") }}?grid_only=1&_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml',
                'Cache-Control': 'no-cache'
            }
        })
        .then(html => {
            // Smooth DOM update: only replace if the markup actually changed to avoid layout reflows
            if (container.innerHTML !== html) {
                container.innerHTML = html;
            }
        })
        .catch(err => {
            console.error('[MonitorMeja] Gagal memperbarui status meja:', err);
        })
        .finally(() => {
            isReloadingMeja = false;
            if (refreshBtn) {
                const icon = refreshBtn.querySelector('i');
                if (icon) icon.classList.remove('spin-animation');
                refreshBtn.disabled = false;
            }
        });
    };

    // 1. Integrasi WebSocket Instan (Reverb / Echo) - Update Instan saat ada event nyata
    window.addEventListener('meja-status-updated', function(e) {
        window.reloadMejaGrid(true);
    });

    window.addEventListener('pesanan-baru', function(e) {
        window.reloadMejaGrid(true);
    });

    if (window.Echo) {
        const channel = window.Echo.channel('kasir-notifications');
        channel
            .listen('.MejaStatusUpdated', () => window.reloadMejaGrid(true))
            .listen('MejaStatusUpdated', () => window.reloadMejaGrid(true))
            .listen('.PesananBaru', () => window.reloadMejaGrid(true))
            .listen('PesananBaru', () => window.reloadMejaGrid(true));
    }

    // 2. Refresh instan saat kasir membuka kembali tab
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !isReloadingMeja) {
            window.reloadMejaGrid(true);
        }
    });

    // 3. Fallback Heartbeat santai (15 detik) untuk memastikan data tetap sinkron tanpa membebani browser
    setInterval(() => {
        if (!document.hidden && !isReloadingMeja) {
            window.reloadMejaGrid(true);
        }
    }, 15000);
</script>
@endsection
