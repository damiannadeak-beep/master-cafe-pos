@php
    $hasSubKategoriCol = \Illuminate\Support\Facades\Schema::hasColumn('menu', 'sub_kategori');
    $makananSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'makanan')->pluck('sub_kategori')->filter()->unique()->values() : collect();
    $minumanSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'minuman')->pluck('sub_kategori')->filter()->unique()->values() : collect();
    $allSubs = $hasSubKategoriCol ? $menus->pluck('sub_kategori')->filter()->unique()->values() : collect();
@endphp

<div class="kategori-filter-wrapper w-100 mt-2 mb-1">
    <!-- Level 1: Kategori Utama (Semua / Makanan / Minuman) -->
    <div class="d-flex gap-2 overflow-auto pb-2 mb-2" style="white-space: nowrap; max-width: 100%; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <button type="button" id="btn-main-cat-semua" class="btn btn-sm btn-outline-secondary text-white active btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('semua', this)">
            Semua Menu
        </button>
        <button type="button" id="btn-main-cat-makanan" class="btn btn-sm btn-outline-secondary text-white btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('makanan', this)">
            Makanan
        </button>
        <button type="button" id="btn-main-cat-minuman" class="btn btn-sm btn-outline-secondary text-white btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('minuman', this)">
            Minuman
        </button>
    </div>

    <!-- Level 2: Sub-Kategori Pills (Dinamis berdasarkan Level 1) -->
    <div id="sub-category-pills-container" class="d-flex gap-2 overflow-auto pb-1" style="white-space: nowrap; max-width: 100%;">
        <!-- Rendered dynamically by JS -->
    </div>
</div>

<script>
    window.subCategoryData = {
        makanan: @json($makananSubs),
        minuman: @json($minumanSubs),
        semua: @json($allSubs)
    };
</script>