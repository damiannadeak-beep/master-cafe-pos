/**
 * Master Cafe POS - Waitress Module: Thermal Printing
 * File: public/js/waitress/modules/thermal.js
 * Deskripsi: Menangani pengiriman perintah cetak struk thermal ke printer kasir.
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
})();
