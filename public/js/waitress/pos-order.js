/**
 * Master Cafe POS - Waitress POS Order Engine (Main Orchestrator)
 * File: public/js/waitress/pos-order.js
 * Deskripsi: Entry point & lifecycle inisialisasi awal halaman kasir POS.
 *            Sub-modul terbagi di: public/js/waitress/modules/pos/
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.renderMenus === 'function') window.renderMenus();
        if (typeof window.renderCart === 'function') window.renderCart();
        if (typeof window.toggleMeja === 'function') window.toggleMeja();

        // Cek URL params untuk mode cepat "Tambah Pesanan" dari Kartu Pesanan Aktif
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const paramTipe = urlParams.get('tipe');
            const paramMeja = urlParams.get('id_meja');

            if (paramTipe) {
                const selTipe = document.getElementById('select-tipe-pesanan');
                if (selTipe) {
                    selTipe.value = paramTipe;
                    if (typeof window.toggleMeja === 'function') window.toggleMeja();
                }
            }
            if (paramMeja) {
                const selMeja = document.getElementById('select-id-meja');
                if (selMeja) {
                    selMeja.value = paramMeja;
                    const selectedText = selMeja.options[selMeja.selectedIndex]?.text || ('Meja #' + paramMeja);
                    if (window.showToast) {
                        window.showToast('Mode Tambah Menu: ' + selectedText, 'info');
                    }
                }
            }
        } catch (e) {
            console.warn('[Waitress POS] Gagal membaca query params:', e);
        }
    });

    console.info('[Waitress POS] POS Order modular engine initialized.');

})();
