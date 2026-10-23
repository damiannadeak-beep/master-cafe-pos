/**
 * Master Cafe POS - Waitress Module: Merge Bill
 * File: public/js/waitress/modules/merge-bill.js
 * Deskripsi: Menangani penggabungan pesanan (Merge Bill / Merge Orders)
 *            antar meja atau antar tiket pesanan yang belum lunas.
 */

(function () {
    'use strict';

    let currentTargetOrderId = null;
    const DEFAULT_MERGE_BTN_HTML = '<i class="bi bi-intersect"></i> <span>Gabungkan Sekarang</span>';

    function getCsrfToken() {
        return window.WaitressHelper?.getCsrfToken?.() ||
            window.WaitressConfig?.csrfToken ||
            document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            '';
    }

    /**
     * Membuka modal penggabungan pesanan dan memuat kandidat pesanan aktif lain
     */
    window.openMergeModal = function (orderId, orderLabel) {
        currentTargetOrderId = orderId;

        const targetLabelEl = document.getElementById('merge-target-label');
        if (targetLabelEl) {
            targetLabelEl.innerHTML = `<i class="bi bi-receipt text-warning me-1.5"></i> ${orderLabel || ('Pesanan #' + orderId)}`;
        }

        const countBadge = document.getElementById('merge-candidates-count');
        const spinner = document.getElementById('merge-loading-spinner');
        const selectEl = document.getElementById('merge-source-select');
        const emptyAlert = document.getElementById('merge-empty-alert');
        const submitBtn = document.getElementById('btn-execute-merge');

        // Selalu pulihkan tombol ke kondisi normal saat modal dibuka
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = DEFAULT_MERGE_BTN_HTML;
        }

        if (countBadge) countBadge.innerText = 'Memuat...';
        if (spinner) spinner.style.display = 'block';
        if (selectEl) {
            selectEl.style.display = 'none';
            selectEl.innerHTML = '<option value="">-- Pilih Pesanan yang Ingin Digabungkan --</option>';
        }
        if (emptyAlert) emptyAlert.style.display = 'none';

        if (typeof window.openModalById === 'function') {
            window.openModalById('mergeModal');
        }

        // Fetch daftar pesanan aktif lain yang belum bayar
        fetch(`/kasir/order/${orderId}/merge-candidates`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
            .then(async res => {
                const data = await res.json().catch(() => ([]));
                if (!res.ok) throw new Error(data.message || 'Gagal memuat kandidat');
                return data;
            })
            .then(candidates => {
                if (spinner) spinner.style.display = 'none';

                if (!Array.isArray(candidates) || candidates.length === 0) {
                    if (emptyAlert) emptyAlert.style.display = 'block';
                    if (countBadge) countBadge.innerText = '0 pesanan';
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    if (countBadge) countBadge.innerText = candidates.length + ' pesanan tersedia';
                    candidates.forEach(cand => {
                        const opt = document.createElement('option');
                        opt.value = cand.id;
                        opt.textContent = cand.label;
                        selectEl.appendChild(opt);
                    });
                    if (selectEl) selectEl.style.display = 'block';
                    if (submitBtn) submitBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error('[MergeBill] Gagal memuat daftar pesanan kandidat:', err);
                if (spinner) spinner.style.display = 'none';
                if (emptyAlert) {
                    emptyAlert.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat daftar pesanan. Silakan coba lagi.';
                    emptyAlert.style.display = 'block';
                }
                if (submitBtn) submitBtn.disabled = true;
            });
    };

    /**
     * Mengeksekusi penggabungan pesanan terpilih ke pesanan utama
     */
    window.executeMerge = async function () {
        if (!currentTargetOrderId) return;

        const selectEl = document.getElementById('merge-source-select');
        const sourceOrderId = selectEl ? selectEl.value : null;

        if (!sourceOrderId) {
            if (window.showToast) {
                window.showToast('Silakan pilih pesanan yang ingin digabungkan terlebih dahulu.', 'warning');
            } else {
                alert('Silakan pilih pesanan yang ingin digabungkan.');
            }
            return;
        }

        const selectedText = selectEl.options[selectEl.selectedIndex]?.text || ('Pesanan #' + sourceOrderId);

        if (!confirm(`Apakah Anda yakin ingin menggabungkan "${selectedText}" ke dalam pesanan utama ini?\n\nSeluruh menu akan disatukan dan pesanan sumber akan ditutup.`)) {
            return;
        }

        const submitBtn = document.getElementById('btn-execute-merge');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5" role="status"></span> Menggabungkan...';
        }

        try {
            const res = await fetch(`/kasir/order/${currentTargetOrderId}/merge`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ merge_with_id: parseInt(sourceOrderId) })
            });

            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                const errMsg = data.error || data.message || (res.status === 419 ? 'Sesi kasir kedaluwarsa. Silakan muat ulang halaman.' : `Gagal menggabungkan pesanan (Kode: ${res.status}).`);
                throw new Error(errMsg);
            }

            if (typeof window.closeModalById === 'function') {
                window.closeModalById('mergeModal');
            }

            if (window.showToast) {
                window.showToast(data.message || 'Pesanan berhasil digabungkan!', 'success');
            } else {
                alert(data.message || 'Pesanan berhasil digabungkan!');
            }

            if (typeof window.reloadActiveOrdersCards === 'function') {
                setTimeout(() => {
                    window.reloadActiveOrdersCards(true);
                }, 350);
            }
        } catch (err) {
            console.error('[MergeBill] Error saat proses penggabungan:', err);
            const msg = err.message || 'Terjadi kesalahan jaringan saat menggabungkan pesanan.';
            if (window.showToast) window.showToast(msg, 'danger');
            else alert(msg);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = DEFAULT_MERGE_BTN_HTML;
            }
        }
    };

})();
