@props(['menus'])

<!-- Kolom Kiri: Daftar Menu -->
<div class="col-lg-8 col-md-7">
    <div class="kasir-card card bg-white">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h5 class="mb-0 fw-bold text-accent"><i class="bi bi-grid-fill me-2"></i>Daftar Produk</h5>
                
                <!-- Search Bar -->
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchInput" class="form-control bg-light border-start-0 rounded-end-pill" placeholder="Cari menu..." onkeyup="searchMenu(this.value)">
                </div>
            </div>
            
            <!-- Kategori Pills -->
            <div class="d-flex gap-2 mt-4 overflow-auto pb-2" style="white-space: nowrap;">
                <button type="button" class="btn btn-soft rounded-pill px-4 active" onclick="filterCategory('semua', this)">Semua</button>
                @php
                    $categories = $menus->pluck('kategori')->unique()->filter()->values();
                @endphp
                @foreach($categories as $cat)
                    <button type="button" class="btn btn-soft rounded-pill px-4" onclick="filterCategory('{{ $cat }}', this)">{{ $cat }}</button>
                @endforeach
            </div>
        </div>
        
        <div class="card-body px-4 pb-4">
            <div class="row g-4" id="menu-container">
                <!-- Menu items will be rendered here by JS -->
            </div>
            
            <!-- Pagination Controls -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-5 pt-3 border-top gap-3">
                <button class="btn btn-outline-secondary rounded-pill px-4" id="prevPage" onclick="changePage(-1)" disabled><i class="bi bi-chevron-left me-2"></i> Sebelumnya</button>
                <span id="pageInfo" class="fw-bold text-muted small">Halaman 1 / 1</span>
                <button class="btn btn-outline-secondary rounded-pill px-4" id="nextPage" onclick="changePage(1)" disabled>Selanjutnya <i class="bi bi-chevron-right ms-2"></i></button>
            </div>
        </div>
    </div>
</div>
