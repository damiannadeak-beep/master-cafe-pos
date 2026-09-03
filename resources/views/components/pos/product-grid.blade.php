@props(['menus'])

<!-- Kolom Kiri: Daftar Menu -->
<div>
    <div class="kasir-card hover-lift card bg-transparent">
        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="mb-0 fw-bold text-accent"><i class="bi bi-grid-fill me-2"></i>Daftar Produk</h5>
                
                <!-- Search Bar -->
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text  border-end-0 rounded-start-pill"><i class="bi bi-search text-secondary"></i></span>
                    <input type="text" id="searchInput" class="form-control  border-start-0 rounded-end-pill" placeholder="Cari menu..." onkeyup="searchMenu(this.value)">
                </div>
            </div>
            
            <!-- Kategori Pills -->
            <div class="d-flex gap-2 mt-4 overflow-auto pb-2" style="white-space: nowrap;">
                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1 active" onclick="filterCategory('semua', this)">Semua</button>
                @php
                    $categories = $menus->pluck('kategori')->unique()->filter()->values();
                @endphp
                @foreach($categories as $cat)
                    <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1" onclick="filterCategory('{{ $cat }}', this)">{{ $cat }}</button>
                @endforeach
            </div>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="row g-4" id="menu-container">
                <!-- Menu items will be rendered here by JS -->
            </div>
            
            <!-- Pagination Controls -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-5 pt-3 border-top gap-3">
                <button class="btn btn-outline-secondary rounded-pill px-4 btn-touch" id="prevPage" onclick="changePage(-1)" disabled><i class="bi bi-chevron-left me-2"></i> Sebelumnya</button>
                <span id="pageInfo" class="fw-bold text-secondary small">Halaman 1 / 1</span>
                <button class="btn btn-outline-secondary rounded-pill px-4 btn-touch" id="nextPage" onclick="changePage(1)" disabled>Selanjutnya <i class="bi bi-chevron-right ms-2"></i></button>
            </div>
        </div>
    </div>
</div>


