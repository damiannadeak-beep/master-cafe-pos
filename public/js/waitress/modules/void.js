/**
 * Master Cafe POS - Waitress Module: Void / Order Cancellation
 * File: public/js/waitress/modules/void.js
 * Deskripsi: Menangani alur pembatalan pesanan (VOID) dengan otorisasi password kasir,
 *            alasan pembatalan terstruktur, serta proteksi modal tanpa macet prompt browser.
 */

(function () {
    'use strict';

    let currentVoidOrderId = null;

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    window.voidOrder = function (id, orderOptions = null) {
        currentVoidOrderId = id;

        const containerSelect = document.getElementById('void-order-selection-container');
        const idSelect = document.getElementById('void-order-id-select');
        const idDisplay = document.getElementById('void-order-id-display');

        if (orderOptions && Array.isArray(orderOptions) && orderOptions.length > 1) {
            if (containerSelect && idSelect) {
                containerSelect.classList.remove('d-none');
                idSelect.innerHTML = '';

                // Prioritaskan pesanan yang belum bayar sebagai default terpilih
                let defaultSelectedId = id;
                const unpaidFirst = orderOptions.find(o => !o.is_paid);
                if (unpaidFirst) {
                    defaultSelectedId = unpaidFirst.id;
                    currentVoidOrderId = unpaidFirst.id;
                }

                orderOptions.forEach(opt => {
                    const optEl = document.createElement('option');
                    optEl.value = opt.id;
                    optEl.innerText = opt.label;
                    if (opt.id === defaultSelectedId) optEl.selected = true;
                    idSelect.appendChild(optEl);
                });
            }
        } else {
            if (containerSelect) containerSelect.classList.add('d-none');
        }

        if (idDisplay) idDisplay.innerText = currentVoidOrderId;

        const errorAlert = document.getElementById('void-error-alert');
        if (errorAlert) {
            errorAlert.classList.add('d-none');
            errorAlert.innerText = '';
        }

        const reasonSelect = document.getElementById('void-alasan-select');
        if (reasonSelect) reasonSelect.value = 'Pelanggan Membatalkan Pesanan';

        const customReason = document.getElementById('void-alasan-custom');
        if (customReason) {
            customReason.classList.add('d-none');
            customReason.value = '';
        }

        const passInput = document.getElementById('void-password-input');
        if (passInput) {
            passInput.value = '';
            passInput.type = 'password';
        }

        const eyeBtnIcon = document.querySelector('.void-toggle-eye i');
        if (eyeBtnIcon) {
            eyeBtnIcon.className = 'bi bi-eye-slash fs-6';
        }

        const submitBtn = document.getElementById('btn-submit-void');
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Konfirmasi VOID';
        }

        if (typeof window.openModalById === 'function') {
            window.openModalById('voidOrderModal');
        }

        setTimeout(() => {
            if (passInput) passInput.focus();
        }, 300);
    };

    window.handleVoidOrderSelection = function (selectEl) {
        currentVoidOrderId = selectEl.value;
        const idDisplay = document.getElementById('void-order-id-display');
        if (idDisplay) idDisplay.innerText = currentVoidOrderId;
    };

    window.handleVoidReasonChange = function (selectEl) {
        const customText = document.getElementById('void-alasan-custom');
        if (!customText) return;
        if (selectEl.value === 'custom') {
            customText.classList.remove('d-none');
            customText.focus();
        } else {
            customText.classList.add('d-none');
        }
    };

    window.toggleVoidPasswordVisibility = function (btn) {
        const input = document.getElementById('void-password-input');
        if (!input) return;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.className = 'bi bi-eye fs-6';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'bi bi-eye-slash fs-6';
        }
    };

    window.submitVoidOrder = function (e) {
        if (e) e.preventDefault();
        if (!currentVoidOrderId) return;

        const selectEl = document.getElementById('void-alasan-select');
        const customEl = document.getElementById('void-alasan-custom');
        let alasan = selectEl ? selectEl.value : '';
        if (alasan === 'custom') {
            alasan = customEl ? customEl.value.trim() : '';
        }
        if (!alasan) {
            alasan = 'Batal';
        }

        const passInput = document.getElementById('void-password-input');
        const password = passInput ? passInput.value : '';

        const errorAlert = document.getElementById('void-error-alert');
        if (!password) {
            if (errorAlert) {
                errorAlert.innerText = 'Password akun kasir wajib diisi untuk otorisasi.';
                errorAlert.classList.remove('d-none');
            }
            if (passInput) passInput.focus();
            return;
        }

        const submitBtn = document.getElementById('btn-submit-void');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses VOID...';
        }

        const card = document.getElementById('order-card-' + currentVoidOrderId);
        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0.5';
        }

        fetch(`/kasir/order/${currentVoidOrderId}/void`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ alasan: alasan, password: password })
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    if (errorAlert) {
                        errorAlert.innerText = data.error;
                        errorAlert.classList.remove('d-none');
                    } else if (window.showToast) {
                        window.showToast(data.error, 'danger');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Konfirmasi VOID';
                    }
                    if (card) {
                        card.style.opacity = '1';
                    }
                } else if (data.errors) {
                    const msg = Object.values(data.errors).flat().join(' ');
                    if (errorAlert) {
                        errorAlert.innerText = msg;
                        errorAlert.classList.remove('d-none');
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Konfirmasi VOID';
                    }
                    if (card) {
                        card.style.opacity = '1';
                    }
                } else {
                    if (typeof window.closeModalById === 'function') {
                        window.closeModalById('voidOrderModal');
                    }
                    if (window.showToast) {
                        window.showToast(data.message || `Pesanan #${currentVoidOrderId} berhasil dibatalkan (VOID).`, 'success');
                    }
                    if (card) {
                        card.style.transform = 'scale(0.9)';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            if (typeof window.reloadActiveOrdersCards === 'function') {
                                window.reloadActiveOrdersCards(true);
                            }
                        }, 250);
                    } else {
                        if (typeof window.reloadActiveOrdersCards === 'function') {
                            window.reloadActiveOrdersCards(true);
                        }
                    }
                }
            })
            .catch(err => {
                console.error(err);
                if (errorAlert) {
                    errorAlert.innerText = 'Terjadi kesalahan jaringan saat mem-void pesanan.';
                    errorAlert.classList.remove('d-none');
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-trash3 me-1"></i> Konfirmasi VOID';
                }
                if (card) {
                    card.style.opacity = '1';
                }
            });
    };

    if (window.WaitressApp) {
        window.WaitressApp.modules.void = {
            openModal: window.voidOrder,
            submit: window.submitVoidOrder,
            handleOrderSelection: window.handleVoidOrderSelection,
            handleReasonChange: window.handleVoidReasonChange,
            togglePasswordVisibility: window.toggleVoidPasswordVisibility
        };
    }

})();
