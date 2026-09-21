/**
 * Master Cafe POS - Consumer Menu Catalog Module
 * File: public/js/konsumen/modules/menu-catalog.js
 * Deskripsi: Menangani filter kategori, expandable detail card, modal varian hidangan, dan kalkulasi harga dinamis.
 */

(function () {
    'use strict';

    const config = window.ConsumerMenuConfig || {};
    const allMenus = config.allMenus || [];
    let currentSelectedMenu = null;
    let currentModalQty = 1;

    // --- 1. MODAL KUANTITAS & KATALOG FILTER ---
    window.changeModalQty = function (delta) {
        currentModalQty += delta;
        if (currentModalQty < 1) currentModalQty = 1;
        const el = document.getElementById('modal-qty-display');
        if (el) el.innerText = currentModalQty;
        window.calculateVariantPrice();
    };

    // --- 1. MODAL KUANTITAS & KATALOG FILTER (2-TIER HIERARCHICAL) ---
    let currentMainCat = 'semua';
    let currentSubCat = 'semua';

    function getIconForSubCategory(sub) {
        const subLower = (sub || '').toLowerCase();
        if (subLower.includes('kopi') || subLower.includes('coffee')) return 'bi-cup-hot-fill';
        if (subLower.includes('teh') || subLower.includes('tea')) return 'bi-cup-straw';
        if (subLower.includes('mojito')) return 'bi-tsunami';
        if (subLower.includes('jus')) return 'bi-droplet-fill';
        if (subLower.includes('non-coffee') || subLower.includes('blend')) return 'bi-cup';
        if (subLower.includes('ayam')) return 'bi-egg-fried';
        if (subLower.includes('seafood') || subLower.includes('ikan') || subLower.includes('udang')) return 'bi-water';
        if (subLower.includes('nasi')) return 'bi-fire';
        if (subLower.includes('mie') || subLower.includes('pasta')) return 'bi-cup-hot';
        if (subLower.includes('sayur') || subLower.includes('lauk')) return 'bi-flower1';
        if (subLower.includes('cemilan') || subLower.includes('snack')) return 'bi-cookie';
        if (subLower.includes('spesial') || subLower.includes('baru')) return 'bi-star-fill';
        return 'bi-tag';
    }

    window.selectMainCategory = function(mainCat, btn) {
        currentMainCat = (mainCat || 'semua').toLowerCase();
        currentSubCat = 'semua';

        document.querySelectorAll('.btn-main-filter').forEach(b => {
            b.classList.remove('active');
            b.style.backgroundColor = '';
            b.style.color = '';
            b.style.borderColor = 'rgba(255,255,255,0.2)';
        });

        if (btn) {
            btn.classList.add('active');
            btn.style.backgroundColor = '#c08e5c';
            btn.style.color = 'white';
            btn.style.borderColor = '#c08e5c';
        }

        renderSubCategoryPills();
        applyHierarchicalMenuFilter();
    };

    window.selectSubCategory = function(subCat, btn) {
        currentSubCat = (subCat || 'semua').toLowerCase();

        document.querySelectorAll('.btn-sub-filter').forEach(b => {
            b.classList.remove('active');
            b.style.backgroundColor = 'rgba(255,255,255,0.05)';
            b.style.color = '#e2e8f0';
            b.style.borderColor = 'rgba(255,255,255,0.15)';
        });

        if (btn) {
            btn.classList.add('active');
            btn.style.backgroundColor = 'rgba(192, 142, 92, 0.2)';
            btn.style.color = '#c08e5c';
            btn.style.borderColor = 'rgba(192, 142, 92, 0.6)';
        }

        applyHierarchicalMenuFilter();
    };

    function renderSubCategoryPills() {
        const container = document.getElementById('sub-category-pills-container');
        if (!container) return;

        const data = window.subCategoryData || {};
        let subs = [];

        if (currentMainCat === 'makanan') {
            subs = data.makanan || [];
            container.style.display = 'flex';
        } else if (currentMainCat === 'minuman') {
            subs = data.minuman || [];
            container.style.display = 'flex';
        } else {
            container.style.display = 'none';
            return;
        }

        let html = `
            <button type="button" class="btn btn-sm btn-sub-filter active rounded-pill px-3 py-1 text-white flex-shrink-0" 
                    onclick="selectSubCategory('semua', this)"
                    style="background-color: rgba(192, 142, 92, 0.2); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.6); font-size: 0.78rem;">
                Semua ${currentMainCat === 'makanan' ? 'Makanan' : 'Minuman'}
            </button>
        `;

        subs.forEach(sub => {
            const safeSub = sub.replace(/'/g, "\\'");
            html += `
                <button type="button" class="btn btn-sm btn-sub-filter rounded-pill px-3 py-1 flex-shrink-0" 
                        onclick="selectSubCategory('${safeSub}', this)"
                        style="background-color: rgba(255,255,255,0.05); color: #e2e8f0; border: 1px solid rgba(255,255,255,0.15); font-size: 0.78rem;">
                    ${sub}
                </button>
            `;
        });

        container.innerHTML = html;
        container.scrollLeft = 0;
    }

    function applyHierarchicalMenuFilter() {
        requestAnimationFrame(() => {
            document.querySelectorAll('.menu-item').forEach(item => {
                const itemCat = (item.getAttribute('data-kategori') || '').toLowerCase();
                const itemSub = (item.getAttribute('data-subkategori') || '').toLowerCase();

                const matchMain = (currentMainCat === 'semua' || itemCat === currentMainCat);
                const matchSub = (currentSubCat === 'semua' || itemSub === currentSubCat);

                if (matchMain && matchSub) {
                    item.style.removeProperty('display');
                    item.classList.remove('d-none');
                } else {
                    item.style.setProperty('display', 'none', 'important');
                    item.classList.add('d-none');
                }
            });
        });
    }

    // Backwards compatibility for single filter calls
    window.filterMenu = function(category, btn) {
        window.selectMainCategory(category, btn);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderSubCategoryPills);
    } else {
        renderSubCategoryPills();
    }

    // --- 2. EXPANDABLE CARD & CATATAN ---
    window.toggleConsumerMenuDesc = function (id) {
        const card = document.getElementById('consumer-menu-card-' + id);
        const icon = document.getElementById('desc-icon-' + id);
        const text = document.getElementById('desc-text-' + id);
        if (!card) return;

        const isExpanded = card.classList.toggle('is-expanded');
        if (isExpanded) {
            if (icon) icon.className = 'bi bi-chevron-up';
            if (text) text.innerText = 'Tutup';
        } else {
            if (icon) icon.className = 'bi bi-chevron-down';
            if (text) text.innerText = 'Detail';
        }
    };

    window.handleMenuNoteChange = function (id, val) {
        if (typeof window.updateCatatan === 'function') {
            window.updateCatatan(id, val);
            if (typeof window.updateCartUI === 'function') {
                window.updateCartUI();
            }
        }
    };

    window.handleAddToCart = function (id) {
        const menuList = (window.ConsumerMenuConfig && window.ConsumerMenuConfig.allMenus) ? window.ConsumerMenuConfig.allMenus : allMenus;
        const menu = menuList.find(m => m.id === id);
        if (!menu) return;

        if (!menu.is_available) {
            alert('Mohon maaf, menu ' + menu.nama_menu + ' sedang habis.');
            return;
        }

        if (menu.is_dynamic_price || Number(menu.harga) === 0) {
            if (typeof window.openDynamicPriceNotice === 'function') {
                window.openDynamicPriceNotice(id);
            }
            return;
        }

        const noteInput = document.getElementById('menu-note-' + id);
        const note = noteInput ? noteInput.value.trim() : '';

        if (menu.variants && menu.variants.length > 0) {
            if (typeof window.openVariantModal === 'function') {
                window.openVariantModal(id);
                setTimeout(() => {
                    const modalCatatanEl = document.getElementById('variantModalCatatan');
                    if (modalCatatanEl && note) {
                        modalCatatanEl.value = note;
                    }
                }, 50);
            }
        } else {
            if (typeof window.addToCart === 'function') {
                window.addToCart(menu.id, menu.nama_menu, Number(menu.harga), [], 1, note);
            }
        }
    };

    // --- 3. MODAL VARIAN ENGINE ---
    window.openVariantModal = function (id) {
        const menuList = (window.ConsumerMenuConfig && window.ConsumerMenuConfig.allMenus) ? window.ConsumerMenuConfig.allMenus : allMenus;
        currentSelectedMenu = menuList.find(m => m.id === id);
        if (!currentSelectedMenu) return;
        if (!currentSelectedMenu.is_available) {
            alert('Mohon maaf, menu ' + currentSelectedMenu.nama_menu + ' sedang habis.');
            return;
        }

        currentModalQty = 1;
        const qtyEl = document.getElementById('modal-qty-display');
        if (qtyEl) qtyEl.innerText = currentModalQty;

        const catatanEl = document.getElementById('variantModalCatatan');
        if (catatanEl) catatanEl.value = '';

        const titleEl = document.getElementById('variantModalMenuTitle');
        if (titleEl) titleEl.innerText = currentSelectedMenu.nama_menu;

        const priceEl = document.getElementById('variantModalMenuPrice');
        if (priceEl) priceEl.innerText = (currentSelectedMenu.is_dynamic_price || Number(currentSelectedMenu.harga) === 0)
            ? 'Sesuai Timbangan'
            : 'Rp ' + Number(currentSelectedMenu.harga).toLocaleString('id-ID');

        const descEl = document.getElementById('variantModalMenuDesc');
        if (descEl) descEl.innerText = currentSelectedMenu.deskripsi || 'Hidangan istimewa racikan Master Cafe dengan kualitas bahan pilihan terbaik.';

        const catEl = document.getElementById('variantModalCategoryBadge');
        if (catEl) catEl.innerText = (currentSelectedMenu.kategori ? currentSelectedMenu.kategori.toUpperCase() : 'MENU');

        const stockEl = document.getElementById('variantModalStockBadge');
        if (stockEl) {
            if (currentSelectedMenu.is_available) {
                stockEl.style.background = 'rgba(72, 187, 120, 0.15)';
                stockEl.style.color = '#48bb78';
                stockEl.style.border = '1px solid rgba(72, 187, 120, 0.3)';
                stockEl.innerHTML = `<i class="bi bi-check-circle me-1"></i> Tersedia`;
            } else {
                stockEl.style.background = 'rgba(245, 101, 101, 0.15)';
                stockEl.style.color = '#f56565';
                stockEl.style.border = '1px solid rgba(245, 101, 101, 0.3)';
                stockEl.innerHTML = `<i class="bi bi-x-circle me-1"></i> Habis`;
            }
        }

        const imgEl = document.getElementById('variantModalImg');
        if (imgEl) {
            imgEl.onerror = function () { this.onerror = null; this.src = '/images/logo.png'; };
            imgEl.src = currentSelectedMenu.image_url || (currentSelectedMenu.image ? (currentSelectedMenu.image.startsWith('http') ? currentSelectedMenu.image : '/storage/' + currentSelectedMenu.image) : '/images/logo.png');
        }

        let content = '';
        if (currentSelectedMenu.variants && currentSelectedMenu.variants.length > 0) {
            content += `<h6 class="fw-bold mb-3">Pilih Varian (Opsional)</h6>`;
            currentSelectedMenu.variants.forEach(variant => {
                if (variant.jenis === 'single') {
                    content += `
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">${variant.nama_varian}</label>
                            <div class="d-flex flex-wrap gap-2">
                                ${variant.opsi.map((opsi) => `
                                    <input type="radio" class="btn-check var-option-input" name="variant_${variant.id}" id="opt_${opsi.id}" value="${opsi.id}" data-price="${opsi.harga_tambahan}" data-name="${opsi.nama_opsi}" onchange="calculateVariantPrice()">
                                    <label class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm var-option-label text-white" for="opt_${opsi.id}" style="border: 1px solid #21262d;">
                                        ${opsi.nama_opsi} ${opsi.harga_tambahan > 0 ? '(+Rp ' + opsi.harga_tambahan.toLocaleString('id-ID') + ')' : ''}
                                    </label>
                                `).join('')}
                            </div>
                        </div>`;
                } else {
                    content += `
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">${variant.nama_varian}</label>
                            <div class="d-flex flex-column gap-2">
                                ${variant.opsi.map(opsi => `
                                    <div class="d-flex justify-content-between align-items-center p-2 rounded-3" style="background-color: rgba(255,255,255,0.03); border: 1px solid #21262d;">
                                        <label class="form-check-label text-white flex-grow-1" for="opt_chk_${opsi.id}">
                                            ${opsi.nama_opsi} <small class="text-muted d-block">${opsi.harga_tambahan > 0 ? '+Rp ' + opsi.harga_tambahan.toLocaleString('id-ID') : 'Gratis'}</small>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-outline-secondary var-qty-btn" type="button" style="width: 32px; padding: 0;" onclick="changeToppingQty('qty_opt_${opsi.id}', -1, ${opsi.harga_tambahan}, this)"><i class="bi bi-dash"></i></button>
                                            <input type="number" class="form-control form-control-sm text-center border-0 bg-transparent text-white var-option-qty fw-bold" id="qty_opt_${opsi.id}" data-id="${opsi.id}" data-price="${opsi.harga_tambahan}" data-name="${opsi.nama_opsi}" value="0" readonly style="width: 40px;">
                                            <button class="btn btn-outline-secondary var-qty-btn" type="button" style="width: 32px; padding: 0;" onclick="changeToppingQty('qty_opt_${opsi.id}', 1, ${opsi.harga_tambahan}, this)"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>`;
                }
            });
        }

        let container = document.getElementById('variantModalContent');
        const btnTextEl = document.getElementById('btnAnotherVariantText');
        if (btnTextEl) {
            if (currentSelectedMenu.variants && currentSelectedMenu.variants.length > 0) {
                btnTextEl.innerText = 'Beda Racikan / Varian';
            } else {
                btnTextEl.innerText = 'Beda Catatan';
            }
        }

        if (container) {
            if (content === '') {
                container.innerHTML = '<p class="text-muted my-2 text-center small"><i class="bi bi-check-circle me-1"></i> Menu siap disajikan tanpa tambahan varian khusus.</p>';
            } else {
                container.innerHTML = content;
            }
        }

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if (alertContainer) alertContainer.innerHTML = '';

        window.calculateVariantPrice();
        const modalEl = document.getElementById('variantModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            let myModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            myModal.show();
        }
    };

    window.changeToppingQty = function (inputId, delta, price, btnEl) {
        let input = document.getElementById(inputId);
        if (!input) return;
        let val = parseInt(input.value) + delta;
        if (val < 0) val = 0;
        input.value = val;

        if (btnEl && btnEl.parentElement && btnEl.parentElement.parentElement) {
            if (val > 0) {
                btnEl.parentElement.parentElement.style.borderColor = '#c08e5c';
            } else {
                btnEl.parentElement.parentElement.style.borderColor = '#21262d';
            }
        }

        window.calculateVariantPrice();
    };

    window.calculateVariantPrice = function () {
        if (!currentSelectedMenu) return;
        let base = currentSelectedMenu.harga;
        let additional = 0;

        document.querySelectorAll('.var-option-input:checked').forEach(el => {
            additional += parseFloat(el.getAttribute('data-price') || 0);
            if (el.nextElementSibling) {
                el.nextElementSibling.style.borderColor = '#c08e5c';
                el.nextElementSibling.style.color = '#c08e5c';
                el.nextElementSibling.classList.replace('btn-outline-secondary', 'btn-outline-primary');
            }
        });
        document.querySelectorAll('.var-option-input:not(:checked)').forEach(el => {
            if (el.nextElementSibling) {
                el.nextElementSibling.style.borderColor = '#21262d';
                el.nextElementSibling.style.color = 'white';
                el.nextElementSibling.classList.replace('btn-outline-primary', 'btn-outline-secondary');
            }
        });

        document.querySelectorAll('.var-option-qty').forEach(el => {
            let q = parseInt(el.value);
            if (q > 0) {
                additional += q * parseFloat(el.getAttribute('data-price') || 0);
            }
        });

        let total = (base + additional) * currentModalQty;
        const isDynamic = currentSelectedMenu.is_dynamic_price || Number(currentSelectedMenu.harga) === 0;
        const priceEl = document.getElementById('variantModalPrice');
        if (priceEl) {
            priceEl.innerText = (isDynamic && total === 0) ? 'Sesuai Timbangan' : 'Rp ' + total.toLocaleString('id-ID');
        }
    };

    window.confirmVariantSelection = function () {
        window.addAnotherVariantSelection();
        const modalEl = document.getElementById('variantModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const myModal = bootstrap.Modal.getInstance(modalEl);
            if (myModal) myModal.hide();
        }
    };

    window.addAnotherVariantSelection = function () {
        if (!currentSelectedMenu) return;

        let selectedVariants = [];
        document.querySelectorAll('.var-option-input:checked').forEach(el => {
            selectedVariants.push({
                id: el.value,
                name: el.getAttribute('data-name'),
                price: parseFloat(el.getAttribute('data-price')),
                qty: 1
            });
        });

        document.querySelectorAll('.var-option-qty').forEach(el => {
            let q = parseInt(el.value);
            if (q > 0) {
                selectedVariants.push({
                    id: el.getAttribute('data-id'),
                    name: el.getAttribute('data-name'),
                    price: parseFloat(el.getAttribute('data-price')),
                    qty: q
                });
            }
        });

        let additional = selectedVariants.reduce((sum, v) => sum + (v.price * v.qty), 0);
        let finalPrice = currentSelectedMenu.harga + additional;

        let catatan = document.getElementById('variantModalCatatan') ? document.getElementById('variantModalCatatan').value.trim() : '';
        if (typeof window.addToCart === 'function') {
            window.addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty, catatan);
        }

        document.querySelectorAll('.var-option-input').forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') input.checked = false;
        });
        document.querySelectorAll('.var-option-qty').forEach(input => {
            input.value = 0;
            if (input.parentElement && input.parentElement.parentElement) {
                input.parentElement.parentElement.style.borderColor = '#21262d';
            }
        });

        if (document.getElementById('variantModalCatatan')) {
            document.getElementById('variantModalCatatan').value = '';
        }

        currentModalQty = 1;
        const qtyEl = document.getElementById('modal-qty-display');
        if (qtyEl) qtyEl.innerText = currentModalQty;
        window.calculateVariantPrice();

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if (alertContainer) {
            alertContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show p-2 mb-3" role="alert" style="font-size:0.85rem; background-color: rgba(72,187,120,0.1); color: #48bb78; border-color: rgba(72,187,120,0.2);">
                <i class="bi bi-check-circle-fill me-1"></i> Porsi berhasil ditambahkan! Silakan pilih untuk porsi berikutnya.
                <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
        }
    };

    function initConsumerSubPillsWheel() {
        const subContainer = document.getElementById('sub-category-pills-container');
        if (subContainer) {
            subContainer.addEventListener('wheel', function(e) {
                if (e.deltaY !== 0) {
                    e.preventDefault();
                    subContainer.scrollLeft += e.deltaY;
                }
            }, { passive: false });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initConsumerSubPillsWheel);
    } else {
        initConsumerSubPillsWheel();
    }
})();
