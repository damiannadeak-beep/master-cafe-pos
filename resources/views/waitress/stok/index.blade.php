@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold text-white mb-0"><i class="bi bi-check2-circle me-2" style="color: #c08e5c;"></i>Status Ketersediaan Menu</h4>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Operasional POS)</span>
            </div>
            <p class="text-secondary small mb-0">Atur ketersediaan menu secara langsung. Menu yang ditandai Habis otomatis tidak dapat dipesan oleh kasir maupun konsumen via QR meja.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-2 px-3 py-2 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25);">
                <i class="bi bi-check-circle-fill"></i> Tersedia: <strong id="count-tersedia" class="ms-1">{{ $menus->where('is_available', true)->count() }}</strong>
            </span>
            <span class="badge rounded-2 px-3 py-2 fw-medium d-inline-flex align-items-center gap-1.5" style="background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25);">
                <i class="bi bi-x-circle-fill"></i> Habis: <strong id="count-habis" class="ms-1">{{ $menus->where('is_available', false)->count() }}</strong>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="card border-0 shadow-sm mb-4" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group pos-search-box">
                        <span class="input-group-text border-end-0 rounded-start-pill ps-3" style="background-color: #0e1217; border-color: #21262d; color: #8b949e;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="searchInput" class="form-control text-white border-start-0 rounded-end-pill pos-search-input pe-3" placeholder="Cari nama menu..." onkeyup="filterMenus()" autocomplete="off" style="background-color: #0e1217; border-color: #21262d;">
                    </div>
                </div>
                <div class="col-12 col-md-7 d-flex justify-content-md-end gap-2 flex-wrap align-items-center">
                    <div class="d-inline-flex p-1 rounded-3 gap-1" style="background-color: #0e1217; border: 1px solid #21262d;">
                        <button type="button" class="btn btn-sm fw-medium px-3 py-1 rounded-2 text-nowrap btn-category-pill active" id="btn-filter-all" onclick="setCategoryFilter('all', this)">Semua</button>
                        <button type="button" class="btn btn-sm fw-medium px-3 py-1 rounded-2 text-nowrap btn-category-pill text-secondary" id="btn-filter-makanan" onclick="setCategoryFilter('makanan', this)">Makanan</button>
                        <button type="button" class="btn btn-sm fw-medium px-3 py-1 rounded-2 text-nowrap btn-category-pill text-secondary" id="btn-filter-minuman" onclick="setCategoryFilter('minuman', this)">Minuman</button>
                    </div>
                    <button type="button" class="btn btn-sm fw-medium d-inline-flex align-items-center gap-1.5 px-3 py-1.5 rounded-2" id="btn-filter-habis" onclick="toggleHabisFilter(this)" style="background-color: #0e1217; border: 1px solid #21262d; color: #8b949e;">
                        <i class="bi bi-filter"></i> Hanya Menu Habis
                    </button>
                </div>
            </div>

            <!-- Sub-Kategori Filter Bar (Level 2) -->
            <div id="stok-sub-category-wrapper" class="w-100 mt-2.5 pt-2 border-top" style="display: none !important; border-color: #21262d !important;">
                <div id="stok-sub-category-pills" class="d-flex gap-2 pb-1 overflow-auto align-items-center" style="white-space: nowrap; flex-wrap: nowrap; -webkit-overflow-scrolling: touch;">
                    <!-- Rendered dynamically by JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Table of Menus -->
    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 68vh; overflow-y: auto;">
                <table class="table table-hover align-middle mb-0" id="menuTable" style="color: #c9d1d9;">
                    <thead class="sticky-top" style="background-color: #161b22; border-bottom: 1px solid #21262d; z-index: 10;">
                        <tr>
                            <th class="ps-4 py-3 text-secondary text-uppercase fw-semibold" style="width: 76px; font-size: 0.72rem; letter-spacing: 0.5px;">Foto</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Nama Menu</th>
                            <th class="text-center py-3 text-secondary text-uppercase fw-semibold" style="width: 160px; font-size: 0.72rem; letter-spacing: 0.5px;">Kategori</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold text-nowrap" style="width: 140px; font-size: 0.72rem; letter-spacing: 0.5px;">Harga</th>
                            <th class="pe-4 text-center py-3 text-secondary text-uppercase fw-semibold" style="width: 220px; font-size: 0.72rem; letter-spacing: 0.5px;">Status Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                            <tr class="menu-row {{ !$menu->is_available ? 'menu-row-habis' : '' }}" 
                                data-name="{{ strtolower($menu->nama_menu) }}" 
                                data-category="{{ strtolower($menu->kategori) }}" 
                                data-subcategory="{{ strtolower($menu->sub_kategori ?? '') }}" 
                                data-available="{{ $menu->is_available ? '1' : '0' }}" 
                                id="row-{{ $menu->id }}"
                                style="border-bottom: 1px solid #21262d; transition: background-color 0.2s ease, opacity 0.2s ease;">
                                <td class="ps-4 py-3">
                                    <div class="rounded-3 d-flex align-items-center justify-content-center overflow-hidden menu-thumb" style="width: 48px; height: 48px; background-color: #0e1217; border: 1px solid #21262d;">
                                        <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="{{ $menu->nama_menu }}" style="object-fit: cover; width: 100%; height: 100%;">
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="fw-semibold text-white fs-6 menu-name-text">{{ $menu->nama_menu }}</span>
                                        <span class="badge rounded-2 fw-medium px-2 py-0.5" id="badge-habis-{{ $menu->id }}" style="background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.7rem; {{ $menu->is_available ? 'display: none !important;' : '' }}">
                                            Habis
                                        </span>
                                    </div>
                                    @if($menu->is_dynamic_price)
                                        <span class="text-secondary small d-inline-block mt-1" style="font-size: 0.72rem;">
                                            <i class="bi bi-tag me-1"></i> Harga Dinamis / Timbangan
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center py-3">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <span class="badge rounded-2 fw-medium px-2.5 py-1" style="background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.72rem;">
                                            {{ ucfirst($menu->kategori) }}
                                        </span>
                                        @if($menu->sub_kategori)
                                            <span class="text-secondary small" style="font-size: 0.72rem;">
                                                {{ $menu->sub_kategori }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-nowrap fw-bold py-3" style="color: #c08e5c;">
                                    {{ ($menu->is_dynamic_price || $menu->harga == 0) ? 'Sesuai Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <!-- Tactile Segmented Control -->
                                    <div class="avail-toggle-wrapper shadow-sm" role="group" id="btn-group-{{ $menu->id }}">
                                        <button type="button" 
                                                class="btn btn-sm btn-touch flex-fill rounded-pill {{ $menu->is_available ? 'btn-avail-active fw-semibold' : 'btn-avail-inactive' }}" 
                                                onclick="setMenuAvailability({{ $menu->id }}, 1)"
                                                id="btn-tersedia-{{ $menu->id }}">
                                            <i class="bi bi-check-circle-fill"></i> Tersedia
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-touch flex-fill rounded-pill {{ !$menu->is_available ? 'btn-habis-active fw-semibold' : 'btn-avail-inactive' }}" 
                                                onclick="setMenuAvailability({{ $menu->id }}, 0)"
                                                id="btn-habis-{{ $menu->id }}">
                                            <i class="bi bi-x-circle-fill"></i> Habis
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.03); border: 1px solid #21262d; color: #8b949e;">
                                            <i class="bi bi-cup-hot fs-3"></i>
                                        </div>
                                        <h6 class="text-white fw-bold mb-1">Belum Ada Menu Terdaftar</h6>
                                        <p class="text-secondary small mb-0">Daftar produk menu belum tersedia pada database.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
/* Category Pills */
.btn-category-pill {
    border: 1px solid transparent !important;
    transition: all 0.18s ease;
}
.btn-category-pill.active {
    background-color: rgba(192, 142, 92, 0.18) !important;
    color: #c08e5c !important;
    border: 1px solid rgba(192, 142, 92, 0.35) !important;
}

/* Tactile Segmented Control */
.avail-toggle-wrapper {
    background-color: #0e1217;
    border: 1px solid #21262d;
    padding: 3px;
    border-radius: 9999px;
    display: inline-flex;
    width: 204px;
    max-width: 100%;
    user-select: none;
}
.avail-toggle-wrapper .btn {
    border: none !important;
    padding: 6px 12px !important;
    font-size: 0.78rem !important;
    transition: all 0.18s cubic-bezier(0.16, 1, 0.3, 1) !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}
.btn-avail-active {
    background: rgba(52, 211, 153, 0.16) !important;
    color: #34d399 !important;
    border: 1px solid rgba(52, 211, 153, 0.35) !important;
    box-shadow: 0 2px 8px rgba(52, 211, 153, 0.15) !important;
}
.btn-habis-active {
    background: rgba(239, 68, 68, 0.16) !important;
    color: #f87171 !important;
    border: 1px solid rgba(239, 68, 68, 0.35) !important;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.15) !important;
}
.btn-avail-inactive {
    background: transparent !important;
    color: #8b949e !important;
    border: 1px solid transparent !important;
}
.btn-avail-inactive:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.05) !important;
}

