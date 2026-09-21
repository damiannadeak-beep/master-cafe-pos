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
            
            @php
                $makananSubs = $menus->filter(fn($m) => strtolower($m->kategori) === 'makanan')->pluck('sub_kategori')->filter()->unique()->values();
                $minumanSubs = $menus->filter(fn($m) => strtolower($m->kategori) === 'minuman')->pluck('sub_kategori')->filter()->unique()->values();
                $allSubs = $menus->pluck('sub_kategori')->filter()->unique()->values();
            @endphp
            <script>
                window.posSubCategoryData = {
                    makanan: @json($makananSubs),
                    minuman: @json($minumanSubs),
                    semua: @json($allSubs)
                };
            </script>

            <!-- Kategori Utama Pills (Level 1) -->
            <div class="d-flex gap-2 mt-3 overflow-auto pb-2" style="white-space: nowrap; border-bottom: 1px solid rgba(255,255,255,0.08);">
                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1 btn-pos-main-cat active" onclick="filterPosMainCategory('semua', this)">
                    Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1 btn-pos-main-cat" onclick="filterPosMainCategory('makanan', this)">
                    Makanan
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1 btn-pos-main-cat" onclick="filterPosMainCategory('minuman', this)">
                    Minuman
                </button>
            </div>

            <!-- Sub-Kategori Pills (Level 2) - Hanya tampil jika Makanan atau Minuman dipilih -->
            <div id="pos-sub-category-wrapper" class="w-100 mt-2" style="display: none !important;">
                <div id="pos-sub-category-pills" class="d-flex gap-2 pb-2 overflow-auto subcat-scroll-container align-items-center" style="white-space: nowrap; flex-wrap: nowrap; -webkit-overflow-scrolling: touch;">
                    <!-- Rendered dynamically by JS -->
                </div>
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


