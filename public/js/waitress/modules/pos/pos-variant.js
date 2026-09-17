/**
 * Master Cafe POS - POS Module: Variant & Topping Engine
 * File: public/js/waitress/modules/pos/pos-variant.js
 * Deskripsi: Menangani dialog opsi varian produk, kuantitas topping porsi,
 *            kalkulasi harga dinamis (timbangan), dan validasi pilihan single/multiple.
 */

(function () {
    'use strict';

    let currentSelectedMenu = null;
    let currentModalQty = 1;
    let currentDynamicBasePrice = 0;

    window.changeModalQty = function (delta) {
        currentModalQty += delta;
        if (currentModalQty < 1) currentModalQty = 1;
        const el = document.getElementById('modal-qty-display');
        if (el) el.innerText = currentModalQty;
        window.calculateVariantPrice();
    };

    window.openVariantModal = function (id) {
        const allMenus = window.allMenus || [];
        const menu = allMenus.find(m => m.id === id);
        if (!menu) return;

        let dynamicPrice = null;
        if (menu.is_dynamic_price || Number(menu.harga) === 0) {
            let inputPrice = prompt(`Masukkan harga aktual untuk menu: ${menu.nama_menu}\n(Contoh: harga berdasarkan timbangan ikan)`);
            if (inputPrice === null || inputPrice === '') return;
            dynamicPrice = parseFloat(inputPrice);
            if (isNaN(dynamicPrice) || dynamicPrice < 0) {
                alert("Harga yang dimasukkan tidak valid!");
                return;
            }
            currentDynamicBasePrice = dynamicPrice;
        } else {
            currentDynamicBasePrice = parseFloat(menu.harga);
        }

        let variants = [];
        if (menu.variants_json) {
            try { variants = JSON.parse(menu.variants_json); } catch (e) { }
        }

        if (variants.length === 0) {
            // Langsung tambah ke cart jika tidak ada varian
            if (typeof window.addToCart === 'function') {
                window.addToCart(menu.id, menu.nama_menu, currentDynamicBasePrice, []);
            }
            return;
        }

        currentSelectedMenu = menu;
        const titleEl = document.getElementById('variantModalTitle');
        if (titleEl) titleEl.innerText = menu.nama_menu;

        let html = '';
        variants.forEach((group, gIndex) => {
            html += `<div class="mb-3">
                        <label class="fw-bold d-block mb-2">${group.group_name}</label>`;

            group.options.forEach((opt, oIndex) => {
                const isMultiple = group.type === 'multiple';
                const inputName = `var_group_${gIndex}`;
                const inputId = `var_${gIndex}_${oIndex}`;
                const priceText = opt.price > 0 ? `(+Rp ${opt.price.toLocaleString('id-ID')})` : '';

                if (isMultiple) {
                    html += `
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="d-block">${opt.name}</span>
                                <span class="text-white-50 small">${priceText}</span>
                            </div>
                            <div class="input-group input-group-sm" style="width: 110px; flex-wrap: nowrap;">
                                <button class="btn btn-outline-secondary var-qty-btn btn-touch" type="button" style="width: 32px; padding: 0;" onclick="changeToppingQty('${inputId}', -1)"><i class="bi bi-dash"></i></button>
                                <input type="number" class="form-control text-white border-secondary text-center px-0 var-option-qty" id="${inputId}_qty" 
                                       data-gname="${group.group_name}" data-oname="${opt.name}" data-price="${opt.price}" 
                                       value="0" min="0" readonly style="background-color: transparent;">
                                <button class="btn btn-outline-secondary var-qty-btn btn-touch" type="button" style="width: 32px; padding: 0;" onclick="changeToppingQty('${inputId}', 1)"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="form-check mb-1">
                            <input class="form-check-input var-option-input" type="radio" name="${inputName}" id="${inputId}" 
                                   data-gname="${group.group_name}" data-oname="${opt.name}" data-price="${opt.price}" onchange="calculateVariantPrice()">
                            <label class="form-check-label d-flex justify-content-between" for="${inputId}">
                                <span>${opt.name}</span>
                                <span class="text-white-50 small">${priceText}</span>
                            </label>
                        </div>
                    `;
                }
            });
            html += `</div>`;
        });

        const contentEl = document.getElementById('variantModalContent');
        if (contentEl) contentEl.innerHTML = html;

        currentModalQty = 1;
        const qtyEl = document.getElementById('modal-qty-display');
        if (qtyEl) qtyEl.innerText = currentModalQty;

        window.calculateVariantPrice();

        const modalEl = document.getElementById('variantModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const vModal = bootstrap.Modal.getOrCreateInstance
                ? bootstrap.Modal.getOrCreateInstance(modalEl)
                : (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl));
            vModal.show();
        }
    };

    window.changeToppingQty = function (inputId, delta) {
        const input = document.getElementById(inputId + '_qty');
        if (!input) return;
        let val = parseInt(input.value) + delta;
        if (val < 0) val = 0;
        input.value = val;
        window.calculateVariantPrice();
    };

    window.calculateVariantPrice = function () {
        if (!currentSelectedMenu) return 0;
        let unitPrice = currentDynamicBasePrice;

        document.querySelectorAll('.var-option-input:checked').forEach(input => {
            unitPrice += parseFloat(input.dataset.price);
        });

        document.querySelectorAll('.var-option-qty').forEach(input => {
            let qty = parseInt(input.value);
            if (qty > 0) {
                unitPrice += parseFloat(input.dataset.price) * qty;
            }
        });

        let total = unitPrice * currentModalQty;
        const priceEl = document.getElementById('variantModalPrice');
        if (priceEl) priceEl.innerText = 'Rp ' + total.toLocaleString('id-ID');
        return unitPrice;
    };

    window.confirmVariantSelection = function () {
        if (!currentSelectedMenu) return;

        let selectedVariants = [];
        document.querySelectorAll('.var-option-input:checked').forEach(input => {
            selectedVariants.push({
                group: input.dataset.gname,
                name: input.dataset.oname,
                price: parseFloat(input.dataset.price),
                qty: 1
            });
        });

        document.querySelectorAll('.var-option-qty').forEach(input => {
            let qty = parseInt(input.value);
            if (qty > 0) {
                selectedVariants.push({
                    group: input.dataset.gname,
                    name: input.dataset.oname,
                    price: parseFloat(input.dataset.price),
                    qty: qty
                });
            }
        });

        // Validasi radio (harus pilih satu jika grup bertipe single)
        let variantsDef = JSON.parse(currentSelectedMenu.variants_json || '[]');
        for (let i = 0; i < variantsDef.length; i++) {
            if (variantsDef[i].type === 'single') {
                const hasSelected = selectedVariants.find(sv => sv.group === variantsDef[i].group_name);
                if (!hasSelected) {
                    alert(`Silakan pilih salah satu opsi dari ${variantsDef[i].group_name}!`);
                    return;
                }
            }
        }

        let finalPrice = window.calculateVariantPrice() / currentModalQty;
        if (typeof window.addToCart === 'function') {
            window.addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty);
        }

        const modalEl = document.getElementById('variantModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const vModal = bootstrap.Modal.getInstance(modalEl);
            if (vModal) vModal.hide();
        }
    };

    window.addAnotherVariantSelection = function () {
        if (!currentSelectedMenu) return;

        let selectedVariants = [];
        document.querySelectorAll('.var-option-input:checked').forEach(input => {
            selectedVariants.push({
                group: input.dataset.gname,
                name: input.dataset.oname,
                price: parseFloat(input.dataset.price),
                qty: 1
            });
        });

        document.querySelectorAll('.var-option-qty').forEach(input => {
            let qty = parseInt(input.value);
            if (qty > 0) {
                selectedVariants.push({
                    group: input.dataset.gname,
                    name: input.dataset.oname,
                    price: parseFloat(input.dataset.price),
                    qty: qty
                });
            }
        });

        // Validasi radio (harus pilih satu jika grup bertipe single)
        let variantsDef = JSON.parse(currentSelectedMenu.variants_json || '[]');
        for (let i = 0; i < variantsDef.length; i++) {
            if (variantsDef[i].type === 'single') {
                const hasSelected = selectedVariants.find(sv => sv.group === variantsDef[i].group_name);
                if (!hasSelected) {
                    alert(`Silakan pilih salah satu opsi dari ${variantsDef[i].group_name}!`);
                    return;
                }
            }
        }

        let finalPrice = window.calculateVariantPrice() / currentModalQty;
        if (typeof window.addToCart === 'function') {
            window.addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty);
        }

        // Reset Inputs
        document.querySelectorAll('.var-option-input').forEach(input => {
            if (input.type === 'radio' || input.type === 'checkbox') input.checked = false;
        });
        document.querySelectorAll('.var-option-qty').forEach(input => {
            input.value = 0;
        });

        currentModalQty = 1;
        const qtyEl = document.getElementById('modal-qty-display');
        if (qtyEl) qtyEl.innerText = currentModalQty;
        window.calculateVariantPrice();

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if (alertContainer) {
            alertContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show p-2 mb-3" role="alert" style="font-size:0.85rem;">
                <i class="bi bi-check-circle-fill me-1"></i> Porsi sebelumnya berhasil ditambahkan! Silakan pilih varian untuk porsi berikutnya.
                <button type="button" class="btn-close p-2 btn-touch" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
        }
    };

})();
