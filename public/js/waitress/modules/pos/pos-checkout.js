/**
 * Master Cafe POS - POS Module: Checkout & Payment Submission
 * File: public/js/waitress/modules/pos/pos-checkout.js
 * Deskripsi: Menangani alur checkout pembayaran tunai (preset kalkulasi kembalian),
 *            pembayaran QRIS, submit order manual, dan integrasi cetak struk thermal.
 */

(function () {
    'use strict';

    function getCsrfToken() {
        return window.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    let posCashState = {
        total: 0,
        nominal: 0,
        isUangPas: true
    };

    // --- LOGIKA PEMBAYARAN QRIS ---
    window.showQrisModal = function () {
        const cart = window.posCart || [];
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const modalEl = document.getElementById('qrisModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const qrisModal = bootstrap.Modal.getOrCreateInstance
                ? bootstrap.Modal.getOrCreateInstance(modalEl)
                : (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl));
            qrisModal.show();
        }
    };

    window.confirmQrisPayment = function () {
        const modalEl = document.getElementById('qrisModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        window.submitOrder(1, 'qris');
    };

    // --- INTEGRASI QRIS DINAMIS MIDTRANS SANDBOX (POS KASIR) ---
    window.startPosMidtransQris = function () {
        const cart = window.posCart || [];
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const modalEl = document.getElementById('qrisModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value || 'dine_in';
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        const idMeja = tipePesanan === 'takeaway' ? null : (mejaSelect ? mejaSelect.value : null);

        if (tipePesanan === 'dine_in' && !idMeja) {
            return alert('Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In)!');
        }

        const btnQris = document.getElementById('btn-start-midtrans-qris');
        if (btnQris) {
            btnQris.disabled = true;
            btnQris.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan Midtrans...';
        }

        const formData = {
            _token: getCsrfToken(),
            id_meja: idMeja,
            tipe_pesanan: tipePesanan,
            promo_id: document.querySelector('select[name="promo_id"]')?.value || null,
            pembayaran_langsung: 0,
            metode_pembayaran: 'qris',
            items: cart
        };

        fetch(window.manualOrderUrl || '/kasir/manual-order', {
            method: "POST",
            headers: { 
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken()
            },
            body: JSON.stringify(formData)
        })
            .then(res => res.json())
            .then(data => {
                if (btnQris) {
                    btnQris.disabled = false;
                    btnQris.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> QRIS Dinamis Midtrans';
                }

                if (data.error) {
                    alert('Gagal memproses pesanan: ' + data.error);
                    return;
                }

                if (!data.snap_token) {
                    alert('Gagal mendapatkan token Midtrans QRIS. Silakan gunakan opsi QRIS Statis Toko.');
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    alert('Script Midtrans Snap belum termuat. Periksa koneksi internet Anda.');
                    return;
                }

                // Buka Popup Resmi Midtrans Snap Sandbox
                window.snap.pay(data.snap_token, {
                    onSuccess: function (result) {
                        fetch(`/kasir/order/${data.id_pesanan}/qris-success`, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": getCsrfToken()
                            },
                            body: JSON.stringify({ midtrans_result: result })
                        })
                            .then(res => res.json())
                            .then(resSuccess => {
                                localStorage.removeItem('kasir_cart');
                                window.posCart = [];

                                if (confirm('✅ Pembayaran QRIS Berhasil (LUNAS)!\n\nIngin mencetak struk sekarang?')) {
                                    if (window.printerActive) {
                                        fetch(`/kasir/order/${data.id_pesanan}/print-thermal`, {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
                                        }).finally(() => location.reload());
                                    } else {
                                        window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                                        location.reload();
                                    }
                                } else {
                                    location.reload();
                                }
                            })
                            .catch(() => location.reload());
                    },
                    onPending: function (result) {
                        alert('⏳ Pembayaran QRIS sedang menunggu transaksi (Pending).\nPesanan tersimpan di antrean monitor kasir.');
                        localStorage.removeItem('kasir_cart');
                        window.posCart = [];
                        window.location.href = '/kasir/pesanan-aktif';
                    },
                    onError: function (result) {
                        alert('❌ Pembayaran QRIS Midtrans gagal diproses.');
                    },
                    onClose: function () {
                        if (confirm('Jendela QRIS ditutup. Pesanan #' + data.id_pesanan + ' telah tersimpan di antrean kasir sebagai Belum Bayar.\n\nBuka monitor antrean kasir sekarang?')) {
                            localStorage.removeItem('kasir_cart');
                            window.posCart = [];
                            window.location.href = '/kasir/pesanan-aktif';
                        }
                    }
                });
            })
            .catch(err => {
                if (btnQris) {
                    btnQris.disabled = false;
                    btnQris.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> QRIS Dinamis Midtrans';
                }
                alert('Terjadi kesalahan jaringan: ' + err.message);
            });
    };

    // --- LOGIKA PEMBAYARAN TUNAI ---
    window.showCashModal = function () {
        const cart = window.posCart || [];
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value || 'dine_in';
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        const idMeja = tipePesanan === 'takeaway' ? null : (mejaSelect ? mejaSelect.value : null);

        if (tipePesanan === 'dine_in' && !idMeja) {
            return alert('Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In)!');
        }

        const grandTotal = typeof window.getCurrentGrandTotal === 'function' ? window.getCurrentGrandTotal() : 0;
        posCashState.total = grandTotal;
        posCashState.nominal = grandTotal;
        posCashState.isUangPas = true;

        const totalDisplay = document.getElementById('pos-cash-total-display');
        if (totalDisplay) totalDisplay.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');

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

        const presetContainer = document.getElementById('pos-cash-presets-container');
        if (presetContainer) presetContainer.innerHTML = presetHtml;

        const customContainer = document.getElementById('pos-custom-nominal-container');
        if (customContainer) customContainer.style.display = 'none';

        const customInput = document.getElementById('pos-custom-nominal-input');
        if (customInput) customInput.value = '';

        window.updatePosKembalianUI(grandTotal, 0, true);

        const modalEl = document.getElementById('cashPaymentModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const cashModal = bootstrap.Modal.getOrCreateInstance
                ? bootstrap.Modal.getOrCreateInstance(modalEl)
                : (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl));
            cashModal.show();
        }
    };

    window.selectPosCashPreset = function (type, amount, btnEl) {
        document.querySelectorAll('.pos-cash-preset-btn').forEach(btn => {
            btn.classList.remove('btn-outline-success', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        btnEl.classList.remove('btn-outline-secondary');
        btnEl.classList.add('btn-outline-success', 'active');

        const customContainer = document.getElementById('pos-custom-nominal-container');
        const customInput = document.getElementById('pos-custom-nominal-input');

        if (type === 'pas') {
            if (customContainer) customContainer.style.display = 'none';
            posCashState.nominal = posCashState.total;
            posCashState.isUangPas = true;
            window.updatePosKembalianUI(posCashState.total, 0, true);
        } else if (type === 'fixed') {
            if (customContainer) customContainer.style.display = 'none';
            posCashState.nominal = amount;
            posCashState.isUangPas = false;
            const kembalian = Math.max(0, amount - posCashState.total);
            window.updatePosKembalianUI(amount, kembalian, false);
        } else if (type === 'custom') {
            if (customContainer) customContainer.style.display = 'block';
            if (customInput) {
                customInput.focus();
                posCashState.isUangPas = false;
                if (customInput.value) {
                    window.onPosCustomNominalChange(customInput.value);
                } else {
                    window.updatePosKembalianUI(0, 0, false, true);
                }
            }
        }
    };

    window.onPosCustomNominalChange = function (val) {
        const nominal = parseInt(val) || 0;
        posCashState.nominal = nominal;

        if (nominal < posCashState.total) {
            window.updatePosKembalianUI(nominal, 0, false, false, true);
        } else {
            const kembalian = nominal - posCashState.total;
            window.updatePosKembalianUI(nominal, kembalian, kembalian === 0);
        }
    };

    window.updatePosKembalianUI = function (nominal, kembalian, isPas, isNeedInput = false, isUnderpaid = false) {
        const labelUang = document.getElementById('pos-label-uang-diterima');
        const labelKembalian = document.getElementById('pos-label-kembalian');
        const alertMsg = document.getElementById('pos-kembalian-alert-msg');
        const card = document.getElementById('pos-kembalian-card');
        const submitBtn = document.getElementById('btn-confirm-pos-cash');

        if (isUnderpaid) {
            if (labelUang) labelUang.innerText = 'Rp ' + nominal.toLocaleString('id-ID');
            if (labelKembalian) labelKembalian.innerHTML = '<span class="text-danger">⚠️ Uang Kurang</span>';
            if (alertMsg) alertMsg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Uang yang diserahkan kurang dari total tagihan (Rp ' + posCashState.total.toLocaleString('id-ID') + ').</span>';
            if (card) {
                card.style.background = 'rgba(239, 68, 68, 0.08)';
                card.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            }
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        if (isNeedInput) {
            if (labelUang) labelUang.innerText = '-';
            if (labelKembalian) labelKembalian.innerText = '-';
            if (alertMsg) alertMsg.innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Masukkan jumlah uang tunai yang diserahkan tamu.';
            if (card) {
                card.style.background = 'rgba(255, 255, 255, 0.04)';
                card.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            }
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        if (submitBtn) submitBtn.disabled = false;
        if (labelUang) labelUang.innerText = 'Rp ' + nominal.toLocaleString('id-ID');

        if (isPas || kembalian === 0) {
            if (labelKembalian) labelKembalian.innerHTML = '<span class="text-success">Rp 0 (Uang Pas)</span>';
            if (alertMsg) alertMsg.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Uang pas, tidak ada kembalian.';
            if (card) {
                card.style.background = 'rgba(34, 197, 94, 0.08)';
                card.style.borderColor = 'rgba(34, 197, 94, 0.4)';
            }
        } else {
            if (labelKembalian) labelKembalian.innerHTML = '<span class="text-warning fw-bold fs-5">Rp ' + kembalian.toLocaleString('id-ID') + '</span>';
            if (alertMsg) alertMsg.innerHTML = '<i class="bi bi-bell-fill text-warning me-1"></i> <strong>Kembalikan ke tamu sebesar Rp ' + kembalian.toLocaleString('id-ID') + '</strong>.';
            if (card) {
                card.style.background = 'rgba(234, 179, 8, 0.09)';
                card.style.borderColor = 'rgba(234, 179, 8, 0.4)';
            }
        }
    };

    window.confirmCashPayment = function () {
        const modalEl = document.getElementById('cashPaymentModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        window.submitOrder(1, 'cash', posCashState.nominal, posCashState.isUangPas);
    };

    window.submitOrder = function (isLunas, method, nominalTunai = null, isUangPas = false) {
        const cart = window.posCart || [];
        if (cart.length === 0) return alert('Keranjang masih kosong!');

        const tipePesanan = document.querySelector('select[name="tipe_pesanan"]')?.value || 'dine_in';
        const mejaSelect = document.querySelector('select[name="id_meja"]');
        const idMeja = tipePesanan === 'takeaway' ? null : (mejaSelect ? mejaSelect.value : null);

        if (tipePesanan === 'dine_in' && !idMeja) {
            return alert('Silakan pilih nomor meja untuk pesanan Makan di Tempat (Dine In)!');
        }

        let formData = {
            _token: getCsrfToken(),
            id_meja: idMeja,
            tipe_pesanan: tipePesanan,
            promo_id: document.querySelector('select[name="promo_id"]')?.value || null,
            pembayaran_langsung: isLunas,
            metode_pembayaran: method,
            nominal_tunai: nominalTunai,
            is_uang_pas: isUangPas,
            items: cart
        };

        fetch(window.manualOrderUrl || '/kasir/manual-order', {
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
                            const totalTagihan = typeof window.getCurrentGrandTotal === 'function' ? window.getCurrentGrandTotal() : 0;
                            const kembalian = Math.max(0, nominalTunai - totalTagihan);
                            if (kembalian > 0) {
                                alertSuccessMsg += `\n\n💵 Kembalian untuk Tamu: Rp ${kembalian.toLocaleString('id-ID')}`;
                            }
                        }

                        if (confirm(alertSuccessMsg + '\n\nIngin cetak struk sekarang?')) {
                            if (window.printerActive) {
                                if (confirm('Kirim langsung ke Mesin Printer Thermal? (Pilih Cancel untuk cetak lewat Browser)')) {
                                    fetch(`/kasir/order/${data.id_pesanan}/print-thermal`, {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() }
                                    })
                                        .then(res => res.json())
                                        .then(resData => {
                                            if (resData.error) alert(resData.error);
                                            else alert(resData.message);
                                        });
                                } else {
                                    window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                                }
                            } else {
                                window.open(`/kasir/order/${data.id_pesanan}/receipt`, '_blank');
                            }
                        }
                    } else {
                        alert('Pesanan berhasil disimpan (Belum dibayar).');
                    }
                    localStorage.removeItem('kasir_cart'); // Kosongkan cart setelah berhasil
                    window.posCart = [];
                    location.reload();
                }
            });
    };

    window.updateOrderStatus = function (idPesanan, newStatus) {
        if (!confirm(`Ubah status pesanan ke ${newStatus.toUpperCase()}?`)) return;

        fetch(`/kasir/order/${idPesanan}/status`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken()
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
    };

    window.payOrder = function (idPesanan) {
        let method = prompt("Masukkan metode pembayaran untuk Pesanan #" + idPesanan + "\nKetik 'cash' atau 'qris':");
        if (!method) return;
        method = method.toLowerCase().trim();
        if (method !== 'cash' && method !== 'qris') {
            alert("Metode tidak valid. Harus 'cash' atau 'qris'.");
            return;
        }

        fetch(`/kasir/order/${idPesanan}/pay`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": getCsrfToken()
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
    };

})();
