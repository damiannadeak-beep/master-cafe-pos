/**
 * Master Cafe POS - Waitress Module: Modal Helper
 * File: public/js/waitress/modules/modal.js
 * Deskripsi: Helper universal untuk menampilkan dan menutup Bootstrap 5 modal
 *            lengkap dengan DOM fallback jika JS Bootstrap terkendala.
 */

(function () {
    'use strict';

    window.openModalById = function (elementId) {
        const el = document.getElementById(elementId);
        if (!el) {
            console.error('[Modal] Element tidak ditemukan: #' + elementId);
            return;
        }

        // 1. Coba lewat Bootstrap standard
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getOrCreateInstance
                    ? bootstrap.Modal.getOrCreateInstance(el)
                    : (bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el));
                inst.show();
                return;
            }
        } catch (e) {
            console.warn('[Modal] Bootstrap show error, falling back to CSS:', e);
        }

        // 2. Direct DOM Fallback jika Bootstrap JS terkendala
        el.classList.add('show');
        el.style.display = 'block';
        el.removeAttribute('aria-hidden');
        el.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');

        let backdrop = document.getElementById('modal-fallback-backdrop');
        if (!backdrop) {
            backdrop = document.createElement('div');
            backdrop.id = 'modal-fallback-backdrop';
            backdrop.className = 'modal-backdrop fade show';
            backdrop.onclick = function () { window.closeModalById(elementId); };
            document.body.appendChild(backdrop);
        }
    };

    window.closeModalById = function (elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;

        // 1. Coba lewat Bootstrap standard
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el);
                if (inst) {
                    inst.hide();
                }
            }
        } catch (e) {
            console.warn('[Modal] Bootstrap hide error:', e);
        }

        // 2. Direct DOM Cleanup Fallback
        setTimeout(() => {
            el.classList.remove('show');
            el.style.display = 'none';
            el.setAttribute('aria-hidden', 'true');
            el.removeAttribute('aria-modal');
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');

            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(b => b.remove());
        }, 150);
    };

})();
