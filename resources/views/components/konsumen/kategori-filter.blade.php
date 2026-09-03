<div class="d-flex gap-2 overflow-auto pb-2" style="white-space: nowrap; max-width: 100%;">
    <button type="button" class="btn btn-sm btn-outline-secondary text-white active btn-filter rounded-pill px-3 py-1" onclick="filterMenu('semua', this)">Semua</button>
    @php
        $categories = $menus->pluck('kategori')->unique()->filter()->values();
    @endphp
    @foreach($categories as $cat)
        <button type="button" class="btn btn-sm btn-outline-secondary text-white btn-filter rounded-pill px-3 py-1 text-capitalize" onclick="filterMenu('{{ $cat }}', this)">{{ $cat }}</button>
    @endforeach
</div>