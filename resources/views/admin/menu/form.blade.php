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
                    <div class="col-md-7">
                        <label class="form-label fw-bold">Nama Produk <span class="text-danger">*</span></label>
                        <input type="text" name="nama_menu" class="form-control" value="{{ old('nama_menu', $menu->nama_menu) }}" placeholder="Cth: Ayam Bakar Madu" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Kategori <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select" required>
                            <option value="">-- Pilih Kategori Utama --</option>
                            <option value="makanan" {{ strtolower((string) old('kategori', $menu->kategori)) == 'makanan' ? 'selected' : '' }}>Makanan</option>
                            <option value="minuman" {{ strtolower((string) old('kategori', $menu->kategori)) == 'minuman' ? 'selected' : '' }}>Minuman</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3 align-items-center">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Harga Satuan (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" step="0.01" name="harga" class="form-control" value="{{ old('harga', $menu->harga) }}" placeholder="Cth: 15000" required>
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
(function() {
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function initMenuVariantBuilder() {
        const variantsContainer = document.getElementById('variants-container');
        const inputVariants = document.getElementById('variants_json_input');
        const formProduk = document.getElementById('form-produk');
        if (!variantsContainer || !inputVariants) return;

        let variants = [];
        try {
            let rawVal = inputVariants.value || '[]';
            const txt = document.createElement('textarea');
            txt.innerHTML = rawVal;
            rawVal = txt.value;
            variants = typeof rawVal === 'string' ? JSON.parse(rawVal) : rawVal;
        } catch(e) {
            console.error('Failed to parse variants_json:', e);
            variants = [];
        }
        if (!Array.isArray(variants)) {
            variants = [];
        }

        function syncInput() {
            if (inputVariants) {
                inputVariants.value = JSON.stringify(variants);
            }
        }

        function renderVariants() {
            variantsContainer.innerHTML = '';

            if (variants.length === 0) {
                variantsContainer.innerHTML = `
                    <div class="p-4 rounded-3 text-center mb-3" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.15);">
                        <i class="bi bi-tags text-secondary fs-3 d-block mb-2"></i>
                        <div class="text-white-50 small mb-2">Belum ada varian atau toping untuk produk ini.</div>
                        <div class="small text-secondary">Gunakan tombol <strong>Preset Cepat</strong> di atas atau klik <strong>Tambah Grup Varian</strong> untuk membuat pilihan seperti Level Pedas, Suhu, atau Topping.</div>
                    </div>
                `;
                syncInput();
                return;
            }

            variants.forEach((group, gIndex) => {
                let optionsHtml = '';
                (group.options || []).forEach((opt, oIndex) => {
                    const isFirst = oIndex === 0;
                    const isLast = oIndex === group.options.length - 1;
                    optionsHtml += `
                        <div class="row g-2 align-items-center mb-2 option-row p-2 rounded-2" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="col-md-5 col-12">
                                <label class="small text-white-50 d-md-none mb-1">Nama Opsi:</label>
                                <input type="text" class="form-control text-white border-secondary form-control-sm var-opt-name" data-g="${gIndex}" data-o="${oIndex}" value="${escapeHtml(opt.name)}" placeholder="Nama Opsi (Cth: Sedang, Extra Keju)">
                            </div>
                            <div class="col-md-4 col-7">
                                <label class="small text-white-50 d-md-none mb-1">Tambahan Harga:</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text" style="background: rgba(192, 142, 92, 0.2); color: #c08e5c; border-color: rgba(255,255,255,0.15);">+ Rp</span>
                                    <input type="number" class="form-control text-white border-secondary var-opt-price" data-g="${gIndex}" data-o="${oIndex}" value="${opt.price || 0}" placeholder="0" min="0" step="500">
                                </div>
                            </div>
                            <div class="col-md-3 col-5 text-end d-flex gap-1 justify-content-end align-items-center pt-md-0 pt-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary move-opt-up" data-g="${gIndex}" data-o="${oIndex}" title="Geser ke Atas" ${isFirst ? 'disabled' : ''}>
                                    <i class="bi bi-arrow-up"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary move-opt-down" data-g="${gIndex}" data-o="${oIndex}" title="Geser ke Bawah" ${isLast ? 'disabled' : ''}>
                                    <i class="bi bi-arrow-down"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-opt" data-g="${gIndex}" data-o="${oIndex}" title="Hapus Opsi">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });

                const isSingle = group.type === 'single';
                const html = `
                    <div class="card mb-3 border-0 shadow-sm rounded-3" style="background: #11141a; border: 1px solid rgba(192, 142, 92, 0.35) !important;">
                        <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: rgba(192, 142, 92, 0.12); border-bottom: 1px solid rgba(192, 142, 92, 0.25);">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill text-white fw-bold" style="background: #c08e5c;">#${gIndex + 1}</span>
                                <span class="fw-bold text-white small">${escapeHtml(group.group_name) || 'Grup Varian Baru'}</span>
                                <span class="badge rounded-pill ${isSingle ? 'bg-primary' : 'bg-warning text-dark'} small" style="font-size: 0.68rem;">
                                    ${isSingle ? '<i class="bi bi-ui-radios me-1"></i>Pilih 1 (Radio)' : '<i class="bi bi-ui-checks me-1"></i>Bisa Banyak (Checkbox)'}
                                </span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-group" data-g="${gIndex}">
                                <i class="bi bi-trash3 me-1"></i> Hapus Grup
                            </button>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-white-50">Nama Grup Varian</label>
                                    <input type="text" class="form-control text-white border-secondary form-control-sm var-group-name" data-g="${gIndex}" value="${escapeHtml(group.group_name)}" placeholder="Cth: Level Pedas, Pilihan Saus, Toping">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-white-50">Sifat Pilihan untuk Konsumen/Kasir</label>
                                    <select class="form-select text-white border-secondary form-select-sm var-group-type" data-g="${gIndex}">
                                        <option value="single" ${isSingle ? 'selected' : ''}>Pilih Satu Opsi (Radio Button - Wajib 1)</option>
                                        <option value="multiple" ${!isSingle ? 'selected' : ''}>Bisa Pilih Banyak Opsi (Checkbox - Tambahan/Toping)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold text-white-50">Daftar Pilihan & Harga Tambahan:</span>
                            </div>

                            <div class="options-container">
                                ${optionsHtml}
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-success mt-2 add-opt" data-g="${gIndex}">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Pilihan Baru
                            </button>
                        </div>
                    </div>
                `;
                variantsContainer.insertAdjacentHTML('beforeend', html);
            });

            syncInput();
        }

        // Add group button listener
        const addGroupBtn = document.getElementById('add-variant-group');
        if (addGroupBtn) {
            addGroupBtn.onclick = function() {
                variants.push({
                    group_name: '',
                    type: 'single',
                    options: [{ name: '', price: 0 }]
                });
                renderVariants();
                // Focus newly added group name input
                const inputs = variantsContainer.querySelectorAll('.var-group-name');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            };
        }

        // Presets handler globally exposed on window
        window.addPresetVariant = function(type) {
            if (type === 'saus') {
                variants.push({
                    group_name: 'Pilihan Saus',
                    type: 'single',
                    options: [
                        { name: 'Saus Original / Biasa', price: 0 },
                        { name: 'Saus Lada Hitam', price: 3000 },
                        { name: 'Saus Asam Manis', price: 2000 },
                        { name: 'Saus BBQ', price: 3000 }
                    ]
                });
            } else if (type === 'pedas') {
                variants.push({
                    group_name: 'Level Pedas',
                    type: 'single',
                    options: [
                        { name: 'Level 0 (Tidak Pedas)', price: 0 },
                        { name: 'Level 1 (Sedang)', price: 0 },
                        { name: 'Level 2 (Pedas)', price: 1000 },
                        { name: 'Level 3 (Extra Pedas)', price: 2000 }
                    ]
                });
            } else if (type === 'suhu') {
                variants.push({
                    group_name: 'Suhu / Penyajian',
                    type: 'single',
                    options: [
                        { name: 'Dingin / Pakai Es', price: 0 },
                        { name: 'Panas / Hangat', price: 0 }
                    ]
                });
            } else if (type === 'toping') {
                variants.push({
                    group_name: 'Toping Extra',
                    type: 'multiple',
                    options: [
                        { name: 'Extra Keju', price: 2000 },
                        { name: 'Extra Telur', price: 3000 },
                        { name: 'Extra Sosis', price: 3000 },
                        { name: 'Extra Mozzarella', price: 5000 }
                    ]
                });
            }
            renderVariants();
        };

        // Live input updates
        variantsContainer.oninput = function(e) {
            const target = e.target;
            const g = target.dataset.g;
            const o = target.dataset.o;

            if (target.classList.contains('var-group-name')) {
                if (variants[g]) {
                    variants[g].group_name = target.value;
                    // Update header title in card
                    const headerTitle = target.closest('.card')?.querySelector('.card-header .fw-bold');
                    if (headerTitle) {
                        headerTitle.innerText = target.value || 'Grup Varian Baru';
                    }
                }
            } else if (target.classList.contains('var-group-type')) {
                if (variants[g]) {
                    variants[g].type = target.value;
                }
            } else if (target.classList.contains('var-opt-name')) {
                if (variants[g] && variants[g].options && variants[g].options[o]) {
                    variants[g].options[o].name = target.value;
                }
            } else if (target.classList.contains('var-opt-price')) {
                if (variants[g] && variants[g].options && variants[g].options[o]) {
                    variants[g].options[o].price = parseInt(target.value || 0, 10);
                }
            }
            syncInput();
        };

        // Change event for selects
        variantsContainer.onchange = function(e) {
            if (e.target.classList.contains('var-group-type')) {
                const g = e.target.dataset.g;
                if (variants[g]) {
                    variants[g].type = e.target.value;
                    renderVariants();
                }
            }
        };

        // Click delegation for buttons
        variantsContainer.onclick = function(e) {
            const addOptBtn = e.target.closest('.add-opt');
            if (addOptBtn) {
                const g = addOptBtn.dataset.g;
                if (variants[g]) {
                    variants[g].options = variants[g].options || [];
                    variants[g].options.push({ name: '', price: 0 });
                    renderVariants();
                }
                return;
            }

            const removeOptBtn = e.target.closest('.remove-opt');
            if (removeOptBtn) {
                const g = removeOptBtn.dataset.g;
                const o = parseInt(removeOptBtn.dataset.o, 10);
                if (variants[g] && variants[g].options) {
                    variants[g].options.splice(o, 1);
                    renderVariants();
                }
                return;
            }

            const moveUpBtn = e.target.closest('.move-opt-up');
            if (moveUpBtn) {
                const g = moveUpBtn.dataset.g;
                const o = parseInt(moveUpBtn.dataset.o, 10);
                if (o > 0 && variants[g] && variants[g].options) {
                    const temp = variants[g].options[o];
                    variants[g].options[o] = variants[g].options[o - 1];
                    variants[g].options[o - 1] = temp;
                    renderVariants();
                }
                return;
            }

            const moveDownBtn = e.target.closest('.move-opt-down');
            if (moveDownBtn) {
                const g = moveDownBtn.dataset.g;
                const o = parseInt(moveDownBtn.dataset.o, 10);
                if (variants[g] && variants[g].options && o < variants[g].options.length - 1) {
                    const temp = variants[g].options[o];
                    variants[g].options[o] = variants[g].options[o + 1];
                    variants[g].options[o + 1] = temp;
                    renderVariants();
                }
                return;
            }

            const removeGroupBtn = e.target.closest('.remove-group');
            if (removeGroupBtn) {
                const g = parseInt(removeGroupBtn.dataset.g, 10);
                if (confirm('Hapus grup varian ini beserta seluruh opsinya?')) {
                    variants.splice(g, 1);
                    renderVariants();
                }
                return;
            }
        };

        // Form submission sanitize
        if (formProduk) {
            formProduk.onsubmit = function() {
                // Filter out empty groups or groups without valid names
                const cleaned = variants
                    .filter(g => g.group_name && g.group_name.trim() !== '')
                    .map(g => ({
                        group_name: g.group_name.trim(),
                        type: g.type || 'single',
                        options: (g.options || [])
                            .filter(o => o.name && o.name.trim() !== '')
                            .map(o => ({
                                name: o.name.trim(),
                                price: parseInt(o.price, 10) || 0
                            }))
                    }))
                    .filter(g => g.options.length > 0);

                inputVariants.value = JSON.stringify(cleaned);
            };
        }

        // Initial render
        renderVariants();
    }

    // AI Description generator
    window.generateDesc = function() {
        const namaInput = document.querySelector('input[name="nama_menu"]');
        const namaMenu = namaInput ? namaInput.value.trim() : '';
        if (!namaMenu) {
            alert('Silakan isi Nama Produk terlebih dahulu!');
            if (namaInput) namaInput.focus();
            return;
        }

        const btn = document.getElementById('btn-ai-desc');
        const status = document.getElementById('ai-status');
        const descInput = document.getElementById('deskripsi-input');

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sedang memikirkan...';
        }
        if (status) status.innerHTML = '';

        fetch('{{ route('admin.menu.ai_description') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nama_menu: namaMenu })
        })
        .then(res => res.json())
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-stars"></i> Generate dengan AI';
            }
            if (data.error) {
                if (status) status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${data.error}</span>`;
            } else if (data.description) {
                if (descInput) descInput.value = data.description;
                if (status) status.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> Deskripsi berhasil di-generate oleh AI</span>`;
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-stars"></i> Generate dengan AI';
            }
            if (status) status.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> Gagal terhubung ke server AI.</span>`;
        });
    };

    // Execute immediately or on DOM ready / SPA load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMenuVariantBuilder);
    } else {
        initMenuVariantBuilder();
    }

    // Also listen to SPA page-loaded event
    window.addEventListener('admin:page-loaded', function() {
        initMenuVariantBuilder();
    });
})();
</script>
@endsection
