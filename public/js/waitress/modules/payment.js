/**
 * Master Cafe POS - Waitress Module: Payment Engine
 * File: public/js/waitress/modules/payment.js
 * Deskripsi: Menangani alur penerimaan kasir, kalkulasi uang pas & pecahan,
 *            perhitungan kembalian otomatis, metode pembayaran tunai & QRIS Midtrans.
 */

(function () {
    'use strict';

    let currentOrderId = null;
    let activeOrderPayState = {
        id: null,
        orderIds: [],
        total: 0,
        nominal: 0,
        isUangPas: true,
        method: 'cash'
    };

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    window.payGroupOrders = function (idsStr, total = 0, prefilledUangDiterima = 0, prefilledUangKembalian = 0, subOrdersData = null) {
        const ids = (typeof idsStr === 'string' && idsStr.includes(','))
            ? idsStr.split(',').map(s => parseInt(s.trim()))
            : [parseInt(idsStr)];
        const primaryId = ids[0];
        window.payOrder(primaryId, total, prefilledUangDiterima, prefilledUangKembalian, ids, subOrdersData);
    };

    window.payOrder = function (id, total = 0, prefilledUangDiterima = 0, prefilledUangKembalian = 0, orderIds = null, subOrdersData = null) {
        currentOrderId = id;
        activeOrderPayState.id = id;
        activeOrderPayState.orderIds = orderIds || (typeof id === 'string' && id.includes(',') ? id.split(',').map(s => parseInt(s.trim())) : [parseInt(id)]);
        activeOrderPayState.subOrdersData = subOrdersData || [];

        // Failsafe: Read total and cash info from DOM data attributes if total is missing/0
        const cardEl = document.getElementById(`order-card-${id}`);
        if ((!total || total <= 0) && cardEl) {
            const domTotal = parseInt(cardEl.getAttribute('data-total')) || 0;
            if (domTotal > 0) total = domTotal;
            if (!prefilledUangDiterima) {
                prefilledUangDiterima = parseInt(cardEl.getAttribute('data-uang-diterima')) || 0;
            }
            if (!prefilledUangKembalian) {
                prefilledUangKembalian = parseInt(cardEl.getAttribute('data-uang-kembalian')) || 0;
            }
        }

        activeOrderPayState.total = total || 0;

        if (prefilledUangDiterima && prefilledUangDiterima >= (total || 0)) {
            activeOrderPayState.nominal = prefilledUangDiterima;
            activeOrderPayState.isUangPas = (prefilledUangDiterima === (total || 0));
        } else {
            activeOrderPayState.nominal = total || 0;
            activeOrderPayState.isUangPas = true;
        }
        activeOrderPayState.method = 'cash';

        const idDisplay = document.getElementById('payment-order-id-display');
        if (idDisplay) {
            if (activeOrderPayState.orderIds && activeOrderPayState.orderIds.length > 1) {
                idDisplay.innerText = activeOrderPayState.orderIds.join(' & #');
            } else {
                idDisplay.innerText = id;
            }
        }

        const totalDisplay = document.getElementById('active-order-cash-total-display');
        if (totalDisplay) totalDisplay.innerText = 'Rp ' + (total || 0).toLocaleString('id-ID');

        const emailInput = document.getElementById('email_pelanggan');
        if (emailInput) emailInput.value = '';

        // Render checklist pilihan pesanan gabungan (Cara 2)
        window.renderSelectivePaymentOrders(subOrdersData);

        window.switchActiveOrderPayMethod('cash');
        window.renderActiveOrderCashPresets(prefilledUangDiterima);

        if (typeof window.openModalById === 'function') {
            window.openModalById('paymentModal');
        }
    };

    /**
     * Render daftar checkbox pesanan jika merupakan pesanan gabungan (Split Bill)
     */
    window.renderSelectivePaymentOrders = function (subOrdersData) {
        const container = document.getElementById('payment-selective-orders-container');
        const listEl = document.getElementById('payment-selective-orders-list');
        const alertEl = document.getElementById('payment-selective-orders-alert');

        if (!container || !listEl) return;

        if (!Array.isArray(subOrdersData) || subOrdersData.length <= 1) {
            container.style.display = 'none';
            listEl.innerHTML = '';
            if (alertEl) alertEl.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        if (alertEl) alertEl.style.display = 'none';

        let html = '';
        subOrdersData.forEach(order => {
            html += `
                <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-between selective-order-row" 
                     id="selective-order-row-${order.id}" 
                     style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.12); cursor: pointer; transition: all 0.15s ease;"
                     onclick="window.toggleOrderPaymentCheckbox(${order.id}, event)">
                    <div class="d-flex align-items-center gap-2.5">
                        <input class="form-check-input mt-0 payment-order-checkbox" 
                               type="checkbox" 
                               value="${order.id}" 
                               id="chk-pay-order-${order.id}" 
                               data-total="${order.total}" 
                               checked 
                               style="width: 1.25rem; height: 1.25rem; cursor: pointer; accent-color: #2ea043;"
                               onclick="event.stopPropagation(); window.onPaymentOrderCheckboxChange();">
                        <div>
                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                <span class="fw-bold text-white" style="font-size: 0.85rem;">${order.label}</span>
                                <span class="badge bg-dark border border-secondary text-info px-1.5 py-0.5" style="font-size: 0.68rem;">${order.customer}</span>
                            </div>
                            <small class="text-white-50 d-block" style="font-size: 0.74rem;">${order.summary || ''}</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="fw-bold text-accent" style="font-size: 0.88rem;">Rp ${Number(order.total).toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
        });

        listEl.innerHTML = html;
    };

    /**
     * Handler perubahan checkbox pemilihan pesanan
     */
    window.onPaymentOrderCheckboxChange = function () {
        const checkboxes = document.querySelectorAll('.payment-order-checkbox');
        const alertEl = document.getElementById('payment-selective-orders-alert');
        const submitBtn = document.getElementById('btn-submit-active-order-pay');
        const idDisplay = document.getElementById('payment-order-id-display');

        let selectedIds = [];
        let newTotal = 0;

        checkboxes.forEach(chk => {
            const row = document.getElementById(`selective-order-row-${chk.value}`);
            if (chk.checked) {
                selectedIds.push(parseInt(chk.value));
                newTotal += (parseInt(chk.getAttribute('data-total')) || 0);
                if (row) {
                    row.style.background = 'rgba(46, 160, 67, 0.12)';
                    row.style.borderColor = 'rgba(46, 160, 67, 0.45)';
                }
            } else {
                if (row) {
                    row.style.background = 'rgba(255, 255, 255, 0.02)';
                    row.style.borderColor = 'rgba(255, 255, 255, 0.08)';
                }
            }
        });

        if (selectedIds.length === 0) {
            if (alertEl) alertEl.style.display = 'block';
            if (submitBtn) submitBtn.disabled = true;
            activeOrderPayState.total = 0;
            activeOrderPayState.orderIds = [];
            const totalDisplay = document.getElementById('active-order-cash-total-display');
            if (totalDisplay) totalDisplay.innerText = 'Rp 0';
            window.updateActiveOrderKembalianUI(0, 0, false, false, false);
            return;
        }

        if (alertEl) alertEl.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;

        activeOrderPayState.orderIds = selectedIds;
        activeOrderPayState.id = selectedIds[0];
        currentOrderId = selectedIds[0];
        activeOrderPayState.total = newTotal;
        activeOrderPayState.nominal = newTotal;
        activeOrderPayState.isUangPas = true;

        if (idDisplay) {
            idDisplay.innerText = selectedIds.join(' & #');
        }

        const totalDisplay = document.getElementById('active-order-cash-total-display');
        if (totalDisplay) totalDisplay.innerText = 'Rp ' + newTotal.toLocaleString('id-ID');

        window.renderActiveOrderCashPresets(newTotal);
        window.updateActiveOrderKembalianUI(newTotal, 0, true);
    };

    window.toggleOrderPaymentCheckbox = function (orderId, event) {
        if (event && event.target && event.target.tagName === 'INPUT') return;
        const chk = document.getElementById(`chk-pay-order-${orderId}`);
        if (chk) {
            chk.checked = !chk.checked;
            window.onPaymentOrderCheckboxChange();
        }
    };

    window.toggleAllPaymentCheckboxes = function (checkAll) {
        const checkboxes = document.querySelectorAll('.payment-order-checkbox');
        checkboxes.forEach(chk => {
            chk.checked = checkAll;
        });
        window.onPaymentOrderCheckboxChange();
    };

    window.switchActiveOrderPayMethod = function (method) {
        activeOrderPayState.method = method;
        const btnCash = document.getElementById('tab-btn-cash');
        const btnQris = document.getElementById('tab-btn-qris');
        const cashSection = document.getElementById('active-order-cash-section');
        const submitBtn = document.getElementById('btn-submit-active-order-pay');

        if (method === 'cash') {
            btnCash?.classList.add('active', 'btn-outline-success');
            btnCash?.classList.remove('btn-outline-secondary');
            btnQris?.classList.remove('active', 'btn-outline-primary');
            btnQris?.classList.add('btn-outline-secondary');
            if (cashSection) cashSection.style.display = 'block';
            if (submitBtn) submitBtn.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Selesaikan Pembayaran Tunai';
        } else {
            btnQris?.classList.add('active', 'btn-outline-primary');
            btnQris?.classList.remove('btn-outline-secondary');
            btnCash?.classList.remove('active', 'btn-outline-success');
            btnCash?.classList.add('btn-outline-secondary');
            if (cashSection) cashSection.style.display = 'none';
            if (submitBtn) submitBtn.innerHTML = '<i class="bi bi-qr-code-scan me-1"></i> Lanjut ke Scan QRIS';
        }
    };

    window.renderActiveOrderCashPresets = function (prefilledNominal = 0) {
        const total = activeOrderPayState.total;
        const selectedNominal = (prefilledNominal && prefilledNominal >= total) ? prefilledNominal : (activeOrderPayState.nominal || total);
        const isUangPas = (selectedNominal === total);

        let presetHtml = `
            <button type="button" class="btn btn-sm ${isUangPas ? 'btn-outline-success active' : 'btn-outline-secondary'} active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('pas', ${total}, this)">
                <i class="bi bi-check2-circle me-1"></i> Uang Pas (Rp ${total.toLocaleString('id-ID')})
            </button>
        `;

        if (total < 50000) {
            const is50k = (selectedNominal === 50000);
            presetHtml += `
                <button type="button" class="btn btn-sm ${is50k ? 'btn-outline-success active' : 'btn-outline-secondary'} active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', 50000, this)">
                    Rp 50.000
                </button>
            `;
        }

        if (total < 100000 && total !== 50000) {
            const is100k = (selectedNominal === 100000);
            presetHtml += `
                <button type="button" class="btn btn-sm ${is100k ? 'btn-outline-success active' : 'btn-outline-secondary'} active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', 100000, this)">
                    Rp 100.000
                </button>
            `;
        }

        const nextCeil = total > 100000 ? Math.ceil((total + 1000) / 50000) * 50000 : 0;
        if (total > 100000) {
            const isCeil = (selectedNominal === nextCeil);
            presetHtml += `
                <button type="button" class="btn btn-sm ${isCeil ? 'btn-outline-success active' : 'btn-outline-secondary'} active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', ${nextCeil}, this)">
                    Rp ${nextCeil.toLocaleString('id-ID')}
                </button>
            `;
        }

        const isStandardPreset = (selectedNominal === total || selectedNominal === 50000 || selectedNominal === 100000 || (nextCeil && selectedNominal === nextCeil));
        const isCustom = !isStandardPreset && selectedNominal > total;

        presetHtml += `
            <button type="button" class="btn btn-sm ${isCustom ? 'btn-outline-success active' : 'btn-outline-secondary'} active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('custom', 0, this)">
                <i class="bi bi-pencil me-1"></i> Nominal Lain
            </button>
        `;

        const container = document.getElementById('active-order-cash-presets');
        if (container) container.innerHTML = presetHtml;
        const customContainer = document.getElementById('active-order-custom-nominal-container');
        if (customContainer) customContainer.style.display = isCustom ? 'block' : 'none';
        const customInput = document.getElementById('active-order-custom-nominal');
        if (customInput) customInput.value = isCustom ? selectedNominal : '';

        const kembalian = Math.max(0, selectedNominal - total);
        window.updateActiveOrderKembalianUI(selectedNominal, kembalian, isUangPas);
    };

    window.selectActiveOrderCashPreset = function (type, amount, btnEl) {
        document.querySelectorAll('.active-cash-preset-btn').forEach(btn => {
            btn.classList.remove('btn-outline-success', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        btnEl.classList.remove('btn-outline-secondary');
        btnEl.classList.add('btn-outline-success', 'active');

        const customContainer = document.getElementById('active-order-custom-nominal-container');
        const customInput = document.getElementById('active-order-custom-nominal');

        if (type === 'pas') {
            if (customContainer) customContainer.style.display = 'none';
            activeOrderPayState.nominal = activeOrderPayState.total;
            activeOrderPayState.isUangPas = true;
            window.updateActiveOrderKembalianUI(activeOrderPayState.total, 0, true);
        } else if (type === 'fixed') {
            if (customContainer) customContainer.style.display = 'none';
            activeOrderPayState.nominal = amount;
            activeOrderPayState.isUangPas = false;
            const kembalian = Math.max(0, amount - activeOrderPayState.total);
            window.updateActiveOrderKembalianUI(amount, kembalian, false);
        } else if (type === 'custom') {
            if (customContainer) customContainer.style.display = 'block';
            if (customInput) {
                customInput.focus();
                if (customInput.value) {
                    window.onActiveOrderCustomNominalChange(customInput.value);
                } else {
                    window.updateActiveOrderKembalianUI(0, 0, false, true);
                }
            }
            activeOrderPayState.isUangPas = false;
        }
    };

    window.onActiveOrderCustomNominalChange = function (val) {
        const nominal = parseInt(val) || 0;
        activeOrderPayState.nominal = nominal;

        if (nominal < activeOrderPayState.total) {
            window.updateActiveOrderKembalianUI(nominal, 0, false, false, true);
        } else {
            const kembalian = nominal - activeOrderPayState.total;
            window.updateActiveOrderKembalianUI(nominal, kembalian, kembalian === 0);
        }
    };

    window.updateActiveOrderKembalianUI = function (nominal, kembalian, isPas, isNeedInput = false, isUnderpaid = false) {
        const labelUang = document.getElementById('active-order-label-uang');
        const labelKembalian = document.getElementById('active-order-label-kembalian');
        const alertMsg = document.getElementById('active-order-kembalian-alert');
        const card = document.getElementById('active-order-kembalian-card');
        const submitBtn = document.getElementById('btn-submit-active-order-pay');

        if (isUnderpaid) {
            if (labelUang) labelUang.innerText = 'Rp ' + nominal.toLocaleString('id-ID');
            if (labelKembalian) labelKembalian.innerHTML = '<span class="text-danger">⚠️ Uang Kurang</span>';
            if (alertMsg) alertMsg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Uang kurang dari total tagihan (Rp ' + activeOrderPayState.total.toLocaleString('id-ID') + ').</span>';
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
            if (alertMsg) alertMsg.innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Masukkan jumlah uang tunai yang diterima.';
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
            if (alertMsg) alertMsg.innerHTML = '<i class="bi bi-bell-fill text-warning me-1"></i> <strong>Kembalikan uang ke tamu sebesar Rp ' + kembalian.toLocaleString('id-ID') + '</strong>.';
            if (card) {
                card.style.background = 'rgba(234, 179, 8, 0.09)';
                card.style.borderColor = 'rgba(234, 179, 8, 0.4)';
            }
        }
    };

    window.submitActiveOrderPayment = function () {
        if (activeOrderPayState.method === 'qris') {
            window.closeModalById('paymentModal');
            window.openModalById('qrisScanModal');
        } else {
            window.executePayment('cash');
        }
    };

    window.executePayment = function (method) {
        if (!activeOrderPayState.orderIds || activeOrderPayState.orderIds.length === 0) {
            if (window.showToast) window.showToast('Pilih minimal satu pesanan untuk dibayar.', 'warning');
            else alert('Pilih minimal satu pesanan untuk dibayar.');
            return;
        }

        window.closeModalById('qrisScanModal');
        window.closeModalById('paymentModal');

        let emailInput = document.getElementById('email_pelanggan');
        let emailVal = emailInput ? emailInput.value : '';

        const payload = {
            metode: method,
            email_pelanggan: emailVal,
            order_ids: activeOrderPayState.orderIds
        };

        if (method === 'cash') {
            payload.nominal_tunai = activeOrderPayState.nominal;
            payload.is_uang_pas = activeOrderPayState.isUangPas;
        }

        fetch(`/kasir/order/${currentOrderId}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    if (window.showToast) window.showToast(data.error, 'danger');
                    else alert(data.error);
                } else {
                    const paidOrderId = currentOrderId;
                    const receiptParam = (activeOrderPayState.orderIds && activeOrderPayState.orderIds.length > 1)
                        ? activeOrderPayState.orderIds.join(',')
                        : paidOrderId;
                    const displayId = (activeOrderPayState.orderIds && activeOrderPayState.orderIds.length > 1)
                        ? activeOrderPayState.orderIds.join(' & #')
                        : paidOrderId;

                    let toastMsg = `Pembayaran pesanan #${displayId} berhasil!`;
                    if (method === 'cash' && !activeOrderPayState.isUangPas && activeOrderPayState.nominal > activeOrderPayState.total) {
                        const kembalian = activeOrderPayState.nominal - activeOrderPayState.total;
                        toastMsg += ` (Kembalian: Rp ${kembalian.toLocaleString('id-ID')})`;
                    }

                    if (window.showToast) {
                        window.showToast(toastMsg, 'success');
                    }
                    if (confirm(toastMsg + '\n\nCetak struk sekarang?')) {
                        window.open(`/kasir/order/${receiptParam}/receipt`, '_blank');
                    }
                    if (typeof window.reloadActiveOrdersCards === 'function') {
                        window.reloadActiveOrdersCards(true);
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Gagal memproses pembayaran.', 'danger');
            });
    };

    // --- INTEGRASI QRIS DINAMIS MIDTRANS SANDBOX (PESANAN AKTIF) ---
    window.openActiveOrderMidtransSnap = function () {
        const orderId = currentOrderId || activeOrderPayState.id;
        if (!orderId) return alert('Pesanan tidak ditemukan.');

        const btnSnap = document.getElementById('btn-active-order-midtrans-qris');
        if (btnSnap) {
            btnSnap.disabled = true;
            btnSnap.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Menghubungkan Midtrans...';
        }

        fetch(`/kasir/order/${orderId}/snap-token`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (btnSnap) {
                    btnSnap.disabled = false;
                    btnSnap.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Tampilkan QRIS Dinamis Midtrans';
                }

                if (data.error) {
                    alert('Gagal mengambil kode Midtrans: ' + data.error);
                    return;
                }

                if (!data.snap_token) {
                    alert('Gagal mendapatkan token Midtrans QRIS.');
                    return;
                }

                if (typeof window.snap === 'undefined') {
                    alert('Script Midtrans Snap belum termuat. Periksa koneksi internet Anda.');
                    return;
                }

                window.closeModalById('qrisScanModal');

                window.snap.pay(data.snap_token, {
                    onSuccess: function (result) {
                        let emailInput = document.getElementById('email_pelanggan');
                        let emailVal = emailInput ? emailInput.value : '';

                        fetch(`/kasir/order/${orderId}/qris-success`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ email_pelanggan: emailVal, midtrans_result: result })
                        })
                            .then(res => res.json())
                            .then(resSuccess => {
                                if (window.showToast) window.showToast('✅ Pembayaran QRIS Midtrans LUNAS!', 'success');
                                if (confirm('✅ Pembayaran QRIS Berhasil (LUNAS)!\n\nIngin mencetak struk sekarang?')) {
                                    window.open(`/kasir/order/${orderId}/receipt`, '_blank');
                                }
                                if (typeof window.reloadActiveOrdersCards === 'function') {
                                    window.reloadActiveOrdersCards(true);
                                }
                            })
                            .catch(() => {
                                if (typeof window.reloadActiveOrdersCards === 'function') {
                                    window.reloadActiveOrdersCards(true);
                                }
                            });
                    },
                    onPending: function (result) {
                        alert('⏳ Pembayaran QRIS sedang diproses oleh pelanggan (Pending).');
                        if (typeof window.reloadActiveOrdersCards === 'function') {
                            window.reloadActiveOrdersCards(true);
                        }
                    },
                    onError: function (result) {
                        alert('❌ Pembayaran QRIS Midtrans gagal diproses.');
                    },
                    onClose: function () {
                        // Jendela ditutup oleh kasir/pelanggan
                    }
                });
            })
            .catch(err => {
                if (btnSnap) {
                    btnSnap.disabled = false;
                    btnSnap.innerHTML = '<i class="bi bi-lightning-charge-fill me-1"></i> Tampilkan QRIS Dinamis Midtrans';
                }
                alert('Terjadi kesalahan jaringan: ' + err.message);
            });
    };

})();

