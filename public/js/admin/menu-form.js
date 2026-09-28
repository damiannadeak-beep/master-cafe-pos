/**
 * Master Cafe POS - Admin Menu Form & Variant Builder Engine
 * File: public/js/admin/menu-form.js
 */

(function () {
    'use strict';

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
        } catch (e) {
            console.error('Failed to parse variants_json:', e);
            variants = [];
        }
        if (!Array.isArray(variants)) {
            variants = [];
        }

        let syncTimeout = null;
        function syncInput() {
            clearTimeout(syncTimeout);
            syncTimeout = setTimeout(() => {
                if (inputVariants) {
                    inputVariants.value = JSON.stringify(variants);
                }
            }, 100);
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
            addGroupBtn.onclick = function () {
                variants.push({
                    group_name: '',
                    type: 'single',
                    options: [{ name: '', price: 0 }]
                });
                renderVariants();
                const inputs = variantsContainer.querySelectorAll('.var-group-name');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            };
        }

        // Presets handler globally exposed on window
        window.addPresetVariant = function (type) {
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
                        { name: 'Level 1 (Pedas Sedang)', price: 0 },
                        { name: 'Level 2 (Pedas Ekstra)', price: 1000 },
                        { name: 'Level 3 (Super Pedas)', price: 2000 }
                    ]
                });
            } else if (type === 'suhu') {
                variants.push({
                    group_name: 'Pilihan Suhu',
                    type: 'single',
                    options: [
                        { name: 'Dingin (Ice)', price: 0 },
                        { name: 'Hangat / Panas (Hot)', price: 0 }
                    ]
                });
            } else if (type === 'toping') {
                variants.push({
                    group_name: 'Tambahan Toping',
                    type: 'multiple',
                    options: [
                        { name: 'Extra Keju Cheddar', price: 3000 },
                        { name: 'Extra Telur Ceplok', price: 4000 },
                        { name: 'Extra Sambal Terasi', price: 2000 },
                        { name: 'Extra Kerupuk Kaleng', price: 1500 }
                    ]
                });
            }
            renderVariants();
        };

        // Event delegation for dynamic variant interactions
        variantsContainer.addEventListener('input', function (e) {
            const target = e.target;
            const g = target.getAttribute('data-g');
            const o = target.getAttribute('data-o');

            if (target.classList.contains('var-group-name') && g !== null) {
                if (variants[g]) variants[g].group_name = target.value;
                syncInput();
            } else if (target.classList.contains('var-opt-name') && g !== null && o !== null) {
                if (variants[g] && variants[g].options[o]) variants[g].options[o].name = target.value;
                syncInput();
            } else if (target.classList.contains('var-opt-price') && g !== null && o !== null) {
                if (variants[g] && variants[g].options[o]) variants[g].options[o].price = parseFloat(target.value) || 0;
                syncInput();
            }
        });

        variantsContainer.addEventListener('change', function (e) {
            const target = e.target;
            const g = target.getAttribute('data-g');
            if (target.classList.contains('var-group-type') && g !== null) {
                if (variants[g]) {
                    variants[g].type = target.value;
                    renderVariants();
                }
            }
        });

        variantsContainer.addEventListener('click', function (e) {
            const target = e.target.closest('button');
            if (!target) return;

            const g = target.getAttribute('data-g');
            const o = target.getAttribute('data-o');

            if (target.classList.contains('remove-group') && g !== null) {
                if (confirm('Hapus grup varian ini?')) {
                    variants.splice(g, 1);
                    renderVariants();
                }
            } else if (target.classList.contains('add-opt') && g !== null) {
                if (variants[g]) {
                    variants[g].options.push({ name: '', price: 0 });
                    renderVariants();
                }
            } else if (target.classList.contains('remove-opt') && g !== null && o !== null) {
                if (variants[g] && variants[g].options) {
                    variants[g].options.splice(o, 1);
                    renderVariants();
                }
            } else if (target.classList.contains('move-opt-up') && g !== null && o !== null) {
                const idx = parseInt(o, 10);
                if (variants[g] && variants[g].options && idx > 0) {
                    const temp = variants[g].options[idx];
                    variants[g].options[idx] = variants[g].options[idx - 1];
                    variants[g].options[idx - 1] = temp;
                    renderVariants();
                }
            } else if (target.classList.contains('move-opt-down') && g !== null && o !== null) {
                const idx = parseInt(o, 10);
                if (variants[g] && variants[g].options && idx < variants[g].options.length - 1) {
                    const temp = variants[g].options[idx];
                    variants[g].options[idx] = variants[g].options[idx + 1];
                    variants[g].options[idx + 1] = temp;
                    renderVariants();
                }
            }
        });

        // Form submit safety sanitization
        if (formProduk) {
            formProduk.onsubmit = function () {
                const cleaned = variants
                    .filter(g => g.group_name && g.group_name.trim() !== '')
                    .map(g => ({
                        group_name: g.group_name.trim(),
                        type: g.type === 'multiple' ? 'multiple' : 'single',
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

        renderVariants();
    }

    // AI Description generator
    window.generateDesc = function () {
        const config = window.MenuFormConfig || {};
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

        fetch(config.aiDescriptionUrl || '/admin/menu/ai-description', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
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

    function initDynamicPriceToggle() {
        const isDynamicSwitch = document.getElementById('is_dynamic_price');
        const hargaInput = document.getElementById('harga-input');
        const hargaAsterisk = document.getElementById('harga-asterisk');
        const hargaDynamicNote = document.getElementById('harga-dynamic-note');

        if (!isDynamicSwitch || !hargaInput) return;

        function updateState() {
            if (isDynamicSwitch.checked) {
                hargaInput.removeAttribute('required');
                if (hargaAsterisk) hargaAsterisk.style.display = 'none';
                if (hargaDynamicNote) hargaDynamicNote.style.display = 'block';
                hargaInput.placeholder = 'Otomatis Sesuai Timbangan (0)';
            } else {
                hargaInput.setAttribute('required', 'required');
                if (hargaAsterisk) hargaAsterisk.style.display = 'inline';
                if (hargaDynamicNote) hargaDynamicNote.style.display = 'none';
                hargaInput.placeholder = 'Cth: 15000';
            }
        }

        isDynamicSwitch.addEventListener('change', updateState);
        updateState();
    }

    const subCategoryPresets = {
        'makanan': [
            { value: 'Seafood & Ikan', label: 'Seafood & Ikan' },
            { value: 'Olahan Ayam', label: 'Olahan Ayam' },
            { value: 'Nasi Goreng', label: 'Nasi Goreng' },
            { value: 'Mie & Pasta', label: 'Mie & Pasta' },
            { value: 'Sayur & Lauk', label: 'Sayur & Lauk Pendamping' },
            { value: 'Cemilan', label: 'Cemilan / Snack' },
            { value: 'Spesial & Menu Baru', label: 'Spesial & Menu Baru' }
        ],
        'minuman': [
            { value: 'Coffee', label: 'Coffee (Kopi)' },
            { value: 'Varian Teh', label: 'Varian Teh' },
            { value: 'Varian Mojito', label: 'Varian Mojito' },
            { value: 'Varian Jus', label: 'Varian Jus Buah' },
            { value: 'Non-Coffee & Blend', label: 'Non-Coffee & Blend' }
        ]
    };

    window.handleMainCategoryChange = function (mainCategory, initialSubValue = null) {
        const subSelect = document.getElementById('sub-kategori-select');
        const customInput = document.getElementById('sub-kategori-final-input');
        if (!subSelect || !customInput) return;

        const currentVal = initialSubValue !== null ? initialSubValue : customInput.value;
        const presets = subCategoryPresets[mainCategory] || [];

        let html = '<option value="">-- Pilih Sub-Kategori --</option>';
        let isMatched = false;

        presets.forEach(p => {
            const selected = (currentVal && currentVal.toLowerCase() === p.value.toLowerCase()) ? 'selected' : '';
            if (selected) isMatched = true;
            html += `<option value="${p.value}" ${selected}>${p.label}</option>`;
        });

        const isCustomSelected = (!isMatched && currentVal && currentVal.trim() !== '');
        html += `<option value="custom" ${isCustomSelected ? 'selected' : ''}>+ Tambah Sub-Kategori Baru...</option>`;
        subSelect.innerHTML = html;

        if (isCustomSelected) {
            customInput.style.display = 'block';
        } else {
            customInput.style.display = 'none';
            if (isMatched) {
                const selectedOpt = presets.find(p => p.value.toLowerCase() === currentVal.toLowerCase());
                if (selectedOpt) customInput.value = selectedOpt.value;
            }
        }
    };

    window.handleSubCategorySelectChange = function (selectEl) {
        const customInput = document.getElementById('sub-kategori-final-input');
        if (!customInput) return;
        if (selectEl.value === 'custom') {
            customInput.style.display = 'block';
            customInput.value = '';
            customInput.focus();
        } else {
            customInput.style.display = 'none';
            customInput.value = selectEl.value;
        }
    };

    function initSubCategoryForm() {
        const mainCatSelect = document.getElementById('kategori-select');
        const customInput = document.getElementById('sub-kategori-final-input');
        if (mainCatSelect && customInput) {
            handleMainCategoryChange(mainCatSelect.value, customInput.value);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initMenuVariantBuilder();
            initDynamicPriceToggle();
            initSubCategoryForm();
        });
    } else {
        initMenuVariantBuilder();
        initDynamicPriceToggle();
        initSubCategoryForm();
    }

    window.addEventListener('admin:page-loaded', function () {
        initMenuVariantBuilder();
        initDynamicPriceToggle();
        initSubCategoryForm();
    });
})();
