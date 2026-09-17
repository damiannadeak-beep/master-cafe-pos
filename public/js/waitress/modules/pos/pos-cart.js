/**
 * Master Cafe POS - POS Module: Cart & Promo Engine
 * File: public/js/waitress/modules/pos/pos-cart.js
 * Deskripsi: Menangani state keranjang belanja (localStorage), penambahan item/varian,
 *            kalkulasi diskon promo, catatan per item, dan rendering daftar tagihan.
 */

(function () {
    'use strict';

    // Inisialisasi Cart dari Local Storage agar aman saat browser ter-refresh
    window.posCart = JSON.parse(localStorage.getItem('kasir_cart')) || [];

    window.addToCart = function (id, name, price, variants = [], qty = 1) {
        // Cek apakah item dengan menu_id dan varian yang SAMA persis sudah ada
        const variantsString = JSON.stringify(variants);
        let itemIndex = window.posCart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString);

        if (itemIndex !== -1) {
            window.posCart[itemIndex].jumlah += qty;
        } else {
            window.posCart.push({
                id_menu: id,
                nama: name,
                harga: price,
                jumlah: qty,
                catatan: '',
                variants: variants
            });
        }
        window.saveCart();
    };

    window.saveCart = function () {
        localStorage.setItem('kasir_cart', JSON.stringify(window.posCart));
        window.renderCart();
    };

    window.renderCart = function () {
        let html = '';
        let total = 0;
        window.posCart.forEach((item, index) => {
            let subtotal = item.harga * item.jumlah;
            total += subtotal;

            let variantsHtml = '';
            if (item.variants && item.variants.length > 0) {
                const varText = item.variants.map(v => {
                    return (v.qty && v.qty > 1) ? `${v.qty}x ${v.name}` : v.name;
                }).join(', ');
                variantsHtml = `<div class="small text-primary mb-1"><i class="bi bi-tags me-1"></i>${varText}</div>`;
            }

            html += `
                <div class="cart-item d-flex flex-column bg-transparent p-2 rounded mb-2 shadow-sm border">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-1 gap-2">
                        <div style="flex: 1; width: 100%;">
                            <span class="d-block fw-bold text-accent">${item.nama}</span>
                            ${variantsHtml}
                            <small class="text-white-50 font-sans">Rp ${item.harga.toLocaleString('id-ID')}</small>
                        </div>
                        <div class="d-flex justify-content-between align-items-center w-100" style="max-width: 220px;">
                            <div class="input-group input-group-sm" style="width: 90px;">
                                <button class="btn btn-outline-secondary px-2 btn-touch" type="button" onclick="updateQty(${index}, ${item.jumlah - 1})">-</button>
                                <input type="number" class="form-control text-white border-secondary text-center px-0 fw-bold font-sans" value="${item.jumlah}" min="0" readonly>
                                <button class="btn btn-outline-secondary px-2 btn-touch" type="button" onclick="updateQty(${index}, ${item.jumlah + 1})">+</button>
                            </div>
                            <div class="text-end">
                                <span class="small fw-bold price font-sans">Rp ${subtotal.toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-1">
                        <input type="text" class="form-control text-white border-secondary form-control-sm border-0 text-white rounded-pill px-3" style="font-size: 0.8rem;" placeholder="Catatan: misal pedas, setengah matang..." value="${item.catatan || ''}" onchange="updateCatatan(${index}, this.value)">
                    </div>
                </div>
            `;
        });

        if (html === '') {
            html = `
            <div class="d-flex flex-column justify-content-center align-items-center h-100 text-white-50 py-5">
                <i class="bi bi-basket2 text-opacity-25 text-accent" style="font-size: 3rem;"></i>
                <p class="mt-2 mb-0">Keranjang masih kosong</p>
            </div>`;
        }

        let discount = 0;
        const promoSelect = document.querySelector('select[name="promo_id"]');
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
            }
            if (discount > total) discount = total;
        }

        let totalTagihan = total - discount;

        const cartListEl = document.getElementById('cart-list');
        if (cartListEl) cartListEl.innerHTML = html;

        let tagihanHtml = '<span class="font-sans">Rp ' + totalTagihan.toLocaleString('id-ID') + '</span>';
        if (discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-white-50 small font-sans">Rp ${total.toLocaleString('id-ID')}</span><br><span class="font-sans">Rp ${totalTagihan.toLocaleString('id-ID')}</span>`;
        }
        const grandTotalEl = document.getElementById('grand-total');
        if (grandTotalEl) grandTotalEl.innerHTML = tagihanHtml;
    };

    window.updateQty = function (index, val) {
        let qty = parseInt(val);
        if (qty <= 0) {
            window.posCart.splice(index, 1); // Hapus item jika jumlah 0
        } else {
            window.posCart[index].jumlah = qty;
        }
        window.saveCart();
    };

    window.updateCatatan = function (index, val) {
        if (window.posCart[index]) {
            window.posCart[index].catatan = val;
            window.saveCart();
        }
    };

    window.getCurrentGrandTotal = function () {
        let total = 0;
        window.posCart.forEach(item => {
            total += item.harga * item.jumlah;
        });
        let discount = 0;
        const promoSelect = document.querySelector('select[name="promo_id"]');
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
            }
            if (discount > total) discount = total;
        }
        return Math.max(0, total - discount);
    };

})();
