@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $pageTitle == 'Manajemen Menu' ? 'Manajemen Produk' : $pageTitle }}</h2>
            @if(!empty($showStockPage))
                <p class="text-white-50 mb-0">Kelola stok produk dan perbarui jumlah item yang tersedia.</p>
            @else
                <p class="text-white-50 mb-0">Tambah, edit, dan pantau ketersediaan produk.</p>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.menu.create') }}" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg me-1"></i> Tambah Produk</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    <div class="card shadow-sm">
        <div class="card-body p-0">
            @php
                $selectedCategory = request('category');
                $selectedSubCategory = request('sub_category');
                
                $hasSubKategoriCol = \Illuminate\Support\Facades\Schema::hasColumn('menu', 'sub_kategori');
                $subCatQuery = \App\Models\Menu::query();
                if ($selectedCategory) {
                    $subCatQuery->where('kategori', $selectedCategory);
                }
                $availableSubCategories = $hasSubKategoriCol 
                    ? $subCatQuery->pluck('sub_kategori')->filter()->unique()->values() 
                    : collect();
            @endphp

            <!-- Filter Header (Terpisah di luar table-responsive agar scroll independen dan lancar) -->
            <div class="p-3 border-bottom border-secondary">
                <!-- Level 1: Kategori Utama -->
                <div class="d-flex gap-2 align-items-center mb-2 flex-wrap">
                    <span class="fw-bold me-2 text-white-50 small">Kategori Utama:</span>
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'sub_category' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">Semua</a>
                    <a href="{{ request()->fullUrlWithQuery(['category' => 'makanan', 'sub_category' => null, 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'makanan' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">Makanan</a>
                    <a href="{{ request()->fullUrlWithQuery(['category' => 'minuman', 'sub_category' => null, 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'minuman' ? 'btn-primary' : 'btn-outline-primary' }} rounded-pill px-3">Minuman</a>
                    
                    <a href="{{ route('admin.menu.index') }}" class="btn btn-sm btn-outline-secondary ms-auto">Reset Filter</a>
                </div>

                <!-- Level 2: Sub-Kategori Spesifik (Hanya muncul jika Makanan atau Minuman dipilih) -->
                @if($selectedCategory && count($availableSubCategories) > 0)
                    <div class="d-flex gap-1 align-items-center overflow-auto pt-2 pb-3 subcat-scroll-container" style="white-space: nowrap; max-width: 100%;">
                        <span class="fw-bold me-2 text-white-50 small" style="font-size: 0.78rem;">Sub-Kategori:</span>
                        <a href="{{ request()->fullUrlWithQuery(['sub_category' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('sub_category') ? 'btn-secondary' : 'btn-outline-secondary text-white' }} rounded-pill px-3 py-1" style="font-size: 0.78rem;">Semua {{ ucfirst($selectedCategory) }}</a>
                        @foreach($availableSubCategories as $sub)
                            <a href="{{ request()->fullUrlWithQuery(['sub_category' => $sub, 'page' => null]) }}" class="btn btn-sm {{ request('sub_category') == $sub ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary text-white-50' }} rounded-pill px-3 py-1" style="font-size: 0.78rem;">{{ $sub }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            <style>
                .subcat-scroll-container {
                    cursor: grab;
                    user-select: none;
                    -webkit-overflow-scrolling: touch;
                }
                .subcat-scroll-container::-webkit-scrollbar {
                    height: 6px;
                }
                .subcat-scroll-container::-webkit-scrollbar-track {
                    background: rgba(255, 255, 255, 0.05);
                    border-radius: 10px;
                }
                .subcat-scroll-container::-webkit-scrollbar-thumb {
                    background: rgba(192, 142, 92, 0.4);
                    border-radius: 10px;
                }
                .subcat-scroll-container::-webkit-scrollbar-thumb:hover {
                    background: rgba(192, 142, 92, 0.7);
                }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const subCatContainer = document.querySelector('.subcat-scroll-container');
                    if (!subCatContainer) return;

                    // 1. Mouse wheel scrolls horizontally
                    subCatContainer.addEventListener('wheel', function(e) {
                        if (e.deltaY !== 0) {
                            e.preventDefault();
                            subCatContainer.scrollLeft += e.deltaY;
                        }
                    }, { passive: false });

                    // 2. Click & drag to scroll
                    let isDown = false;
                    let startX;
                    let scrollLeft;

                    subCatContainer.addEventListener('mousedown', (e) => {
                        // Ignore if user clicked directly on link to navigate
                        isDown = true;
                        subCatContainer.style.cursor = 'grabbing';
                        startX = e.pageX - subCatContainer.offsetLeft;
                        scrollLeft = subCatContainer.scrollLeft;
                    });

                    subCatContainer.addEventListener('mouseleave', () => {
                        isDown = false;
                        subCatContainer.style.cursor = 'grab';
                    });

                    subCatContainer.addEventListener('mouseup', () => {
                        isDown = false;
                        subCatContainer.style.cursor = 'grab';
                    });

                    subCatContainer.addEventListener('mousemove', (e) => {
                        if (!isDown) return;
                        e.preventDefault();
                        const x = e.pageX - subCatContainer.offsetLeft;
                        const walk = (x - startX) * 1.5;
                        subCatContainer.scrollLeft = scrollLeft - walk;
                    });
                });
            </script>

            <div class="table-responsive">
                <table class="table table-dark text-white border-secondary mb-0">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th class="text-center">Ketersediaan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $m)
                            <tr>
                                <td>
                                    @if($m->image)
                                        <div class="text-white border rounded d-flex align-items-center justify-content-center" style="background-color: #161b22; border: 1px solid #21262d !important; width: 80px; height: 80px; overflow: hidden;">
                                            <img src="{{ $m->image_url }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="{{ $m->nama_menu }}" style="object-fit: contain; width: 100%; height: 100%; padding: 4px;">
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="fw-semibold">{{ $m->nama_menu }}</td>
                                <td>
                                    <div class="d-flex flex-column gap-1 align-items-start">
                                        <span class="badge {{ strtolower($m->kategori) == 'makanan' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                            {{ strtolower($m->kategori) == 'makanan' ? 'Makanan' : 'Minuman' }}
                                        </span>
                                        @if($m->sub_kategori)
                                            <span class="badge bg-secondary text-white" style="font-size: 0.72rem;">
                                                {{ $m->sub_kategori }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-nowrap fw-bold" style="color: #c08e5c;">
                                    @if($m->is_dynamic_price || $m->harga == 0)
                                        <span class="badge bg-warning text-dark"><i class="bi bi-speedometer2 me-1"></i> Sesuai Timbangan</span>
                                    @else
                                        Rp {{ number_format($m->harga, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($m->is_available)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> Tersedia
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
                                            <i class="bi bi-x-circle me-1"></i> Habis
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex justify-content-start gap-1 flex-wrap flex-md-nowrap">
                                        <a href="{{ route('admin.menu.edit', $m->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="bi bi-pencil"></i> <span class="d-none d-md-inline">Edit</span>
                                        </a>
                                        <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus produk ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="bi bi-trash"></i> <span class="d-none d-md-inline">Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $menus->links() }}
        </div>
    </div>
</div>
@endsection
