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
    });

    console.info('[Waitress POS] POS Order modular engine initialized.');

})();
