@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-white mb-1">{{ $pageTitle == 'Manajemen Menu' ? 'Manajemen Produk' : $pageTitle }}</h2>
            @if(!empty($showStockPage))
                <p class="text-secondary small mb-0">Kelola stok produk dan perbarui jumlah item yang tersedia.</p>
            @else
                <p class="text-secondary small mb-0">Tambah, edit, dan pantau ketersediaan produk.</p>
            @endif
        </div>
        <div>
            <a href="{{ route('admin.menu.create') }}" class="btn fw-semibold text-white px-3 py-2 rounded-3 shadow-sm d-inline-flex align-items-center gap-2" style="background-color: #c08e5c; border: none; font-size: 0.9rem;">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important;">
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

            <!-- Filter Header Rapi & Bersih -->
            <div class="p-3 border-bottom" style="border-color: #21262d !important; background-color: #161b22;">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <!-- Segmented Control Kategori Utama -->
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="text-secondary small fw-medium me-1">Kategori:</span>
                        <div class="d-inline-flex p-1 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                            <a href="{{ request()->fullUrlWithQuery(['category' => null, 'sub_category' => null, 'page' => null]) }}" 
                               class="btn btn-sm px-3 py-1 text-decoration-none fw-medium rounded-2" 
                               style="{{ !request('category') ? 'background-color: rgba(192, 142, 92, 0.2); color: #fff; border: 1px solid rgba(192, 142, 92, 0.4);' : 'color: #8b949e; border: 1px solid transparent;' }}">
                               Semua
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['category' => 'makanan', 'sub_category' => null, 'page' => null]) }}" 
                               class="btn btn-sm px-3 py-1 text-decoration-none fw-medium rounded-2" 
                               style="{{ request('category') == 'makanan' ? 'background-color: rgba(192, 142, 92, 0.2); color: #fff; border: 1px solid rgba(192, 142, 92, 0.4);' : 'color: #8b949e; border: 1px solid transparent;' }}">
                               Makanan
                            </a>
                            <a href="{{ request()->fullUrlWithQuery(['category' => 'minuman', 'sub_category' => null, 'page' => null]) }}" 
                               class="btn btn-sm px-3 py-1 text-decoration-none fw-medium rounded-2" 
                               style="{{ request('category') == 'minuman' ? 'background-color: rgba(192, 142, 92, 0.2); color: #fff; border: 1px solid rgba(192, 142, 92, 0.4);' : 'color: #8b949e; border: 1px solid transparent;' }}">
                               Minuman
                            </a>
                        </div>
                    </div>

                    @if(request('category') || request('sub_category'))
                        <a href="{{ route('admin.menu.index') }}" class="btn btn-sm text-secondary px-3 py-1.5 rounded-2 d-inline-flex align-items-center gap-1.5" style="border: 1px solid #21262d; background: transparent; font-size: 0.8rem;">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filter
                        </a>
                    @endif
                </div>

                <!-- Level 2: Sub-Kategori Spesifik (Horizontal Scroll Halus) -->
                @if($selectedCategory && count($availableSubCategories) > 0)
                    <div class="d-flex gap-1.5 align-items-center overflow-auto pt-3 subcat-scroll-container" style="white-space: nowrap; max-width: 100%;">
                        <span class="text-secondary small fw-medium me-2" style="font-size: 0.78rem;">Sub-Kategori:</span>
                        <a href="{{ request()->fullUrlWithQuery(['sub_category' => null, 'page' => null]) }}" 
                           class="btn btn-sm rounded-2 px-3 py-1 text-decoration-none" 
                           style="{{ !request('sub_category') ? 'background-color: #c08e5c; color: #fff; font-weight: 600;' : 'background-color: rgba(255, 255, 255, 0.04); color: #8b949e; border: 1px solid rgba(255, 255, 255, 0.08);' }} font-size: 0.78rem;">
                           Semua {{ ucfirst($selectedCategory) }}
                        </a>
                        @foreach($availableSubCategories as $sub)
                            <a href="{{ request()->fullUrlWithQuery(['sub_category' => $sub, 'page' => null]) }}" 
                               class="btn btn-sm rounded-2 px-3 py-1 text-decoration-none" 
                               style="{{ request('sub_category') == $sub ? 'background-color: #c08e5c; color: #fff; font-weight: 600;' : 'background-color: rgba(255, 255, 255, 0.04); color: #8b949e; border: 1px solid rgba(255, 255, 255, 0.08);' }} font-size: 0.78rem;">
                               {{ $sub }}
                            </a>
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

                    subCatContainer.addEventListener('wheel', function(e) {
                        if (e.deltaY !== 0) {
                            e.preventDefault();
                            subCatContainer.scrollLeft += e.deltaY;
                        }
                    }, { passive: false });

                    let isDown = false;
                    let startX;
                    let scrollLeft;

                    subCatContainer.addEventListener('mousedown', (e) => {
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

            <!-- Tabel Produk Bersih & Matang -->
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle mb-0" style="--bs-table-bg: transparent;">
                    <thead style="border-bottom: 1px solid #21262d;">
                        <tr class="text-secondary small">
                            <th class="ps-4 py-3 fw-medium" style="width: 70px;">Gambar</th>
                            <th class="py-3 fw-medium">Nama Produk</th>
                            <th class="py-3 fw-medium">Kategori</th>
                            <th class="py-3 fw-medium">Harga</th>
                            <th class="text-center py-3 fw-medium">Ketersediaan</th>
                            <th class="text-end pe-4 py-3 fw-medium" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($menus as $m)
                            <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                                <!-- Thumbnail Gambar Rapi -->
                                <td class="ps-4 py-3">
                                    <div class="rounded-2 d-flex align-items-center justify-content-center overflow-hidden" 
                                         style="width: 44px; height: 44px; background-color: #12161d; border: 1px solid #21262d;">
                                        @if($m->image)
                                            <img src="{{ $m->image_url }}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="{{ $m->nama_menu }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <i class="bi {{ strtolower($m->kategori) == 'minuman' ? 'bi-cup-hot' : 'bi-egg-fried' }}" style="color: #6b7280; font-size: 1.1rem;"></i>
                                        @endif
                                    </div>
                                </td>

                                <!-- Nama Produk -->
                                <td class="py-3">
                                    <span class="fw-medium text-white d-block">{{ $m->nama_menu }}</span>
                                </td>

                                <!-- Kategori & Sub-Kategori Halus -->
                                <td class="py-3">
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <span class="badge rounded-2 px-2.5 py-1 text-secondary" style="background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.72rem; font-weight: 500;">
                                            {{ ucfirst($m->kategori ?? 'Menu') }}
                                        </span>
                                        @if($m->sub_kategori)
                                            <span class="text-secondary small" style="font-size: 0.75rem;">
                                                {{ $m->sub_kategori }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Harga -->
                                <td class="py-3 text-nowrap">
                                    @if($m->is_dynamic_price || $m->harga == 0)
                                        <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.75rem;">
                                            <i class="bi bi-speedometer2 me-1"></i> Timbangan
                                        </span>
                                    @else
                                        <span class="fw-semibold text-white">Rp {{ number_format($m->harga, 0, ',', '.') }}</span>
                                    @endif
                                </td>

                                <!-- Ketersediaan Status -->
                                <td class="py-3 text-center">
                                    @if($m->is_available)
                                        <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.75rem; font-weight: 500;">
                                            <i class="bi bi-check-circle-fill me-1" style="font-size: 0.65rem;"></i> Tersedia
                                        </span>
                                    @else
                                        <span class="badge rounded-2 px-2.5 py-1" style="background-color: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.75rem; font-weight: 500;">
                                            <i class="bi bi-x-circle-fill me-1" style="font-size: 0.65rem;"></i> Habis
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi Square Icon Buttons -->
                                <td class="py-3 text-end pe-4">
                                    <div class="d-inline-flex justify-content-end align-items-center gap-2 flex-nowrap" style="gap: 8px !important;">
                                        <a href="{{ route('admin.menu.edit', $m->id) }}" 
                                           class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" 
                                           style="width: 36px; height: 36px; background: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c;" 
                                           title="Edit Produk">
                                            <i class="bi bi-pencil" style="font-size: 0.85rem;"></i>
                                        </a>
                                        <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" class="d-inline-flex m-0 p-0" onsubmit="return confirm('Hapus produk {{ addslashes($m->nama_menu) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" 
                                                    style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" 
                                                    title="Hapus Produk">
                                                <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-secondary small">Belum ada produk yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer px-4 py-3" style="background-color: #161b22; border-top: 1px solid #21262d;">
            {{ $menus->links() }}
        </div>
    </div>
</div>
@endsection
