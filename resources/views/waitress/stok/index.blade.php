@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-0">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-accent mb-1"><i class="bi bi-check2-circle me-2"></i>Status Ketersediaan Menu</h4>
            <p class="text-white-50 mb-0">Atur ketersediaan menu secara langsung (Tersedia atau Habis). Menu yang habis otomatis tidak dapat dipesan oleh tamu.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-3 py-2 rounded-pill">
                <i class="bi bi-check-circle-fill me-1"></i> Tersedia: <strong id="count-tersedia">{{ $menus->where('is_available', true)->count() }}</strong>
            </span>
            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-3 py-2 rounded-pill">
                <i class="bi bi-x-circle-fill me-1"></i> Habis: <strong id="count-habis">{{ $menus->where('is_available', false)->count() }}</strong>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close btn-touch" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="card kasir-card mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-surface-dark border-secondary text-white-50"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" class="form-control text-white border-secondary bg-surface-dark" placeholder="Cari nama menu..." onkeyup="filterMenus()">
                    </div>
                </div>
                <div class="col-12 col-md-7 d-flex justify-content-md-end gap-2 flex-wrap">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-primary active" id="btn-filter-all" onclick="setCategoryFilter('all', this)">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-filter-makanan" onclick="setCategoryFilter('makanan', this)">Makanan</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-filter-minuman" onclick="setCategoryFilter('minuman', this)">Minuman</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-filter-habis" onclick="toggleHabisFilter(this)">
                        <i class="bi bi-filter"></i> Hanya Menu Habis
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table of Menus -->
    <div class="card kasir-card">
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 65vh; overflow-y: auto;">
                <table class="table table-dark table-hover align-middle mb-0" id="menuTable">
                    <thead class="table-dark sticky-top" style="z-index: 10;">
                        <tr>
                            <th class="ps-4" style="width: 80px;">Foto</th>
                            <th>Nama Menu</th>
                            <th class="text-center" style="width: 140px;">Kategori</th>
                            <th class="text-nowrap" style="width: 140px;">Harga</th>
                            <th class="pe-4 text-center" style="width: 220px;">Status Ketersediaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $menu)
                            <tr class="menu-row" data-name="{{ strtolower($menu->nama_menu) }}" data-category="{{ strtolower($menu->kategori) }}" data-available="{{ $menu->is_available ? '1' : '0' }}" id="row-{{ $menu->id }}">
                                <td class="ps-4">
                                    <div class="rounded d-flex align-items-center justify-content-center overflow-hidden" style="width: 50px; height: 50px; background-color: #161b22; border: 1px solid #21262d;">
                                        <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="{{ $menu->nama_menu }}" style="object-fit: contain; width: 100%; height: 100%; padding: 2px;">
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold fs-6 text-light">{{ $menu->nama_menu }}</div>
                                    @if($menu->is_dynamic_price)
                                        <span class="badge bg-info text-dark" style="font-size: 0.7rem;">Harga Dinamis / Timbangan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ strtolower($menu->kategori) == 'makanan' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                        {{ ucfirst($menu->kategori) }}
                                    </span>
                                </td>
                                <td class="text-nowrap fw-bold" style="color: #c08e5c;">
                                    {{ $menu->is_dynamic_price ? 'Timbangan' : 'Rp ' . number_format($menu->harga, 0, ',', '.') }}
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group w-100 shadow-sm" role="group" id="btn-group-{{ $menu->id }}">
                                        <button type="button" 
                                                class="btn btn-sm btn-touch {{ $menu->is_available ? 'btn-success fw-bold' : 'btn-outline-secondary text-white-50' }}" 
                                                onclick="setMenuAvailability({{ $menu->id }}, 1)"
                                                id="btn-tersedia-{{ $menu->id }}">
                                            <i class="bi bi-check-circle me-1"></i> Tersedia
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm btn-touch {{ !$menu->is_available ? 'btn-danger fw-bold' : 'btn-outline-secondary text-white-50' }}" 
                                                onclick="setMenuAvailability({{ $menu->id }}, 0)"
                                                id="btn-habis-{{ $menu->id }}">
                                            <i class="bi bi-x-circle me-1"></i> Habis
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-white-50">Belum ada menu produk terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container for Notifications -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
    <div id="availabilityToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="toastMessage">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <span>Status berhasil diperbarui.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
    let currentCategory = 'all';
    let onlyHabis = false;

    function setCategoryFilter(category, btn) {
        currentCategory = category;
        document.querySelectorAll('.btn-group button').forEach(b => {
            if(b.id.startsWith('btn-filter-')) {
                b.classList.remove('btn-primary', 'active');
                b.classList.add('btn-outline-primary');
            }
        });
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-primary', 'active');
        filterMenus();
    }

    function toggleHabisFilter(btn) {
        onlyHabis = !onlyHabis;
        if(onlyHabis) {
            btn.classList.remove('btn-outline-danger');
            btn.classList.add('btn-danger', 'active');
        } else {
            btn.classList.remove('btn-danger', 'active');
            btn.classList.add('btn-outline-danger');
        }
        filterMenus();
    }

    function filterMenus() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const rows = document.querySelectorAll('.menu-row');

        rows.forEach(row => {
            const name = row.dataset.name;
            const category = row.dataset.category;
            const isAvail = row.dataset.available === '1';

            const matchQuery = name.includes(query);
            const matchCategory = (currentCategory === 'all' || category === currentCategory);
            const matchHabis = (!onlyHabis || !isAvail);

            if(matchQuery && matchCategory && matchHabis) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function setMenuAvailability(menuId, isAvailable) {
        const btnTersedia = document.getElementById('btn-tersedia-' + menuId);
        const btnHabis = document.getElementById('btn-habis-' + menuId);
        const row = document.getElementById('row-' + menuId);

        // Optimistic UI update
        if(isAvailable === 1) {
            btnTersedia.className = 'btn btn-sm btn-touch btn-success fw-bold';
            btnHabis.className = 'btn btn-sm btn-touch btn-outline-secondary text-white-50';
            row.dataset.available = '1';
        } else {
            btnTersedia.className = 'btn btn-sm btn-touch btn-outline-secondary text-white-50';
            btnHabis.className = 'btn btn-sm btn-touch btn-danger fw-bold';
            row.dataset.available = '0';
        }

        updateCounters();

        // Send AJAX request
        fetch('/kasir/stok/' + menuId + '/toggle', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast(data.message, data.is_available ? 'success' : 'danger');
            }
        })
        .catch(err => {
            showToast('Gagal mengubah status ketersediaan.', 'danger');
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
        document.getElementById('count-tersedia').innerText = tersedia;
        document.getElementById('count-habis').innerText = habis;
    }

    function showToast(message, type = 'success') {
        const toastEl = document.getElementById('availabilityToast');
        const msgEl = document.getElementById('toastMessage');
        const icon = type === 'success' ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-danger';
        
        msgEl.innerHTML = `<i class="bi ${icon} fs-5"></i> <span>${message}</span>`;
        const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
    }
</script>
@endsection