/* Habis row visual dimming */
.menu-row-habis {
    background-color: rgba(239, 68, 68, 0.015) !important;
}
.menu-row-habis .menu-thumb img {
    filter: grayscale(80%) opacity(70%);
}
.menu-row-habis .menu-name-text {
    color: #94a3b8 !important;
}
</style>

<script>
    @php
        $hasSubKategoriCol = \Illuminate\Support\Facades\Schema::hasColumn('menu', 'sub_kategori');
        $stokMakananSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'makanan')->pluck('sub_kategori')->filter()->unique()->values() : collect();
        $stokMinumanSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'minuman')->pluck('sub_kategori')->filter()->unique()->values() : collect();
        $stokAllSubs = $hasSubKategoriCol ? $menus->pluck('sub_kategori')->filter()->unique()->values() : collect();
    @endphp

    window.stokSubCategoryData = {
        makanan: @json($stokMakananSubs),
        minuman: @json($stokMinumanSubs),
        all: @json($stokAllSubs)
    };

    let currentCategory = 'all';
    let currentSubCategory = 'all';
    let onlyHabis = false;

    function renderStokSubPills() {
        const wrapper = document.getElementById('stok-sub-category-wrapper');
        const container = document.getElementById('stok-sub-category-pills');
        if (!container) return;

        const data = window.stokSubCategoryData || {};
        let subs = [];
        if (currentCategory === 'makanan') {
            subs = data.makanan || [];
            if (wrapper) wrapper.style.setProperty('display', 'block', 'important');
        } else if (currentCategory === 'minuman') {
            subs = data.minuman || [];
            if (wrapper) wrapper.style.setProperty('display', 'block', 'important');
        } else {
            if (wrapper) wrapper.style.setProperty('display', 'none', 'important');
            container.innerHTML = '';
            return;
        }

        let html = `
            <button type="button" class="btn btn-sm btn-stok-sub active rounded-pill px-3 py-1 flex-shrink-0" 
                    onclick="setSubCategoryFilter('all', this)"
                    style="background-color: rgba(192, 142, 92, 0.18); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.35); font-size: 0.75rem; font-weight: 500;">
                Semua ${currentCategory === 'makanan' ? 'Makanan' : 'Minuman'}
            </button>
        `;

        subs.forEach(sub => {
            const safeSub = sub.replace(/'/g, "\\'");
            html += `
                <button type="button" class="btn btn-sm btn-stok-sub rounded-pill px-3 py-1 flex-shrink-0" 
                        onclick="setSubCategoryFilter('${safeSub}', this)"
                        style="background-color: #0e1217; color: #8b949e; border: 1px solid #21262d; font-size: 0.75rem;">
                    ${sub}
                </button>
            `;
        });

        container.innerHTML = html;
        container.scrollLeft = 0;
    }

    function setCategoryFilter(category, btn) {
        currentCategory = category;
        currentSubCategory = 'all';

        document.querySelectorAll('.btn-category-pill').forEach(b => {
            b.classList.remove('active');
            b.classList.add('text-secondary');
        });
        if (btn) {
            btn.classList.add('active');
            btn.classList.remove('text-secondary');
        }

        renderStokSubPills();
        filterMenus();
    }

    function setSubCategoryFilter(subCat, btn) {
        currentSubCategory = (subCat || 'all').toLowerCase();

        document.querySelectorAll('.btn-stok-sub').forEach(b => {
            b.classList.remove('active');
            b.style.backgroundColor = '#0e1217';
            b.style.color = '#8b949e';
            b.style.borderColor = '#21262d';
        });

        if (btn) {
            btn.classList.add('active');
            btn.style.backgroundColor = 'rgba(192, 142, 92, 0.18)';
            btn.style.color = '#c08e5c';
            btn.style.borderColor = 'rgba(192, 142, 92, 0.35)';
        }

        filterMenus();
    }

    function toggleHabisFilter(btn) {
        onlyHabis = !onlyHabis;
        if(onlyHabis) {
            btn.style.backgroundColor = 'rgba(239, 68, 68, 0.15)';
            btn.style.borderColor = 'rgba(239, 68, 68, 0.35)';
            btn.style.color = '#f87171';
            btn.classList.add('active');
        } else {
            btn.style.backgroundColor = '#0e1217';
            btn.style.borderColor = '#21262d';
            btn.style.color = '#8b949e';
            btn.classList.remove('active');
        }
        filterMenus();
    }

    function filterMenus() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.menu-row');

        rows.forEach(row => {
            const name = row.dataset.name;
            const category = row.dataset.category;
            const subcategory = (row.dataset.subcategory || '').toLowerCase();
            const isAvail = row.dataset.available === '1';

            const matchQuery = name.includes(query);
            const matchCategory = (currentCategory === 'all' || category === currentCategory);
            const matchSubCategory = (currentSubCategory === 'all' || subcategory === currentSubCategory);
            const matchHabis = (!onlyHabis || !isAvail);

            if(matchQuery && matchCategory && matchSubCategory && matchHabis) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function applyAvailabilityUI(menuId, isAvail) {
        const btnTersedia = document.getElementById('btn-tersedia-' + menuId);
        const btnHabis = document.getElementById('btn-habis-' + menuId);
        const row = document.getElementById('row-' + menuId);
        const badgeHabis = document.getElementById('badge-habis-' + menuId);

        if (!row || !btnTersedia || !btnHabis) return;

        row.dataset.available = isAvail ? '1' : '0';
        if (isAvail) {
            row.classList.remove('menu-row-habis');
            if (badgeHabis) badgeHabis.style.setProperty('display', 'none', 'important');
            btnTersedia.className = 'btn btn-sm btn-touch flex-fill rounded-pill btn-avail-active fw-semibold';
            btnHabis.className = 'btn btn-sm btn-touch flex-fill rounded-pill btn-avail-inactive';
        } else {
            row.classList.add('menu-row-habis');
            if (badgeHabis) badgeHabis.style.setProperty('display', 'inline-block', 'important');
            btnTersedia.className = 'btn btn-sm btn-touch flex-fill rounded-pill btn-avail-inactive';
            btnHabis.className = 'btn btn-sm btn-touch flex-fill rounded-pill btn-habis-active fw-semibold';
        }
        updateCounters();
    }

    function setMenuAvailability(menuId, targetAvailable) {
        const row = document.getElementById('row-' + menuId);
        const currentAvail = row ? (row.dataset.available === '1') : null;
        const wantAvailable = (targetAvailable === 1 || targetAvailable === true);

        // Optimistic UI update
        applyAvailabilityUI(menuId, wantAvailable);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        // Send explicit status to server
        fetch('/kasir/stok/' + menuId + '/toggle', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                is_available: wantAvailable
            })
        })
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(data => {
            if(data.success) {
                // Ensure UI is strictly matching backend response
                applyAvailabilityUI(menuId, data.is_available);
                if (typeof window.showToast === 'function') {
                    window.showToast(data.message, data.is_available ? 'success' : 'danger');
                }
            }
        })
        .catch(err => {
            console.error('[Menu Availability Error]', err);
            // Rollback if request failed
            if (currentAvail !== null) {
                applyAvailabilityUI(menuId, currentAvail);
            }
            if (typeof window.showToast === 'function') {
                window.showToast('Gagal mengubah status ketersediaan menu. Periksa koneksi jaringan.', 'danger');
            }
        });
    }

    function updateCounters() {
        const allRows = document.querySelectorAll('.menu-row');
        let tersedia = 0;
        let habis = 0;
        allRows.forEach(r => {
            if(r.dataset.available === '1') tersedia++;
            else habis++;
        });
        const countTersediaEl = document.getElementById('count-tersedia');
        const countHabisEl = document.getElementById('count-habis');
        if (countTersediaEl) countTersediaEl.innerText = tersedia;
        if (countHabisEl) countHabisEl.innerText = habis;
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderStokSubPills();
    });
</script>
@endsection
