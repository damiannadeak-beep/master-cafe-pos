@php
    $hasSubKategoriCol = \Illuminate\Support\Facades\Schema::hasColumn('menu', 'sub_kategori');
    $makananSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'makanan')->pluck('sub_kategori')->filter()->unique()->values() : collect();
    $minumanSubs = $hasSubKategoriCol ? $menus->filter(fn($m) => strtolower($m->kategori) === 'minuman')->pluck('sub_kategori')->filter()->unique()->values() : collect();
    $allSubs = $hasSubKategoriCol ? $menus->pluck('sub_kategori')->filter()->unique()->values() : collect();
@endphp

<div class="kategori-filter-wrapper w-100 mt-2 mb-1">
    <!-- Level 1: Kategori Utama (Semua / Makanan / Minuman) -->
    <div class="d-flex gap-2 overflow-auto pb-2 mb-2" style="white-space: nowrap; max-width: 100%; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <button type="button" id="btn-main-cat-semua" class="btn btn-sm active btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('semua', this)" style="background-color: #ffffff; color: #0e1217; border: 1px solid #ffffff;">
            Semua Menu
        </button>
        <button type="button" id="btn-main-cat-makanan" class="btn btn-sm btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('makanan', this)" style="background-color: transparent; color: #8b949e; border: 1px solid rgba(255,255,255,0.15);">
            Makanan
        </button>
        <button type="button" id="btn-main-cat-minuman" class="btn btn-sm btn-main-filter rounded-pill px-3 py-1-5 fw-bold" onclick="selectMainCategory('minuman', this)" style="background-color: transparent; color: #8b949e; border: 1px solid rgba(255,255,255,0.15);">
            Minuman
        </button>
    </div>

    <!-- Level 2: Sub-Kategori Pills (Dibuat & dimunculkan dinamis HANYA saat Makanan/Minuman di-klik) -->
    <div id="sub-category-pills-container" style="white-space: nowrap; max-width: 100%; display: none;"></div>
</div>

<script>
    window.subCategoryData = {
        makanan: @json($makananSubs),
        minuman: @json($minumanSubs),
        semua: @json($allSubs)
    };
</script>