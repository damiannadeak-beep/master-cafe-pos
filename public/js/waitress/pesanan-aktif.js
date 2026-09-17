/**
 * Master Cafe POS - Waitress Active Orders Monitor (Main Orchestrator)
 * File: public/js/waitress/pesanan-aktif.js
 * Deskripsi: Entry point & shared helper namespace untuk sub-modul antrean kasir.
 *            Sub-modul logika terbagi di: public/js/waitress/modules/
 */

(function () {
    'use strict';

    // Inisialisasi Shared Helper Universal
    window.WaitressHelper = Object.assign(window.WaitressHelper || {}, {
        getCsrfToken: function () {
            return window.WaitressConfig?.csrfToken ||
                document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                '';
        },
        getPesananAktifUrl: function () {
            return window.WaitressConfig?.pesananAktifUrl || '/kasir/pesanan-aktif';
        }
    });

    console.info('[Waitress POS] Active orders modular engine initialized.');

})();
