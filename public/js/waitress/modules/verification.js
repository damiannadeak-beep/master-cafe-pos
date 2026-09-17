/**
 * Master Cafe POS - Waitress Module: Thermal Printing & Payment Verification
 * File: public/js/waitress/modules/verification.js
 * Deskripsi: Menangani pengiriman cetak struk thermal ke printer jaringan kasir,
 *            serta modal verifikasi / penolakan bukti transfer manual konsumen.
 */

(function () {
    'use strict';

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    window.printThermal = function (id) {
        if (!confirm('Kirim struk ini ke Printer Thermal Jaringan?')) return;

        fetch(`/kasir/order/${id}/print-thermal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            }
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    if (window.showToast) window.showToast(data.error, 'danger');
                    else alert(data.error);
                } else {
                    if (window.showToast) window.showToast(data.message, 'success');
                    else alert(data.message);
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Gagal mengirim ke printer thermal.', 'danger');
            });
    };

    window.verifyPayment = function (id, imgUrl) {
        const orderIdEl = document.getElementById('verify-order-id');
        if (orderIdEl) orderIdEl.innerText = id;

        const imgEl = document.getElementById('verify-payment-image');
        if (imgEl) imgEl.src = imgUrl;

        let verifyForm = document.getElementById('verifyPaymentForm');
        if (verifyForm) verifyForm.action = '/kasir/order/' + id + '/verify-payment';

        let rejectForm = document.getElementById('rejectPaymentForm');
        if (rejectForm) rejectForm.action = '/kasir/order/' + id + '/reject-payment';

        if (typeof window.openModalById === 'function') {
            window.openModalById('verifyPaymentModal');
        }
    };

    window.rejectPayment = function () {
        if (!confirm('Yakin menolak bukti pembayaran ini? Pesanan akan dikembalikan ke status belum dibayar.')) return;

        const rejectForm = document.getElementById('rejectPaymentForm');
        const url = rejectForm ? rejectForm.action : '';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'PUT' })
        })
            .then(res => res.json())
            .then(data => {
                if (typeof window.closeModalById === 'function') {
                    window.closeModalById('verifyPaymentModal');
                }
                if (window.showToast) window.showToast(data.message || 'Bukti pembayaran ditolak.', 'info');
                if (typeof window.reloadActiveOrdersCards === 'function') {
                    window.reloadActiveOrdersCards(true);
                }
            })
            .catch(err => {
                console.error(err);
                if (window.showToast) window.showToast('Gagal menolak pembayaran.', 'danger');
            });
    };

    // Form Intercept saat submit verifikasi bukti transfer
    document.addEventListener('DOMContentLoaded', function () {
        const verifyForm = document.getElementById('verifyPaymentForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function (e) {
                e.preventDefault();
                const url = this.action;
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'PUT' })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (typeof window.closeModalById === 'function') {
                            window.closeModalById('verifyPaymentModal');
                        }
                        if (window.showToast) window.showToast(data.message || 'Pembayaran berhasil diverifikasi!', 'success');
                        if (typeof window.reloadActiveOrdersCards === 'function') {
                            window.reloadActiveOrdersCards(true);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        if (window.showToast) window.showToast('Gagal memverifikasi pembayaran.', 'danger');
                    })
                    .finally(() => {
                        if (submitBtn) submitBtn.disabled = false;
                    });
            });
        }
    });

})();
