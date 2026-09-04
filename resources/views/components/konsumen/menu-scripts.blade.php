
<script>
    const allMenus = @json($menus);
    let cart = [];
    let currentSelectedMenu = null;
    let currentModalQty = 1;

    function changeModalQty(delta) {
        currentModalQty += delta;
        if (currentModalQty < 1) currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;
        calculateVariantPrice();
    }

    function filterMenu(category, btn) {
        document.querySelectorAll('.btn-filter').forEach(b => { b.classList.remove('active'); b.classList.replace('btn-outline-secondary', 'btn-outline-secondary'); b.style.backgroundColor=''; b.style.color=''; b.style.border=''; });
        btn.classList.add('active'); btn.style.backgroundColor='#c08e5c'; btn.style.color='white'; btn.style.border='none';

        document.querySelectorAll('.menu-item').forEach(item => {
            if (category === 'semua' || item.getAttribute('data-kategori') === category) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function openVariantModal(id) {
        currentSelectedMenu = allMenus.find(m => m.id === id);
        if (!currentSelectedMenu) return;

        currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;

        let content = '';
        if (currentSelectedMenu.variants && currentSelectedMenu.variants.length > 0) {
            content += `<h6 class="fw-bold mb-3">Pilih Varian (Opsional)</h6>`;
            currentSelectedMenu.variants.forEach(variant => {
                if (variant.jenis === 'single') {
                    content += `
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold mb-2">${variant.nama_varian}</label>
                            <div class="d-flex flex-wrap gap-2">
                                ${variant.opsi.map((opsi, i) => `
                                    <input type="radio" class="btn-check var-option-input" name="variant_${variant.id}" id="opt_${opsi.id}" value="${opsi.id}" data-price="${opsi.harga_tambahan}" data-name="${opsi.nama_opsi}" onchange="calculateVariantPrice()">
                                    <label class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm var-option-label text-white" for="opt_${opsi.id}" style="border: 1px solid #21262d;">
                                        ${opsi.nama_opsi} ${opsi.harga_tambahan > 0 ? '(+Rp '+opsi.harga_tambahan.toLocaleString('id-ID')+')' : ''}
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
                                            ${opsi.nama_opsi} <small class="text-muted d-block">${opsi.harga_tambahan > 0 ? '+Rp '+opsi.harga_tambahan.toLocaleString('id-ID') : 'Gratis'}</small>
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
        if (content === '') {
            container.innerHTML = '<p class="text-muted my-3 text-center">Tidak ada pilihan varian atau topping.</p>';
        } else {
            container.innerHTML = content;
        }

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if(alertContainer) alertContainer.innerHTML = '';

        calculateVariantPrice();
        var myModal = new bootstrap.Modal(document.getElementById('variantModal'));
        myModal.show();
    }

    function changeToppingQty(inputId, delta, price, btnEl) {
        let input = document.getElementById(inputId);
        let val = parseInt(input.value) + delta;
        if (val < 0) val = 0;
        input.value = val;
        
        if (val > 0) {
            btnEl.parentElement.parentElement.style.borderColor = '#c08e5c';
        } else {
            btnEl.parentElement.parentElement.style.borderColor = '#21262d';
        }

        calculateVariantPrice();
    }

    function calculateVariantPrice() {
        if (!currentSelectedMenu) return;
        let base = currentSelectedMenu.harga;
        let additional = 0;

        document.querySelectorAll('.var-option-input:checked').forEach(el => {
            additional += parseFloat(el.getAttribute('data-price') || 0);
            el.nextElementSibling.style.borderColor = '#c08e5c';
            el.nextElementSibling.style.color = '#c08e5c';
            el.nextElementSibling.classList.replace('btn-outline-secondary', 'btn-outline-primary');
        });
        document.querySelectorAll('.var-option-input:not(:checked)').forEach(el => {
            el.nextElementSibling.style.borderColor = '#21262d';
            el.nextElementSibling.style.color = 'white';
            el.nextElementSibling.classList.replace('btn-outline-primary', 'btn-outline-secondary');
        });

        document.querySelectorAll('.var-option-qty').forEach(el => {
            let q = parseInt(el.value);
            if (q > 0) {
                additional += q * parseFloat(el.getAttribute('data-price') || 0);
            }
        });

        let total = (base + additional) * currentModalQty;
        document.getElementById('variantModalPrice').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }

    function confirmVariantSelection() {
        addAnotherVariantSelection();
        var myModal = bootstrap.Modal.getInstance(document.getElementById('variantModal'));
        myModal.hide();
    }

    function addAnotherVariantSelection() {
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
        
        addToCart(currentSelectedMenu.id, currentSelectedMenu.nama_menu, finalPrice, selectedVariants, currentModalQty);
        
        document.querySelectorAll('.var-option-input').forEach(input => {
            if(input.type === 'radio' || input.type === 'checkbox') input.checked = false;
        });
        document.querySelectorAll('.var-option-qty').forEach(input => {
            input.value = 0;
            input.parentElement.parentElement.style.borderColor = '#21262d';
        });
        
        currentModalQty = 1;
        document.getElementById('modal-qty-display').innerText = currentModalQty;
        calculateVariantPrice();

        let alertContainer = document.getElementById('variantModalAlertContainer');
        if(alertContainer) {
            alertContainer.innerHTML = `<div class="alert alert-success alert-dismissible fade show p-2 mb-3" role="alert" style="font-size:0.85rem; background-color: rgba(72,187,120,0.1); color: #48bb78; border-color: rgba(72,187,120,0.2);">
                <i class="bi bi-check-circle-fill me-1"></i> Porsi berhasil ditambahkan! Silakan pilih untuk porsi berikutnya.
                <button type="button" class="btn-close p-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            setTimeout(() => { alertContainer.innerHTML = ''; }, 3000);
        }
    }

    function addToCart(id, name, price, variants = [], qty = 1) {
        const variantsString = JSON.stringify(variants);
        let itemIndex = cart.findIndex(i => i.id_menu === id && JSON.stringify(i.variants) === variantsString);
        if (itemIndex !== -1) {
            cart[itemIndex].jumlah += qty;
        } else {
            cart.push({ id_menu: id, nama: name, harga: price, jumlah: qty, catatan: '', variants: variants });
        }
        updateCartUI();
    }

    function updateCatatan(id, val) {
        let item = cart.find(i => i.id_menu === id);
        if (item) {
            item.catatan = val;
        }
    }

    function removeFromCart(id) {
        let itemIndex = cart.findIndex(i => i.id_menu === id);
        if (itemIndex !== -1) {
            if (cart[itemIndex].jumlah > 1) {
                cart[itemIndex].jumlah--;
            } else {
                cart.splice(itemIndex, 1);
            }
            updateCartUI();
        }
    }

    function updateCartUI() {
        let total = 0;
        let qty = 0;
        
        document.querySelectorAll('[id^="qty-"]').forEach(el => el.innerText = '0');
        document.querySelectorAll('[id^="catatan-container-"]').forEach(el => el.style.display = 'none');

        let aggregatedQty = {};
        let aggregatedVariantsHtml = {};

        cart.forEach(item => {
            total += (item.harga * item.jumlah);
            qty += item.jumlah;
            
            if(!aggregatedQty[item.id_menu]) {
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
            aggregatedVariantsHtml[item.id_menu] += `<div class="mb-1">${item.jumlah}x: ${variantsHtml}</div>`;
        });

        Object.keys(aggregatedQty).forEach(menuId => {
            let qtyDisplay = document.getElementById('qty-' + menuId);
            if(qtyDisplay) qtyDisplay.innerText = aggregatedQty[menuId];

            let catatanContainer = document.getElementById('catatan-container-' + menuId);
            if(catatanContainer && aggregatedVariantsHtml[menuId] !== '') {
                catatanContainer.style.display = 'block';
                catatanContainer.innerHTML = aggregatedVariantsHtml[menuId];
            }
        });
        
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
            if(discount > total) discount = total;
        }
        
        let totalTagihan = total - discount;
        
        let tagihanHtml = 'Rp ' + totalTagihan.toLocaleString('id-ID');
        if (discount > 0) {
            tagihanHtml = `<span class="text-decoration-line-through text-muted small fs-6">Rp ${total.toLocaleString('id-ID')}</span><br>Rp ${totalTagihan.toLocaleString('id-ID')}`;
        }
        document.getElementById('cart-total').innerHTML = tagihanHtml;
        document.getElementById('cart-qty').innerText = qty + ' Item';
    }

    function submitCustomerOrder() {
        if (cart.length === 0) return alert('Silakan pilih menu terlebih dahulu!');
        if (!confirm('Apakah pesanan Anda sudah benar?')) return;
        proceedToCheckout();
    }

    function proceedToCheckout() {
        let formData = {
            _token: "{{ csrf_token() }}",
            tipe_pesanan: '{{ $orderType ?? "dine-in" }}',
            @if(isset($meja))
            id_meja: "{{ $meja->id }}",
            @endif
            promo_id: document.getElementById('promo_id') ? document.getElementById('promo_id').value : null,
            items: cart
        };

                fetch("{{ url('/konsumen/order/add') }}", {
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
            } else {
                window.location.href = "/konsumen/checkout/" + data.id_pesanan;
            }
        })
        .catch(err => {
            if (err.errors) {
                let msg = "";
                for (let key in err.errors) {
                    msg += err.errors[key][0] + "\n";
                }
                alert("Kesalahan validasi:\n" + msg);
            } else if (err.error) {
                alert('Error: ' + err.error);
            } else if (err.message) {
                alert("Error: " + err.message);
            } else {
                alert("Terjadi kesalahan saat memproses pesanan.");
                console.error(err);
            }
        });
    }
</script>

