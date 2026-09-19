/**
 * Master Cafe POS - Consumer Menu Cart & Order Module
 * File: public/js/konsumen/modules/menu-cart.js
 * Deskripsi: Menangani keranjang belanja (cart), drawer UI, validasi WhatsApp guest, dan checkout pesanan.
 */

(function () {
    'use strict';

    const config = window.ConsumerMenuConfig || {};
    let cart = [];
    let isCartDrawerOpen = false;

    function getCsrfToken() {
        return config.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    // --- 1. KERANJANG BELANJA (CART) & ACCORDION DRAWER ---
    window.toggleCartDrawer = function () {
        if (isCartDrawerOpen) {
            window.closeCartDrawer();
        } else {
            window.openCartDrawer();
        }
    };

    window.openCartDrawer = function () {
        const collapse = document.getElementById('cartDrawerCollapse');
        const icon = document.getElementById('cart-chevron-icon');
        const text = document.getElementById('cart-toggle-text');
        const backdrop = document.getElementById('cartDrawerBackdrop');

        if (collapse) {
            collapse.style.maxHeight = '52vh';
            collapse.style.opacity = '1';
        }
        if (icon) {
            icon.style.transform = 'rotate(180deg)';
        }
        if (text) {
            text.innerText = 'Tutup Rincian';
        }
        if (backdrop && cart.length > 0) {
            backdrop.style.display = 'block';
            setTimeout(() => { backdrop.style.opacity = '1'; }, 10);
        }
        isCartDrawerOpen = true;
    };

    window.closeCartDrawer = function () {
        const collapse = document.getElementById('cartDrawerCollapse');
        const icon = document.getElementById('cart-chevron-icon');
        const text = document.getElementById('cart-toggle-text');
        const backdrop = document.getElementById('cartDrawerBackdrop');

        if (collapse) {
            collapse.style.maxHeight = '0';
            collapse.style.opacity = '0';
        }
        if (icon) {
            icon.style.transform = 'rotate(0deg)';
        }
        if (text) {
            text.innerText = 'Lihat Rincian';
        }
        if (backdrop) {
            backdrop.style.opacity = '0';
            setTimeout(() => { backdrop.style.display = 'none'; }, 300);
        }
        isCartDrawerOpen = false;
    };

    window.changeCartItemQty = function (index, delta) {
        if (cart[index] !== undefined) {
            cart[index].jumlah += delta;
            if (cart[index].jumlah <= 0) {
                cart.splice(index, 1);
            }
            window.updateCartUI();
        }
    };

    window.removeCartItemByIndex = function (index) {
        if (cart[index] !== undefined) {
            cart.splice(index, 1);
            window.updateCartUI();
        }
    };

    window.clearCart = function () {
        if (cart.length === 0) return;
        if (confirm('Yakin ingin mengosongkan keranjang pesanan?')) {
            cart = [];
            window.closeCartDrawer();
            window.updateCartUI();
        }
    };

    window.addToCart = function (id, name, price, variants = [], qty = 1, catatan = '') {
        const variantsString = JSON.stringify(variants);
        let itemIndex = cart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString && (i.catatan || '') === catatan);
        if (itemIndex !== -1) {
            cart[itemIndex].jumlah += qty;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: qty, catatan: catatan, variants: variants });
        }
        window.updateCartUI();
    };

    window.updateCatatan = function (id, val) {
        let item = cart.find(i => i.id_menu === id);
        if (item) {
            item.catatan = val;
        }
    };

    window.removeFromCart = function (id) {
        let itemIndex = cart.findIndex(i => i.id_menu === id);
        if (itemIndex !== -1) {
            if (cart[itemIndex].jumlah > 1) {
                cart[itemIndex].jumlah--;
            } else {
                cart.splice(itemIndex, 1);
            }
            window.updateCartUI();
        }
    };

    window.updateCartUI = function () {
        let total = 0;
        let qty = 0;

        document.querySelectorAll('[id^="qty-"]').forEach(el => el.innerText = '0');
        document.querySelectorAll('[id^="catatan-container-"]').forEach(el => el.style.display = 'none');

        let aggregatedQty = {};
        let aggregatedVariantsHtml = {};

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;

            if (!aggregatedQty[item.id_menu]) {
                aggregatedQty[item.id_menu] = 0;
                aggregatedVariantsHtml[item.id_menu] = '';
            }
            aggregatedQty[item.id_menu] += item.jumlah;
            let variantsHtml = '';
            if (item.variants && item.variants.length > 0) {
                const varText = item.variants.map(v => {
                    return (v.qty && v.qty > 1) ? `${v.qty}x ${v.name}` : v.name;
                }).join(', ');
                variantsHtml = `<div class="small text-white mb-1"><i class="bi bi-tags me-1"></i>${varText}</div>`;
            }
            if (item.catatan) {
                variantsHtml += `<div class="small fst-italic" style="color: #c08e5c;"><i class="bi bi-chat-text me-1 text-warning"></i>"${item.catatan}"</div>`;
            }
            if (variantsHtml !== '') {
                aggregatedVariantsHtml[item.id_menu] += `<div class="mb-1">${item.jumlah}x: ${variantsHtml}</div>`;
            }
        });

        Object.keys(aggregatedQty).forEach(menuId => {
            let qtyDisplay = document.getElementById('qty-' + menuId);
            if (qtyDisplay) qtyDisplay.innerText = aggregatedQty[menuId];

            let catatanContainer = document.getElementById('catatan-container-' + menuId);
            if (catatanContainer && aggregatedVariantsHtml[menuId] !== '') {
                catatanContainer.style.display = 'block';
                catatanContainer.innerHTML = aggregatedVariantsHtml[menuId];
            }
        });

        const drawerBadge = document.getElementById('cart-drawer-badge');
        if (drawerBadge) {
            drawerBadge.innerText = qty + ' Item';
        }

        const cartListContainer = document.getElementById('cartItemsList');
        if (cartListContainer) {
            if (cart.length === 0) {
                cartListContainer.innerHTML = `
                    <div class="text-center py-4 text-secondary small">
                        <i class="bi bi-basket2 fs-3 d-block mb-1 opacity-50"></i>
                        Belum ada menu yang dipilih
                    </div>`;
                if (isCartDrawerOpen) {
                    window.closeCartDrawer();
                }
            } else {
                let itemsHtml = '';
                cart.forEach((item, idx) => {
                    let varDetails = '';
                    if (item.variants && item.variants.length > 0) {
                        const vList = item.variants.map(v => (v.qty && v.qty > 1 ? `${v.qty}x ${v.name}` : v.name)).join(', ');
                        varDetails += `<div class="text-white-50 small mt-1" style="font-size: 0.74rem;"><i class="bi bi-tags me-1 text-warning"></i>${vList}</div>`;
                    }
                    if (item.catatan) {
                        varDetails += `<div class="fst-italic text-warning small mt-1" style="font-size: 0.74rem;"><i class="bi bi-chat-left-dots me-1"></i>"${item.catatan}"</div>`;
                    }

                    const subtotal = item.harga * item.jumlah;

                    itemsHtml += `
                        <div class="p-2 mb-2 rounded-3 d-flex align-items-center justify-content-between gap-2" style="background-color: #161b22; border: 1px solid #21262d;">
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-semibold text-white small text-truncate" style="font-size: 0.88rem;">${item.nama}</div>
                                ${varDetails}
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="fw-bold small" style="color: #c08e5c; font-size: 0.82rem;">Rp ${subtotal.toLocaleString('id-ID')}</span>
                                    ${item.jumlah > 1 ? `<small class="text-white-50" style="font-size: 0.72rem;">(Rp ${item.harga.toLocaleString('id-ID')} / porsi)</small>` : ''}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1 bg-dark border border-secondary border-opacity-25 rounded-pill p-1 flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                        style="width: 26px; height: 26px; font-size: 0.75rem;" 
                                        onclick="changeCartItemQty(${idx}, -1)" 
                                        title="${item.jumlah === 1 ? 'Hapus' : 'Kurangi'}">
                                    <i class="bi ${item.jumlah === 1 ? 'bi-trash3' : 'bi-dash'}"></i>
                                </button>
                                <span class="text-white fw-bold px-1 small" style="min-width: 22px; text-align: center; font-size: 0.85rem;">${item.jumlah}</span>
                                <button type="button" class="btn btn-sm btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center" 
                                        style="width: 26px; height: 26px; font-size: 0.75rem; background: var(--gradient-bronze); border: none;" 
                                        onclick="changeCartItemQty(${idx}, 1)" 
                                        title="Tambah">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>`;
                });
                cartListContainer.innerHTML = itemsHtml;
            }
        }

        let discount = 0;
        const promoSelect = document.getElementById('promo_id');
        if (promoSelect && promoSelect.value) {
            const option = promoSelect.options[promoSelect.selectedIndex];
            const pType = option.getAttribute('data-type');
            const pValue = parseFloat(option.getAttribute('data-value'));
            if (pType === 'discount') {
                if (pValue <= 100) {
                    discount = total * (pValue / 100);
                } else {
                    discount = pValue;
                }
            } else if (pType === 'package') {
                let packageMenus = JSON.parse(option.getAttribute('data-menus') || '[]');
                let packageNormalPrice = 0;
                let maxPackageCount = Infinity;

                let cartMap = {};
                cart.forEach(item => {
                    if (!cartMap[item.id_menu]) cartMap[item.id_menu] = 0;
                    cartMap[item.id_menu] += item.jumlah;
                });

                if (packageMenus.length === 0) maxPackageCount = 0;

                packageMenus.forEach(pm => {
                    let requiredQty = pm.jumlah;
                    let availableQty = cartMap[pm.id] || 0;
                    if (availableQty < requiredQty) {
                        maxPackageCount = 0;
                    } else {
                        maxPackageCount = Math.min(maxPackageCount, Math.floor(availableQty / requiredQty));
                    }
                    packageNormalPrice += (pm.harga * requiredQty);
                });

                if (maxPackageCount > 0 && maxPackageCount !== Infinity) {
                    let discountPerPackage = packageNormalPrice - pValue;
                    if (discountPerPackage < 0) discountPerPackage = 0;
                    discount = discountPerPackage * maxPackageCount;
                }
            }
            if (discount > total) discount = total;
        }

        let totalTagihan = total - discount;

        let tagihanHtml = 'Rp ' + totalTagihan.toLocaleString('id-ID');
        if (discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-muted small fs-6">Rp ${total.toLocaleString('id-ID')}</span><br>Rp ${totalTagihan.toLocaleString('id-ID')}`;
        }
        const cartTotalEl = document.getElementById('cart-total');
        if (cartTotalEl) cartTotalEl.innerHTML = tagihanHtml;

        const cartQtyEl = document.getElementById('cart-qty');
        if (cartQtyEl) cartQtyEl.innerText = qty + ' Item';
    };

    // Export internal cart array getter if needed
    window.getCartState = function () {
        return cart;
    };
})();
