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
        document.querySelectorAll('[id^="consumer-note-box-"]').forEach(el => el.style.display = 'none');

        let aggregatedQty = {};

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;

            if (!aggregatedQty[item.id_menu]) {
                aggregatedQty[item.id_menu] = 0;
            }
            aggregatedQty[item.id_menu] += item.jumlah;
        });

        Object.keys(aggregatedQty).forEach(menuId => {
            let numId = Number(menuId);
            let qtyDisplay = document.getElementById('qty-' + menuId);
            if (qtyDisplay) qtyDisplay.innerText = aggregatedQty[menuId];

            let noteBox = document.getElementById('consumer-note-box-' + menuId);
            if (noteBox) {
                if (aggregatedQty[menuId] > 0) {
                    noteBox.style.display = 'block';
                    let noteInput = document.getElementById('menu-note-' + menuId);
                    let item = cart.find(i => i.id_menu === numId || i.id_menu == menuId);
                    if (noteInput && item && document.activeElement !== noteInput) {
                        noteInput.value = item.catatan || '';
                    }
                } else {
                    noteBox.style.display = 'none';
                }
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

    // --- 2. VALIDASI NOMOR WHATSAPP GUEST SECARA REALTIME ---
    window.validateGuestPhone = function (showFeedback = false) {
        const inputPhone = document.getElementById('inputGuestPhone');
        const feedback = document.getElementById('guestPhoneFeedback');
        const counter = document.getElementById('guestPhoneCounter');

        if (!inputPhone) return { isValid: true, message: '', phone: '' };

        // 1. Sanitasi input: hanya angka
        let rawVal = inputPhone.value.trim();
        let val = rawVal.replace(/[^0-9]/g, '');

        if (inputPhone.value !== val) {
            inputPhone.value = val;
        }

        // Batasi panjang maksimal 15 digit (standar E.164 ITU-T)
        const maxLen = 15;
        if (val.length > maxLen) {
            val = val.substring(0, maxLen);
            inputPhone.value = val;
        }

        // 3. Update counter karakter realtime
        if (counter) {
            counter.innerText = val.length + ' / ' + maxLen + ' digit';
            if (val.length >= 10 && val.length <= 13) {
                counter.className = 'badge rounded-pill bg-success bg-opacity-25 text-success border border-success border-opacity-50';
            } else if (val.length > 0) {
                counter.className = 'badge rounded-pill bg-warning bg-opacity-25 text-warning border border-warning border-opacity-50';
            } else {
                counter.className = 'badge rounded-pill bg-dark border border-secondary text-secondary';
            }
        }

        const isTakeaway = config.orderType === 'takeaway' || !config.hasMeja;

        // 4. Cek jika input kosong
        if (!val) {
            if (isTakeaway) {
                if (showFeedback && feedback) {
                    feedback.style.display = 'block';
                    feedback.className = 'mt-1 text-danger';
                    feedback.innerHTML = '<i class="bi bi-x-circle me-1"></i> Nomor WhatsApp wajib diisi untuk pesanan Takeaway.';
                    inputPhone.style.borderColor = '#dc3545';
                } else if (feedback) {
                    feedback.style.display = 'none';
                    inputPhone.style.borderColor = '#30363d';
                }
                return { isValid: false, message: 'Nomor WhatsApp wajib diisi untuk pesanan Takeaway (bawa pulang).', phone: '' };
            } else {
                if (feedback) feedback.style.display = 'none';
                inputPhone.style.borderColor = '#30363d';
                return { isValid: true, message: '', phone: '' };
            }
        }

        // 5. Validasi awalan: harus diawali 08 atau 628
        if (!val.startsWith('08') && !val.startsWith('628')) {
            if (showFeedback && feedback) {
                feedback.style.display = 'block';
                feedback.className = 'mt-1 text-danger';
                feedback.innerHTML = '<i class="bi bi-x-circle me-1"></i> Nomor WhatsApp harus diawali <strong>08</strong> atau <strong>628</strong>.';
                inputPhone.style.borderColor = '#dc3545';
            }
            return { isValid: false, message: 'Nomor WhatsApp tidak valid. Harus diawali dengan 08 atau 628 (contoh: 081234567890).', phone: val };
        }

        // 6. Validasi batas minimum angka
        const minLen = val.startsWith('628') ? 11 : 10;
        if (val.length < minLen) {
            if (showFeedback && feedback) {
                feedback.style.display = 'block';
                feedback.className = 'mt-1 text-warning';
                feedback.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> Terlalu pendek (minimal ${minLen} angka, saat ini ${val.length} angka).`;
                inputPhone.style.borderColor = '#ffc107';
            }
            return { isValid: false, message: `Nomor WhatsApp terlalu pendek. Minimal ${minLen} digit angka (contoh: 081234567890).`, phone: val };
        }

        if (val.length > maxLen) {
            if (showFeedback && feedback) {
                feedback.style.display = 'block';
                feedback.className = 'mt-1 text-danger';
                feedback.innerHTML = `<i class="bi bi-x-circle me-1"></i> Terlalu panjang (maksimal ${maxLen} angka).`;
                inputPhone.style.borderColor = '#dc3545';
            }
            return { isValid: false, message: `Nomor WhatsApp terlalu panjang (maksimal ${maxLen} digit angka).`, phone: val };
        }

        // 7. Format dinyatakan valid
        if (showFeedback && feedback) {
            feedback.style.display = 'block';
            feedback.className = 'mt-1 text-success';
            feedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Format nomor WhatsApp valid (${val.length} digit).`;
            inputPhone.style.borderColor = '#238636';
        } else if (feedback) {
            feedback.style.display = 'none';
            inputPhone.style.borderColor = '#238636';
        }

        return { isValid: true, message: '', phone: val };
    };

    // --- 3. CHECKOUT & PENGIRIMAN ORDER KE SERVER ---
    window.openConfirmOrderModal = function () {
        if (cart.length === 0) {
            alert('Keranjang belanja masih kosong. Silakan pilih menu terlebih dahulu!');
            return;
        }

        const modalQty = document.getElementById('modal-summary-qty');
        const modalTotal = document.getElementById('modal-summary-total');
        if (modalQty && document.getElementById('cart-qty')) {
            modalQty.innerText = document.getElementById('cart-qty').innerText;
        }
        if (modalTotal && document.getElementById('cart-total')) {
            modalTotal.innerHTML = document.getElementById('cart-total').innerHTML;
        }

        // Auto-fill nama dan nomor HP yang tersimpan jika ini adalah pesanan tambahan
        const savedName = localStorage.getItem('master_cafe_guest_name');
        const savedPhone = localStorage.getItem('master_cafe_guest_phone');
        const inputNameEl = document.getElementById('inputGuestName');
        const phoneInputEl = document.getElementById('inputGuestPhone');
        if (inputNameEl && !inputNameEl.value.trim() && savedName) {
            inputNameEl.value = savedName;
        }
        if (phoneInputEl && !phoneInputEl.value.trim() && savedPhone) {
            phoneInputEl.value = savedPhone;
        }

        if (phoneInputEl) {
            window.validateGuestPhone(phoneInputEl.value.trim().length > 0);
        }

        const modalEl = document.getElementById('modalConfirmGuestOrder');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            window.proceedToCheckout();
        }
    };

    window.submitCustomerOrder = function () {
        if (cart.length === 0) return alert('Silakan pilih menu terlebih dahulu!');

        const inputName = document.getElementById('inputGuestName');
        const inputPhone = document.getElementById('inputGuestPhone');

        let guestName = inputName ? inputName.value.trim() : '';

        if (!guestName) {
            alert('Mohon masukkan Nama Pemesan / Panggilan terlebih dahulu.');
            if (inputName) inputName.focus();
            return;
        }

        // Validasi Nomor WhatsApp secara ketat
        const phoneCheck = window.validateGuestPhone(true);
        if (!phoneCheck.isValid) {
            alert(phoneCheck.message);
            if (inputPhone) {
                inputPhone.focus();
                inputPhone.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }
        let guestPhone = phoneCheck.phone;

        const isGeofenceActive = config.isGeofenceActive || false;
        const isDineIn = (config.orderType === 'dine_in' && config.hasMeja);

        if (isGeofenceActive && isDineIn) {
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung verifikasi lokasi GPS. Pemesanan meja (Dine-In) membutuhkan GPS aktif.');
                return;
            }

            const btnSubmit = document.getElementById('btnSubmitFinalOrder');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifikasi GPS...';
            }

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    window.proceedToCheckout(guestName, guestPhone, position.coords.latitude, position.coords.longitude);
                },
                function (error) {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = 'Kirim Pesanan <i class="bi bi-arrow-right ms-1"></i>';
                    }
                    let errorMsg = 'Izin lokasi (GPS) diperlukan untuk memastikan Anda berada di meja kafe.';
                    if (error.code === error.PERMISSION_DENIED) {
                        errorMsg = 'Akses lokasi (GPS) ditolak oleh browser Anda. Harap izinkan akses lokasi di pengaturan browser untuk memesan di meja kafe.';
                    } else if (error.code === error.POSITION_UNAVAILABLE) {
                        errorMsg = 'Lokasi GPS tidak dapat dideteksi. Pastikan fitur lokasi/GPS di HP Anda aktif.';
                    } else if (error.code === error.TIMEOUT) {
                        errorMsg = 'Waktu pencarian lokasi GPS habis. Silakan coba lagi.';
                    }
                    alert(errorMsg);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            window.proceedToCheckout(guestName, guestPhone, null, null);
        }
    };

    window.proceedToCheckout = function (guestName, guestPhone, userLat = null, userLng = null) {
        const btnSubmit = document.getElementById('btnSubmitFinalOrder');
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';
        }

        let formData = {
            _token: getCsrfToken(),
            tipe_pesanan: config.orderType || 'dine_in',
            guest_name: guestName || 'Tamu',
            guest_phone: guestPhone || null,
            user_lat: userLat,
            user_lng: userLng,
            promo_id: document.getElementById('promo_id') ? document.getElementById('promo_id').value : null,
            items: cart
        };

        if (config.hasMeja && config.mejaId) {
            formData.id_meja = config.mejaId;
        }

        fetch(config.orderAddUrl || '/konsumen/order/add', {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(formData)
        })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw err; });
                }
                return res.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = 'Kirim Pesanan <i class="bi bi-arrow-right ms-1"></i>';
                    }
                } else {
                    // Simpan identitas pemesan agar tidak perlu ketik ulang saat nambah pesanan
                    if (guestName) localStorage.setItem('master_cafe_guest_name', guestName);
                    if (guestPhone) localStorage.setItem('master_cafe_guest_phone', guestPhone);

                    // Simpan metadata pesanan aktif ke LocalStorage (TTL 12 Jam & Multi-Order Support)
                    if (data.order_token) {
                        const guestOrder = {
                            token: data.order_token,
                            id_pesanan: data.id_pesanan,
                            guest_name: data.guest_name,
                            id_meja: config.mejaId || '',
                            label: config.mejaLabel || 'Pesanan',
                            tipe_pesanan: config.orderType || 'dine_in',
                            created_at: Date.now(),
                            expires_at: Date.now() + (12 * 60 * 60 * 1000)
                        };
                        localStorage.setItem('active_guest_order', JSON.stringify(guestOrder));

                        // Tambahkan ke daftar pesanan aktif (Multi-Order Array)
                        let orders = [];
                        try {
                            const rawOrders = localStorage.getItem('active_guest_orders');
                            if (rawOrders) orders = JSON.parse(rawOrders);
                            if (!Array.isArray(orders)) orders = [];
                        } catch (e) { orders = []; }
                        orders = orders.filter(o => o.token !== data.order_token);
                        orders.unshift(guestOrder);
                        localStorage.setItem('active_guest_orders', JSON.stringify(orders));
                    }

                    // Kosongkan keranjang setelah pesanan berhasil disubmit
                    try {
                        cart = [];
                        if (typeof cartStorageKey !== 'undefined') sessionStorage.removeItem(cartStorageKey);
                        if (typeof updateCartBar === 'function') updateCartBar();
                        window.updateCartUI();
                    } catch (e) { }

                    // Redirect ke Checkout atau Tracking
                    if (data.checkout_url) {
                        window.location.href = data.checkout_url;
                    } else if (data.tracking_url) {
                        window.location.href = data.tracking_url;
                    } else {
                        window.location.href = "/konsumen/checkout/" + data.id_pesanan;
                    }
                }
            })
            .catch(err => {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = 'Kirim Pesanan <i class="bi bi-arrow-right ms-1"></i>';
                }
                if (err.errors) {
                    let msg = "";
                    for (let key in err.errors) {
                        msg += err.errors[key][0] + "\n";
                    }
                    alert("Kesalahan validasi:\n" + msg);
                } else if (err.error) {
                    alert(err.error);
                } else if (err.message) {
                    alert(err.message);
                } else {
                    alert("Terjadi kesalahan saat memproses pesanan.");
                    console.error(err);
                }
            });
    };

    // Inisialisasi event listener nomor WhatsApp pada DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInputEl = document.getElementById('inputGuestPhone');
        if (phoneInputEl) {
            phoneInputEl.addEventListener('input', function () {
                window.validateGuestPhone(this.value.length > 0);
            });
            phoneInputEl.addEventListener('blur', function () {
                window.validateGuestPhone(true);
            });
            phoneInputEl.addEventListener('paste', function () {
                setTimeout(() => window.validateGuestPhone(true), 50);
            });
        }
    });
})();
