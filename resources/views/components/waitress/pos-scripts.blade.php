<script>
    const allMenus = @json($menus);
    let filteredMenus = [...allMenus];
    let currentPage = 1;
    const itemsPerPage = 10;
    
    // Inisialisasi Cart dari Local Storage agar aman saat ter-refresh
    let cart = JSON.parse(localStorage.getItem('kasir_cart')) || [];

    document.addEventListener('DOMContentLoaded', () => {
        renderMenus();
        renderCart();
        toggleMeja(); // Init state
    });

    // --- LOGIKA TOGGLE MEJA ---
    function toggleMeja() {
        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value;
        const wrapperMeja = document.getElementById('wrapper-nomor-meja');
        const infoTakeaway = document.getElementById('info-takeaway-badge');
        const mejaSelect = document.querySelector('select[name="id_meja"]');

        if (tipePesanan === 'takeaway') {
            if (wrapperMeja) wrapperMeja.classList.add('d-none');
            if (infoTakeaway) infoTakeaway.classList.remove('d-none');
            if (mejaSelect) {
                mejaSelect.disabled = true;
                mejaSelect.removeAttribute('required');
            }
        } else {
            if (wrapperMeja) wrapperMeja.classList.remove('d-none');
            if (infoTakeaway) infoTakeaway.classList.add('d-none');
            if (mejaSelect) {
                mejaSelect.disabled = false;
                mejaSelect.setAttribute('required', 'required');
            }
        }
    }

    // --- LOGIKA FILTER DAN PAGINATION ---
    // --- LOGIKA PENCARIAN & FILTER (DEBOUNCED) ---
    let searchTimeout = null;
    function searchMenu(keyword) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            keyword = (keyword || '').toLowerCase().trim();
            const activeBtn = document.querySelector('.btn-soft.active');
            const currentCatBtn = activeBtn ? activeBtn.innerText.toLowerCase().trim() : 'semua';
            const baseMenus = currentCatBtn === 'semua' ? allMenus : allMenus.filter(m => (m.kategori || '').toLowerCase() === currentCatBtn);
            
            filteredMenus = baseMenus.filter(menu => (menu.nama_menu || '').toLowerCase().includes(keyword));
            currentPage = 1;
            renderMenus();
        }, 120);
    }

    function filterCategory(category, btnElement) {
        document.querySelectorAll('.btn-soft').forEach(btn => btn.classList.remove('active', 'bg-transparent', 'shadow-sm'));
        btnElement.classList.add('active', 'bg-transparent', 'shadow-sm');
        
        // Bersihkan kotak pencarian saat pindah kategori
        const searchInp = document.getElementById('searchInput');
        if (searchInp) searchInp.value = '';

        if (category === 'semua') {
            filteredMenus = [...allMenus];
        } else {
            filteredMenus = allMenus.filter(menu => (menu.kategori || '').toLowerCase() === category.toLowerCase());
        }
        currentPage = 1;
        renderMenus();
    }

    function changePage(direction) {
        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage) || 1;
        currentPage += direction;
        if (currentPage < 1) currentPage = 1;
        if (currentPage > maxPage) currentPage = maxPage;
        renderMenus();
    }

    function renderMenus() {
        const container = document.getElementById('menu-container');
        if (!container) return;

        const maxPage = Math.ceil(filteredMenus.length / itemsPerPage) || 1;
        
        // Update Pagination Controls
        const pageInfo = document.getElementById('pageInfo');
        if (pageInfo) pageInfo.innerText = `Halaman ${currentPage} / ${maxPage}`;

        const prevBtn = document.getElementById('prevPage');
        if (prevBtn) prevBtn.disabled = currentPage === 1;

        const nextBtn = document.getElementById('nextPage');
        if (nextBtn) nextBtn.disabled = currentPage === maxPage;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const menusToShow = filteredMenus.slice(startIndex, endIndex);

        if (menusToShow.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center text-white-50 py-5 mt-4">
                    <i class="bi bi-search" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h5 class="mt-3">Produk tidak ditemukan</h5>
                    <p class="small">Coba gunakan kata kunci lain.</p>
                </div>`;
            return;
        }

        const cardsHtml = menusToShow.map(menu => {
            const imageHtml = menu.image 
                  ? `<div class="bg-transparent text-center border-bottom" style="height: 150px;">
                       <img src="${menu.image_url}" onerror="this.onerror=null; this.src='{{ asset('images/logo.png') }}';" alt="${menu.nama_menu}" style="object-fit: contain; width: 100%; height: 100%;">
                     </div>`
                  : `<div class="card-img-top d-flex justify-content-center align-items-center border-bottom" style="height: 150px; background-color: #14171c;">
                         <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover;">
                     </div>`;
                   
            const badgeCat = `<span class="badge position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm bg-info text-white text-capitalize" style="backdrop-filter: blur(4px);">${menu.kategori}</span>`;

            const isHabis = !menu.is_available;
            const disabledStyle = isHabis ? 'opacity: 0.6; filter: grayscale(80%); pointer-events: none;' : 'cursor: pointer;';
            const habisOverlay = isHabis ? `<div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background: rgba(0,0,0,0.5); z-index: 5;"><h4 class="text-white fw-bold border border-2 border-white p-2 rounded">HABIS</h4></div>` : '';

            return `
                <div class="col-6 col-sm-6 col-md-4 col-lg-3">
                    <div class="menu-card card h-100 position-relative overflow-hidden ${!isHabis ? 'hover-lift' : ''}" 
                         onclick="${!isHabis ? `openVariantModal(${menu.id})` : ''}"
                         style="${disabledStyle}">
                        ${habisOverlay}
                        ${badgeCat}
                        ${imageHtml}
                        <div class="card-body text-center p-2 p-md-3">
                            <h6 class="fw-bold mb-1 text-truncate" style="font-size: 0.9rem;" title="${menu.nama_menu}">${menu.nama_menu}</h6>
                            <p class="price mb-0 fw-bold text-primary font-sans" style="font-size: 0.9rem;">${menu.is_dynamic_price ? 'Sesuai Timbangan' : 'Rp ' + parseFloat(menu.harga).toLocaleString('id-ID')}</p>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = cardsHtml.join('');
    }

    // --- LOGIKA CART (KERANJANG) ---
    let currentSelectedMenu = null;
    let currentModalQty = 1;
    let currentDynamicBasePrice = 0;

    function changeModalQty(delta) {
        currentModalQty += delta;
        if (currentModalQty < 1) currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;
        calculateVariantPrice();
    }

    function openVariantModal(id) {
        const menu = allMenus.find(m => m.id === id);
        if (!menu) return;

        let dynamicPrice = null;
        if (menu.is_dynamic_price) {
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
            try { variants = JSON.parse(menu.variants_json); } catch(e) {}
        }

        if (variants.length === 0) {
            // Langsung tambah ke cart jika tidak ada varian
            addToCart(menu.id, menu.nama_menu, currentDynamicBasePrice, []);
            return;
        }

        currentSelectedMenu = menu;
        document.getElementById('variantModalTitle').innerText = menu.nama_menu;
        
        let html = '';
        variants.forEach((group, gIndex) => {
            html += `<div class="mb-3">
                        <label class="fw-bold d-block mb-2">${group.group_name}</label>`;
            
            group.options.forEach((opt, oIndex) => {
                const isMultiple = group.type === 'multiple';
                const inputType = isMultiple ? 'checkbox' : 'radio';
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
                                <input type="number" class="form-control text-white border-secondary  text-center px-0 var-option-qty" id="${inputId}_qty" 
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

        document.getElementById('variantModalContent').innerHTML = html;
        currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;
        calculateVariantPrice();
        
        var vModal = new bootstrap.Modal(document.getElementById('variantModal'));
        vModal.show();
    }

    function changeToppingQty(inputId, delta) {
        const input = document.getElementById(inputId + '_qty');
        if (!input) return;
        let val = parseInt(input.value) + delta;
        if (val < 0) val = 0;
        input.value = val;
        calculateVariantPrice();
    }

    function calculateVariantPrice() {
        if (!currentSelectedMenu) return;
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
        document.getElementById('variantModalPrice').innerText = 'Rp ' + total.toLocaleString('id-ID');
        return unitPrice;
    }

    function confirmVariantSelection() {
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

        let finalPrice = calculateVariantPrice() / currentModalQty;
        addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty);
        
        var vModal = bootstrap.Modal.getInstance(document.getElementById('variantModal'));
        vModal.hide();
    }

    function addAnotherVariantSelection() {
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

        let finalPrice = calculateVariantPrice() / currentModalQty;
        addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty);
        
        // Reset Inputs
        document.querySelectorAll('.var-option-input').forEach(input => {
            if(input.type === 'radio' || input.type === 'checkbox') input.checked = false;
        });
        document.querySelectorAll('.var-option-qty').forEach(input => {
            input.value = 0;
        });
        
        currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;
        calculateVariantPrice();

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if(alertContainer) {
            alertContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show p-2 mb-3" role="alert" style="font-size:0.85rem;">
                <i class="bi bi-check-circle-fill me-1"></i> Porsi sebelumnya berhasil ditambahkan! Silakan pilih varian untuk porsi berikutnya.
                <button type="button" class="btn-close p-2 btn-touch" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
        }
    }

    function addToCart(id, name, price, variants = [], qty = 1) {
        // Cek apakah item dengan menu_id dan varian yang SAMA persis sudah ada
        const variantsString = JSON.stringify(variants);
        let itemIndex = cart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString);
        
        if (itemIndex !== -1) {
            cart[itemIndex].jumlah += qty;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: qty, catatan: '', variants: variants });
        }
        saveCart();
    }

    function saveCart() {
        localStorage.setItem('kasir_cart', JSON.stringify(cart));
        renderCart();
    }

    function renderCart() {
        let html = '';
        let total = 0;
        cart.forEach((item, index) => {
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
                                <input type="number" class="form-control text-white border-secondary  text-center px-0 border-secondary fw-bold font-sans" value="${item.jumlah}" min="0" readonly>
                                <button class="btn btn-outline-secondary px-2 btn-touch" type="button" onclick="updateQty(${index}, ${item.jumlah + 1})">+</button>
                            </div>
                            <div class="text-end">
                                <span class="small fw-bold price font-sans">Rp ${subtotal.toLocaleString('id-ID')}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-1">
                        <input type="text" class="form-control text-white border-secondary  form-control-sm border-0  text-white rounded-pill px-3" style="font-size: 0.8rem;" placeholder="Catatan: misal pedas, setengah matang..." value="${item.catatan || ''}" onchange="updateCatatan(${index}, this.value)">
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
            if(discount > total) discount = total;
        }

        let totalTagihan = total - discount;
        
        document.getElementById('cart-list').innerHTML = html;
        let tagihanHtml = '<span class="font-sans">Rp ' + totalTagihan.toLocaleString('id-ID') + '</span>';
        if(discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-white-50 small font-sans">Rp ${total.toLocaleString('id-ID')}</span><br><span class="font-sans">Rp ${totalTagihan.toLocaleString('id-ID')}</span>`;
        }
        document.getElementById('grand-total').innerHTML = tagihanHtml;
    }

    function updateQty(index, val) {
        let qty = parseInt(val);
        if (qty <= 0) {
            cart.splice(index, 1); // Hapus item jika jumlah 0
        } else {
            cart[index].jumlah = qty;
        }
        saveCart();
    }

    function updateCatatan(index, val) {
        cart[index].catatan = val;
        saveCart();
    }

    function showQrisModal() {
        if (cart.length === 0) return alert('Keranjang masih kosong!');
        var qrisModal = new bootstrap.Modal(document.getElementById('qrisModal'));
        qrisModal.show();
    }

    function confirmQrisPayment() {
        var modalEl = document.getElementById('qrisModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if(modal) modal.hide();
        
        submitOrder(1, 'qris');
    }

    function getCurrentGrandTotal() {
        let total = 0;
        cart.forEach(item => {
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
    }

    let posCashState = {
        total: 0,
        nominal: 0,
        isUangPas: true
    };

    function showCashModal() {
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value || 'dine_in';
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        const idMeja = tipePesanan === 'takeaway' ? null : (mejaSelect ? mejaSelect.value : null);

        if (tipePesanan === 'dine_in' && !idMeja) {
            return alert('Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In)!');
        }

        const grandTotal = getCurrentGrandTotal();
        posCashState.total = grandTotal;
        posCashState.nominal = grandTotal;
        posCashState.isUangPas = true;

        document.getElementById('pos-cash-total-display').innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
        
        // Generate Preset Buttons
        let presetHtml = `
            <button type="button" class="btn btn-sm btn-outline-success pos-cash-preset-btn active rounded-pill px-3 py-2 fw-bold" onclick="selectPosCashPreset('pas', ${grandTotal}, this)">
                <i class="bi bi-check2-circle me-1"></i> Uang Pas (Rp ${grandTotal.toLocaleString('id-ID')})
            </button>
        `;

        if (grandTotal < 50000) {
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary pos-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="selectPosCashPreset('fixed', 50000, this)">
                    Rp 50.000
                </button>
            `;
        }

        if (grandTotal < 100000 && grandTotal !== 50000) {
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary pos-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="selectPosCashPreset('fixed', 100000, this)">
                    Rp 100.000
                </button>
            `;
        }

        if (grandTotal > 100000) {
            const nextCeil = Math.ceil((grandTotal + 1000) / 50000) * 50000;
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary pos-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="selectPosCashPreset('fixed', ${nextCeil}, this)">
                    Rp ${nextCeil.toLocaleString('id-ID')}
                </button>
            `;
        }

        presetHtml += `
            <button type="button" class="btn btn-sm btn-outline-secondary pos-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="selectPosCashPreset('custom', 0, this)">
                <i class="bi bi-pencil me-1"></i> Nominal Lain
            </button>
        `;

        document.getElementById('pos-cash-presets-container').innerHTML = presetHtml;
        document.getElementById('pos-custom-nominal-container').style.display = 'none';
        document.getElementById('pos-custom-nominal-input').value = '';

        updatePosKembalianUI(grandTotal, 0, true);

        const cashModal = new bootstrap.Modal(document.getElementById('cashPaymentModal'));
        cashModal.show();
    }

    function selectPosCashPreset(type, amount, btnEl) {
        document.querySelectorAll('.pos-cash-preset-btn').forEach(btn => {
            btn.classList.remove('btn-outline-success', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        btnEl.classList.remove('btn-outline-secondary');
        btnEl.classList.add('btn-outline-success', 'active');

        const customContainer = document.getElementById('pos-custom-nominal-container');
        const customInput = document.getElementById('pos-custom-nominal-input');

        if (type === 'pas') {
            customContainer.style.display = 'none';
            posCashState.nominal = posCashState.total;
            posCashState.isUangPas = true;
            updatePosKembalianUI(posCashState.total, 0, true);
        } else if (type === 'fixed') {
            customContainer.style.display = 'none';
            posCashState.nominal = amount;
            posCashState.isUangPas = false;
            const kembalian = Math.max(0, amount - posCashState.total);
            updatePosKembalianUI(amount, kembalian, false);
        } else if (type === 'custom') {
            customContainer.style.display = 'block';
            customInput.focus();
            posCashState.isUangPas = false;
            if (customInput.value) {
                onPosCustomNominalChange(customInput.value);
            } else {
                updatePosKembalianUI(0, 0, false, true);
            }
        }
    }

    function onPosCustomNominalChange(val) {
        const nominal = parseInt(val) || 0;
        posCashState.nominal = nominal;

        if (nominal < posCashState.total) {
            updatePosKembalianUI(nominal, 0, false, false, true);
        } else {
            const kembalian = nominal - posCashState.total;
            updatePosKembalianUI(nominal, kembalian, kembalian === 0);
        }
    }

    function updatePosKembalianUI(nominal, kembalian, isPas, isNeedInput = false, isUnderpaid = false) {
        const labelUang = document.getElementById('pos-label-uang-diterima');
        const labelKembalian = document.getElementById('pos-label-kembalian');
        const alertMsg = document.getElementById('pos-kembalian-alert-msg');
        const card = document.getElementById('pos-kembalian-card');
        const submitBtn = document.getElementById('btn-confirm-pos-cash');

        if (isUnderpaid) {
            labelUang.innerText = 'Rp ' + nominal.toLocaleString('id-ID');
            labelKembalian.innerHTML = '<span class="text-danger">⚠️ Uang Kurang</span>';
            alertMsg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Uang yang diserahkan kurang dari total tagihan (Rp ' + posCashState.total.toLocaleString('id-ID') + ').</span>';
            card.style.background = 'rgba(239, 68, 68, 0.08)';
            card.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            submitBtn.disabled = true;
            return;
        }

        if (isNeedInput) {
            labelUang.innerText = '-';
            labelKembalian.innerText = '-';
            alertMsg.innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Masukkan jumlah uang tunai yang diserahkan tamu.';
            card.style.background = 'rgba(255, 255, 255, 0.04)';
            card.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            submitBtn.disabled = true;
            return;
        }

        submitBtn.disabled = false;
        labelUang.innerText = 'Rp ' + nominal.toLocaleString('id-ID');

        if (isPas || kembalian === 0) {
            labelKembalian.innerHTML = '<span class="text-success">Rp 0 (Uang Pas)</span>';
            alertMsg.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Uang pas, tidak ada kembalian.';
            card.style.background = 'rgba(34, 197, 94, 0.08)';
            card.style.borderColor = 'rgba(34, 197, 94, 0.4)';
        } else {
            labelKembalian.innerHTML = '<span class="text-warning fw-bold fs-5">Rp ' + kembalian.toLocaleString('id-ID') + '</span>';
            alertMsg.innerHTML = '<i class="bi bi-bell-fill text-warning me-1"></i> <strong>Kembalikan ke tamu sebesar Rp ' + kembalian.toLocaleString('id-ID') + '</strong>.';
            card.style.background = 'rgba(234, 179, 8, 0.09)';
            card.style.borderColor = 'rgba(234, 179, 8, 0.4)';
        }
    }

    function confirmCashPayment() {
        const modalEl = document.getElementById('cashPaymentModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        submitOrder(1, 'cash', posCashState.nominal, posCashState.isUangPas);
    }

    function submitOrder(isLunas, method, nominalTunai = null, isUangPas = false) {
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value || 'dine_in';
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        const idMeja = tipePesanan === 'takeaway' ? null : (mejaSelect ? mejaSelect.value : null);

        if (tipePesanan === 'dine_in' && !idMeja) {
            return alert('Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In)!');
        }

        let formData = {
            _token: "{{ csrf_token() }}",
            id_meja: idMeja,
            tipe_pesanan: tipePesanan,
            promo_id: document.querySelector('select[name="promo_id"]')?.value || null,
            pembayaran_langsung: isLunas,
            metode_pembayaran: method,
            nominal_tunai: nominalTunai,
            is_uang_pas: isUangPas,
            items: cart
        };

        fetch("{{ url('/kasir/manual-order') }}", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(formData)
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) alert(data.error);
            else {
                if (isLunas) {
                    let alertSuccessMsg = 'Pembayaran Lunas Berhasil!';
                    if (method === 'cash' && nominalTunai && !isUangPas) {
                        const totalTagihan = getCurrentGrandTotal();
                        const kembalian = Math.max(0, nominalTunai - totalTagihan);
                        if (kembalian > 0) {
                            alertSuccessMsg += `\n\n💵 Kembalian untuk Tamu: Rp ${kembalian.toLocaleString('id-ID')}`;
                        }
                    }

                    if (confirm(alertSuccessMsg + '\n\nIngin cetak struk sekarang?')) {
                        @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                        @if($printerActive)
                            if (confirm('Kirim langsung ke Mesin Printer Thermal? (Pilih Cancel untuk cetak lewat Browser)')) {
                                fetch(`/kasir/order/${data.id_pesanan}/print-thermal`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" }
                                })
                                .then(res => res.json())
                                .then(resData => {
                                    if(resData.error) alert(resData.error);
                                    else alert(resData.message);
                                });
                            } else {
                                window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                            }
                        @else
                            window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                        @endif
                    }
                } else {
                    alert('Pesanan berhasil disimpan (Belum dibayar).');
                }
                localStorage.removeItem('kasir_cart'); // Kosongkan cart setelah berhasil
                location.reload();
            }
        });
    }

    function updateOrderStatus(idPesanan, newStatus) {
        if (!confirm(`Ubah status pesanan ke ${newStatus.toUpperCase()}?`)) return;

        fetch(`{{ url('/kasir/order') }}/${idPesanan}/status`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert(data.message);
                location.reload();
            }
        })
        .catch(err => console.error(err));
    }

    function payOrder(idPesanan) {
        let method = prompt("Masukkan metode pembayaran untuk Pesanan #" + idPesanan + "\nKetik 'cash' atau 'qris':");
        if (!method) return;
        method = method.toLowerCase().trim();
        if (method !== 'cash' && method !== 'qris') {
            alert("Metode tidak valid. Harus 'cash' atau 'qris'.");
            return;
        }

        fetch(`{{ url('/kasir/order') }}/${idPesanan}/pay`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ metode: method })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
            } else {
                alert(data.message);
                location.reload();
            }
        })
        .catch(err => console.error(err));
    }
</script>
