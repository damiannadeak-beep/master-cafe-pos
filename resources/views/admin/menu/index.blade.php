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
            <div class="table-responsive">
                <div class="p-3 d-flex gap-2 align-items-center flex-wrap">
                    <span class="fw-bold me-2">Kategori:</span>
                    <div class="btn-group shadow-sm">
                        <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }}">Semua</a>
                        <a href="{{ request()->fullUrlWithQuery(['category' => 'makanan', 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'makanan' ? 'btn-primary' : 'btn-outline-primary' }}">Makanan</a>
                        <a href="{{ request()->fullUrlWithQuery(['category' => 'minuman', 'page' => null]) }}" class="btn btn-sm {{ request('category') == 'minuman' ? 'btn-primary' : 'btn-outline-primary' }}">Minuman</a>
                    </div>
                    
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary ms-auto">Reset Filter</a>
                </div>
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
                                    <span class="badge {{ $m->kategori == 'makanan' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                        {{ ucfirst($m->kategori) }}
                                    </span>
                                </td>
                                <td class="text-nowrap fw-bold" style="color: #c08e5c;">Rp {{ number_format($m->harga,0,',','.') }}</td>
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
