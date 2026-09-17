/**
 * Master Cafe POS - Waitress Toast Notification System
 * File: public/js/waitress/toast.js
 * Deskripsi: Sistem popup notifikasi toast modern & override alert() untuk UX kasir yang elegan.
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        window.showToast = function (message, type = 'success') {
            const toastContainer = document.getElementById('toast-container') || (function () {
                const div = document.createElement('div');
                div.id = 'toast-container';
                div.className = 'toast-container position-fixed bottom-0 end-0 p-3';
                div.style.zIndex = '1095';
                document.body.appendChild(div);
                return div;
            })();

            const toastId = 'toast-' + Date.now();

            const themes = {
                success: {
                    indicator: '#22c55e',
                    iconBg: 'rgba(34, 197, 94, 0.16)',
                    iconColor: '#4ade80',
                    icon: 'bi-check-circle-fill'
                },
                danger: {
                    indicator: '#ef4444',
                    iconBg: 'rgba(239, 68, 68, 0.16)',
                    iconColor: '#f87171',
                    icon: 'bi-exclamation-octagon-fill'
                },
                error: {
                    indicator: '#ef4444',
                    iconBg: 'rgba(239, 68, 68, 0.16)',
                    iconColor: '#f87171',
                    icon: 'bi-exclamation-octagon-fill'
                },
                warning: {
                    indicator: '#f59e0b',
                    iconBg: 'rgba(245, 158, 11, 0.16)',
                    iconColor: '#fbbf24',
                    icon: 'bi-exclamation-triangle-fill'
                },
                info: {
                    indicator: '#3b82f6',
                    iconBg: 'rgba(59, 130, 246, 0.16)',
                    iconColor: '#60a5fa',
                    icon: 'bi-info-circle-fill'
                }
            };

            const theme = themes[type] || themes.warning;
            const formattedMsg = String(message || '').replace(/\n/g, '<br>');

            const toastHtml = `
                <div id="${toastId}" class="toast master-toast align-items-center border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="master-toast-indicator" style="background: ${theme.indicator};"></div>
                    <div class="master-toast-content">
                        <div class="master-toast-icon" style="background: ${theme.iconBg}; color: ${theme.iconColor};">
                            <i class="bi ${theme.icon}"></i>
                        </div>
                        <div class="master-toast-text">${formattedMsg}</div>
                        <button type="button" class="master-toast-close btn-touch" data-bs-dismiss="toast" aria-label="Close">
                            <i class="bi bi-x-lg" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);
            const toastElement = document.getElementById(toastId);
            if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
                const toast = new bootstrap.Toast(toastElement, { delay: 4000 });
                toast.show();
            }

            toastElement.addEventListener('hidden.bs.toast', function () {
                toastElement.remove();
            });
        };

        // Override native alert dengan toast non-blocking
        window.nativeAlert = window.alert;
        window.alert = function (msg) {
            window.showToast(msg, 'warning');
        };
    });

})();
