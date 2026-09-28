@extends('layouts.admin')

@section('content')
<div class="container pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $menu->exists ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
            <p class="text-white-50 mb-0">Kelola informasi nama, kategori, harga, varian, dan foto produk.</p>
        </div>
        <a href="{{ route('admin.menu.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Menu
        </a>
    </div>

    <div class="card shadow-sm border-0" style="background-color: #161b22; border: 1px solid #21262d !important;">
        <div class="card-body p-4">
            @if(isset($errors) && $errors->any())
                <div class="alert alert-danger mb-4">
                    <h5 class="alert-heading fs-6 fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Terjadi kesalahan saat menyimpan produk:</h5>
                    <ul class="mb-0 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="form-produk" action="{{ $menu->exists ? route('admin.menu.update', $menu->id) : route('admin.menu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($menu->exists)
                    @method('PUT')
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_menu" class="form-control" value="{{ old('nama_menu', $menu->nama_menu) }}" placeholder="Cth: Ayam Bakar Madu" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Kategori Utama <span class="text-danger">*</span></label>
                        @php
                            $currentCategory = strtolower(old('kategori', $menu->kategori ?? 'makanan'));
                        @endphp
                        <select name="kategori" id="kategori-select" class="form-select" onchange="handleMainCategoryChange(this.value)" required>
                            <option value="makanan" {{ $currentCategory == 'makanan' ? 'selected' : '' }}>Makanan</option>
                            <option value="minuman" {{ $currentCategory == 'minuman' ? 'selected' : '' }}>Minuman</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Sub-Kategori Spesifik</label>
                        @php
                            $currentSubCategory = old('sub_kategori', $menu->sub_kategori ?? '');
                        @endphp
                        <select id="sub-kategori-select" class="form-select" onchange="handleSubCategorySelectChange(this)">
                            <!-- Populated dynamically by JS based on main category -->
                        </select>
                        <input type="text" name="sub_kategori" id="sub-kategori-final-input" class="form-control mt-2" value="{{ $currentSubCategory }}" placeholder="Ketik nama sub-kategori baru..." style="display: none;">
                    </div>
                </div>

                <div class="row g-3 mb-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Harga Satuan (Rp) <span id="harga-asterisk" class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="0.01" name="harga" id="harga-input" class="form-control" value="{{ old('harga', $menu->harga) }}" placeholder="Cth: 15000" required>
                        </div>
                        <div class="form-text text-warning small mt-1" id="harga-dynamic-note" style="display: none;">
                            <i class="bi bi-info-circle me-1"></i> Mode harga dinamis aktif. Nominal tidak wajib diisi (akan otomatis di-set Rp 0 / Sesuai Timbangan).
                        </div>
                    </div>
                    <div class="col-md-6 pt-md-4">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="is_dynamic_price" value="1" class="form-check-input" id="is_dynamic_price" {{ old('is_dynamic_price', $menu->is_dynamic_price) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold text-warning" for="is_dynamic_price">
                                Harga Dinamis (Tentukan harga manual saat transaksi, cth: ikan timbang)
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-3 p-3 rounded-3" style="background: rgba(192, 142, 92, 0.08); border: 1px dashed rgba(192, 142, 92, 0.3);">
                    <div class="small text-white-50">
                        <i class="bi bi-info-circle text-warning me-1"></i>
                        Status ketersediaan produk (<strong>Tersedia / Habis</strong>) dikontrol langsung secara operasional oleh staf Waitress melalui POS.
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0 fw-bold">Deskripsi Produk</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-ai-desc" onclick="generateDesc()">
                            <i class="bi bi-stars"></i> Generate dengan AI
                        </button>
                    </div>
                    <textarea name="deskripsi" id="deskripsi-input" class="form-control" rows="3" placeholder="Jelaskan cita rasa atau keunikan menu ini...">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                    <small class="text-white-50 d-block mt-1" id="ai-status"></small>
                </div>

                <hr class="my-4 border-secondary opacity-25">

                <!-- Builder Varian & Toping (Add-ons) -->
                @include("components.admin.menu-variant-builder")

                <hr class="my-4 border-secondary opacity-25">

                <!-- Upload Gambar -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Foto Produk</label>
                    @if($menu->image_url && $menu->image)
                        <div class="mb-3 p-2 rounded d-inline-block" style="background: #11141a; border: 1px solid #21262d;">
                            <img src="{{ $menu->image_url }}" onerror="this.onerror=null; this.src='/images/logo.png';" alt="Foto Produk" style="max-width: 140px; max-height: 140px; object-fit: cover; border-radius: 8px;">
                            <div class="small text-white-50 mt-1 text-center">Foto saat ini</div>
                        </div>
                    @endif
                    <input type="file" name="image" accept="image/*" class="form-control">
                    <div class="form-text text-white-50">Format didukung: JPG, PNG, WEBP. Maksimal 2MB. Disarankan rasio 1:1 (persegi).</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Produk
                    </button>
                    <a href="{{ route('admin.menu.index') }}" class="btn btn-secondary px-4 py-2">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    window.MenuFormConfig = {
        aiDescriptionUrl: "{{ route('admin.menu.ai_description') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/admin/menu-form.js') }}?v={{ file_exists(public_path('js/admin/menu-form.js')) ? filemtime(public_path('js/admin/menu-form.js')) : '1.0' }}"></script>
@endsection
