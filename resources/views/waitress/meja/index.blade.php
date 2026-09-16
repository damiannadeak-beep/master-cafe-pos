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
        @include('waitress.meja.grid')
    </div>
    <!-- Modal Pindah Meja -->
    <div class="modal fade" id="modalPindahMeja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-white" style="background-color: #1a1e24; border: 1px solid #2d333b; border-radius: 1rem;">
                <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                    <h5 class="modal-title fw-bold text-warning d-flex align-items-center">
                        <i class="bi bi-arrow-left-right me-2"></i> Pindah Meja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPindahMeja" onsubmit="submitPindahMeja(event)">
                    <div class="modal-body py-4">
                        <input type="hidden" id="pindah_from_meja_id" name="from_meja_id">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Meja Asal</label>
                            <input type="text" id="pindah_from_meja_nama" class="form-control text-white bg-dark border-secondary" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Pilih Meja Tujuan <span class="text-danger">*</span></label>
                            <select id="pindah_to_meja_id" name="to_meja_id" class="form-select text-white bg-dark border-secondary" required>
                                <option value="">-- Pilih Meja Kosong / Tujuan --</option>
                                @foreach($mejas as $m)
                                    <option value="{{ $m->id }}">
                                        {{ $m->nama_meja_atau_nomor }} {{ $m->is_available ? '(Tersedia)' : '(Ada Pesanan)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-white-50 small mb-0">
                            <i class="bi bi-info-circle me-1 text-info"></i> Seluruh pesanan aktif di meja ini akan dipindahkan ke meja tujuan.
                        </p>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold" id="btnSubmitPindah">
                            Konfirmasi Pindah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Gabung Meja -->
    <div class="modal fade" id="modalGabungMeja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-white" style="background-color: #1a1e24; border: 1px solid #2d333b; border-radius: 1rem;">
                <div class="modal-header border-bottom border-secondary border-opacity-25 pb-3">
                    <h5 class="modal-title fw-bold text-info d-flex align-items-center">
                        <i class="bi bi-intersect me-2"></i> Gabung Meja
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formGabungMeja" onsubmit="submitGabungMeja(event)">
                    <div class="modal-body py-4">
                        <input type="hidden" id="gabung_source_meja_id" name="source_meja_id">
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Meja yang Digabung (Asal)</label>
                            <input type="text" id="gabung_source_meja_nama" class="form-control text-white bg-dark border-secondary" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-white-50 small">Gabungkan ke Meja Induk (Tujuan) <span class="text-danger">*</span></label>
                            <select id="gabung_target_meja_id" name="target_meja_id" class="form-select text-white bg-dark border-secondary" required>
                                <option value="">-- Pilih Meja Induk --</option>
                                @foreach($mejas as $m)
                                    <option value="{{ $m->id }}">
                                        {{ $m->nama_meja_atau_nomor }} {{ $m->is_available ? '(Tersedia)' : '(Ada Pesanan)' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="text-white-50 small mb-0">
                            <i class="bi bi-exclamation-triangle me-1 text-warning"></i> Pesanan dari meja ini akan disatukan dengan pesanan di meja induk.
                        </p>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25 pt-3">
                        <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-bold" id="btnSubmitGabung">
                            Konfirmasi Gabung
                        </button>
                    </div>
                </form>
            </div>
        </div>
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
        .then(res => res.text())
        .then(html => {
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

    // Modal Pindah & Gabung Meja Handlers
    window.openModalPindahMeja = function(id, nama) {
        document.getElementById('pindah_from_meja_id').value = id;
        document.getElementById('pindah_from_meja_nama').value = nama;
        const select = document.getElementById('pindah_to_meja_id');
        select.value = '';
        Array.from(select.options).forEach(opt => {
            opt.disabled = (opt.value == id);
        });
        new bootstrap.Modal(document.getElementById('modalPindahMeja')).show();
    };

    window.openModalGabungMeja = function(id, nama) {
        document.getElementById('gabung_source_meja_id').value = id;
        document.getElementById('gabung_source_meja_nama').value = nama;
        const select = document.getElementById('gabung_target_meja_id');
        select.value = '';
        Array.from(select.options).forEach(opt => {
            opt.disabled = (opt.value == id);
        });
        new bootstrap.Modal(document.getElementById('modalGabungMeja')).show();
    };

    window.submitPindahMeja = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitPindah');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memindahkan...';

        const fromId = document.getElementById('pindah_from_meja_id').value;
        const toId = document.getElementById('pindah_to_meja_id').value;

        fetch('{{ route("kasir.meja.pindah") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ from_meja_id: fromId, to_meja_id: toId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const modalEl = document.getElementById('modalPindahMeja');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
                } else {
                    alert(data.message);
                }
                window.reloadMejaGrid();
            } else {
                alert(data.message || 'Gagal memindahkan meja.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Konfirmasi Pindah';
        });
    };

    window.submitGabungMeja = function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitGabung');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menggabungkan...';

        const sourceId = document.getElementById('gabung_source_meja_id').value;
        const targetId = document.getElementById('gabung_target_meja_id').value;

        fetch('{{ route("kasir.meja.gabung") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ source_meja_id: sourceId, target_meja_id: targetId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                const modalEl = document.getElementById('modalGabungMeja');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1800, showConfirmButton: false });
                } else {
                    alert(data.message);
                }
                window.reloadMejaGrid();
            } else {
                alert(data.message || 'Gagal menggabungkan meja.');
            }
        })
        .catch(err => {
            alert('Terjadi kesalahan jaringan.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Konfirmasi Gabung';
        });
    };

    // 1. Integrasi WebSocket Instan (Reverb / Echo)
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

    // 3. Fallback Heartbeat santai (15 detik)
    setInterval(() => {
        if (!document.hidden && !isReloadingMeja) {
            window.reloadMejaGrid(true);
        }
    }, 15000);
</script>
@endsection
