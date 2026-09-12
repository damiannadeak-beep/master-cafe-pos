<script>
    let currentOrderId = null;
    let currentVoidOrderId = null;
    let splitOrderId = null;
    let splitDetails = [];
    let isReloadingCards = false;

    // --- Helper Modal Universal (Bootstrap 5 + DOM Fallback Aman) ---
    window.openModalById = function(elementId) {
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
            backdrop.onclick = function() { window.closeModalById(elementId); };
            document.body.appendChild(backdrop);
        }
    };

    window.closeModalById = function(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return;

        // 1. Coba lewat Bootstrap
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const inst = bootstrap.Modal.getInstance(el);
                if (inst) {
                    inst.hide();
                    return;
                }
            }
        } catch (e) {
            console.warn('[Modal] Bootstrap hide error:', e);
        }

        // 2. Direct DOM Fallback
        el.classList.remove('show');
        el.style.display = 'none';
        el.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        const backdrop = document.getElementById('modal-fallback-backdrop');
        if (backdrop) backdrop.remove();
    };

    // --- 1. Realtime Dynamic Cards Loader (Tanpa Refresh Halaman) ---
    window.reloadActiveOrdersCards = function(silent = false) {
        if (isReloadingCards) return;
        const container = document.getElementById('active-orders-container');
        if (!container) return;

        isReloadingCards = true;
        const refreshBtn = document.getElementById('btn-refresh-orders');
        if (refreshBtn && !silent) {
            const icon = refreshBtn.querySelector('i');
            if (icon) icon.classList.add('spin-animation');
            refreshBtn.disabled = true;
        }

        fetch('{{ route("kasir.pesanan_aktif") }}?cards_only=1&_t=' + Date.now(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml',
                'Cache-Control': 'no-cache'
            }
        })
        .then(res => res.text())
        .then(html => {
            // Hindari menimpa jika kasir sedang membuka modal apa pun atau sedang melihat dialog selesai
            const anyModalOpen = document.querySelector('.modal.show');
            if (!anyModalOpen && !window.activeCompletedOrderId) {
                container.innerHTML = html;
            }
            // Tidak perlu memanggil fetchActiveOrdersCount() ganda di sini karena sudah ditangani oleh sync engine
        })
        .catch(err => {
            console.error('[PesananAktif] Gagal memuat pesanan aktif:', err);
        })
        .finally(() => {
            isReloadingCards = false;
            if (refreshBtn) {
                const icon = refreshBtn.querySelector('i');
                if (icon) icon.classList.remove('spin-animation');
                refreshBtn.disabled = false;
            }
        });
    };

    // Auto-sync berkala tiap 12 detik jika kasir sedang di tab aktif dan idle (tidak ada modal terbuka)
    setInterval(() => {
        if (!document.hidden && !document.querySelector('.modal.show') && !window.activeCompletedOrderId && !isReloadingCards) {
            window.reloadActiveOrdersCards(true);
        }
    }, 12000);

    // --- Modal Selesai & Cetak Struk Controller ---
    window.activeCompletedOrderId = null;

    window.showOrderCompletedModal = function(id) {
        window.activeCompletedOrderId = id;
        const card = document.getElementById('order-card-' + id);

        let customerName = 'Pelanggan';
        let orderType = 'Dine-In';
        let isPaid = true;

        if (card) {
            const customerEl = card.querySelector('.small.text-white.fw-bold span.text-white');
            if (customerEl) customerName = customerEl.innerText.trim();

            const typeEl = card.querySelector('.badge.rounded-pill.text-bg-warning, .badge.rounded-pill.text-bg-secondary');
            if (typeEl) orderType = typeEl.innerText.trim();

            const unpaidBadge = card.querySelector('.badge.bg-danger.border-danger');
            if (unpaidBadge && unpaidBadge.innerText.includes('Belum Lunas')) {
                isPaid = false;
            }
        }

        const subTitle = document.getElementById('orderCompletedSubtitle');
        if (subTitle) subTitle.innerText = `Pesanan #${id}`;

        const custEl = document.getElementById('orderCompletedCustomer');
        if (custEl) custEl.innerText = customerName;

        const typeEl = document.getElementById('orderCompletedType');
        if (typeEl) typeEl.innerText = orderType;

        const payBadge = document.getElementById('orderCompletedPayBadge');
        const promptText = document.getElementById('orderCompletedPromptText');
        const actionsContainer = document.getElementById('orderCompletedActions');

        if (!window.defaultOrderCompletedActionsHTML && actionsContainer) {
            window.defaultOrderCompletedActionsHTML = actionsContainer.innerHTML;
        }

        if (!isPaid) {
            if (payBadge) {
                payBadge.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1';
                payBadge.innerHTML = '<i class="bi bi-x-circle me-1"></i>Belum Lunas';
            }
            if (promptText) {
                promptText.innerHTML = '<span class="text-warning fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Pesanan telah selesai dimasak, namun belum lunas.</span><br><small class="text-white-50">Silakan terima pembayaran terlebih dahulu dari konsumen.</small>';
            }
            if (actionsContainer) {
                actionsContainer.innerHTML = `
                    <button type="button" class="btn btn-warning text-dark fw-bold py-2.5 rounded-3 d-flex align-items-center justify-content-center gap-2" onclick="window.closeModalById('orderCompletedModal'); window.payOrder(${id});">
                        <i class="bi bi-cash-stack fs-5"></i> Terima Pembayaran Sekarang
                    </button>
                    <button type="button" class="btn btn-secondary fw-semibold py-2 rounded-3 mt-1" data-bs-dismiss="modal">
                        Tutup
                    </button>
                `;
            }
        } else {
            if (payBadge) {
                payBadge.className = 'badge bg-success bg-opacity-10 text-success border border-success px-2 py-1';
                payBadge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Lunas';
            }
            if (promptText) {
                promptText.innerText = 'Pesanan telah selesai disiapkan! Apakah ingin mencetak struk transaksi sekarang?';
            }
            if (actionsContainer && window.defaultOrderCompletedActionsHTML) {
                actionsContainer.innerHTML = window.defaultOrderCompletedActionsHTML;
            }

            const browserBtn = document.getElementById('btnOrderCompletedBrowser');
            if (browserBtn) {
                browserBtn.href = `/kasir/order/${id}/receipt`;
            }

            const thermalBtn = document.getElementById('btnOrderCompletedThermal');
            if (thermalBtn) {
                thermalBtn.onclick = function() {
                    window.printThermal(id);
                };
            }
        }

        window.openModalById('orderCompletedModal');
    };

    // Inisialisasi event saat modal selesai ditutup
    document.addEventListener('DOMContentLoaded', function() {
        const completedModalEl = document.getElementById('orderCompletedModal');
        if (completedModalEl) {
            completedModalEl.addEventListener('hidden.bs.modal', function() {
                window.activeCompletedOrderId = null;
                window.reloadActiveOrdersCards(false);
            });
        }
    });

    // --- Cetak Tiket Dapur / Koki ---
    window.printKitchenTicket = function(id) {
        window.open(`/kasir/order/${id}/kitchen-receipt`, 'KitchenTicket_' + id, 'width=420,height=600,scrollbars=yes');
    };

    // --- 2. Update Status Pesanan (Optimistic Update: 0 Detik Instan!) ---
    window.updateOrderStatus = function(id, status, btnElement) {
        const card = document.getElementById('order-card-' + id);
        const badgeContainer = card ? card.querySelector('.card-header .badge') : null;

        // ⚡ OPTIMISTIC UPDATE: Langsung ubah badge & tombol di layar kasir dalam 0 milidetik!
        if (status === 'processing') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-success w-100 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus(${id}, 'completed', this)">
                    <i class="bi bi-check2-all me-1"></i> Selesai Dimasak
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan #${id} sedang dimasak!`, 'success');
        } else if (status === 'completed') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> SELESAI</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button type="button" class="btn btn-sm btn-outline-success w-100 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.showOrderCompletedModal(${id})">
                    <i class="bi bi-printer me-1"></i> Cetak Struk
                </button>`;
            }
            if (window.showToast) window.showToast(`Pesanan #${id} selesai!`, 'success');

            // Buka modal dialog konfirmasi selesai & opsi cetak struk
            window.showOrderCompletedModal(id);
        }

        // Kirim permintaan ke server di background tanpa menghalangi kasir
        fetch(`/kasir/order/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                if (window.showToast) window.showToast(data.error, 'danger');
                else alert(data.error);
                window.reloadActiveOrdersCards(true);
            } else {
                if (typeof fetchActiveOrdersCount === 'function') {
                    fetchActiveOrdersCount();
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal sinkron status pesanan ke server.', 'danger');
            window.reloadActiveOrdersCards(true);
        });
    };

    // --- 3. VOID Pesanan (Modal Dedicated & Bebas Macet Browser Prompt) ---
    window.voidOrder = function(id) {
        currentVoidOrderId = id;
        
        const idDisplay = document.getElementById('void-order-id-display');
        if (idDisplay) idDisplay.innerText = id;

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

        window.openModalById('voidOrderModal');

        setTimeout(() => {
            if (passInput) passInput.focus();
        }, 300);
    };

    window.handleVoidReasonChange = function(selectEl) {
        const customText = document.getElementById('void-alasan-custom');
        if (!customText) return;
        if (selectEl.value === 'custom') {
            customText.classList.remove('d-none');
            customText.focus();
        } else {
            customText.classList.add('d-none');
        }
    };

    window.toggleVoidPasswordVisibility = function(btn) {
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

    window.submitVoidOrder = function(e) {
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                window.closeModalById('voidOrderModal');
                if (window.showToast) {
                    window.showToast(data.message || `Pesanan #${currentVoidOrderId} berhasil dibatalkan (VOID).`, 'success');
                }
                if (card) {
                    card.style.transform = 'scale(0.9)';
                    card.style.opacity = '0';
                    setTimeout(() => {
                        window.reloadActiveOrdersCards(true);
                    }, 250);
                } else {
                    window.reloadActiveOrdersCards(true);
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

    // --- 4. Modal Pembayaran & Eksekusi Pembayaran ---
    let activeOrderPayState = {
        id: null,
        total: 0,
        nominal: 0,
        isUangPas: true,
        method: 'cash'
    };

    window.payOrder = function(id, total = 0) {
        currentOrderId = id;
        activeOrderPayState.id = id;
        activeOrderPayState.total = total || 0;
        activeOrderPayState.nominal = total || 0;
        activeOrderPayState.isUangPas = true;
        activeOrderPayState.method = 'cash';
        
        const idDisplay = document.getElementById('payment-order-id-display');
        if (idDisplay) idDisplay.innerText = id;

        const totalDisplay = document.getElementById('active-order-cash-total-display');
        if (totalDisplay) totalDisplay.innerText = 'Rp ' + (total || 0).toLocaleString('id-ID');

        const emailInput = document.getElementById('email_pelanggan');
        if (emailInput) emailInput.value = '';

        window.switchActiveOrderPayMethod('cash');
        window.renderActiveOrderCashPresets();

        window.openModalById('paymentModal');
    };

    window.switchActiveOrderPayMethod = function(method) {
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

    window.renderActiveOrderCashPresets = function() {
        const total = activeOrderPayState.total;
        let presetHtml = `
            <button type="button" class="btn btn-sm btn-outline-success active-cash-preset-btn active rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('pas', ${total}, this)">
                <i class="bi bi-check2-circle me-1"></i> Uang Pas (Rp ${total.toLocaleString('id-ID')})
            </button>
        `;

        if (total < 50000) {
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', 50000, this)">
                    Rp 50.000
                </button>
            `;
        }

        if (total < 100000 && total !== 50000) {
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', 100000, this)">
                    Rp 100.000
                </button>
            `;
        }

        if (total > 100000) {
            const nextCeil = Math.ceil((total + 1000) / 50000) * 50000;
            presetHtml += `
                <button type="button" class="btn btn-sm btn-outline-secondary active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('fixed', ${nextCeil}, this)">
                    Rp ${nextCeil.toLocaleString('id-ID')}
                </button>
            `;
        }

        presetHtml += `
            <button type="button" class="btn btn-sm btn-outline-secondary active-cash-preset-btn rounded-pill px-3 py-2 fw-bold" onclick="window.selectActiveOrderCashPreset('custom', 0, this)">
                <i class="bi bi-pencil me-1"></i> Nominal Lain
            </button>
        `;

        const container = document.getElementById('active-order-cash-presets');
        if (container) container.innerHTML = presetHtml;
        const customContainer = document.getElementById('active-order-custom-nominal-container');
        if (customContainer) customContainer.style.display = 'none';
        const customInput = document.getElementById('active-order-custom-nominal');
        if (customInput) customInput.value = '';

        window.updateActiveOrderKembalianUI(total, 0, true);
    };

    window.selectActiveOrderCashPreset = function(type, amount, btnEl) {
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

    window.onActiveOrderCustomNominalChange = function(val) {
        const nominal = parseInt(val) || 0;
        activeOrderPayState.nominal = nominal;

        if (nominal < activeOrderPayState.total) {
            window.updateActiveOrderKembalianUI(nominal, 0, false, false, true);
        } else {
            const kembalian = nominal - activeOrderPayState.total;
            window.updateActiveOrderKembalianUI(nominal, kembalian, kembalian === 0);
        }
    };

    window.updateActiveOrderKembalianUI = function(nominal, kembalian, isPas, isNeedInput = false, isUnderpaid = false) {
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

    window.submitActiveOrderPayment = function() {
        if (activeOrderPayState.method === 'qris') {
            window.closeModalById('paymentModal');
            window.openModalById('qrisScanModal');
        } else {
            window.executePayment('cash');
        }
    };

    window.executePayment = function(method) {
        window.closeModalById('qrisScanModal');
        window.closeModalById('paymentModal');
        
        let emailInput = document.getElementById('email_pelanggan');
        let emailVal = emailInput ? emailInput.value : '';

        const payload = {
            metode: method,
            email_pelanggan: emailVal
        };

        if (method === 'cash') {
            payload.nominal_tunai = activeOrderPayState.nominal;
            payload.is_uang_pas = activeOrderPayState.isUangPas;
        }

        fetch(`/kasir/order/${currentOrderId}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                let toastMsg = `Pembayaran pesanan #${paidOrderId} berhasil!`;
                if (method === 'cash' && !activeOrderPayState.isUangPas && activeOrderPayState.nominal > activeOrderPayState.total) {
                    const kembalian = activeOrderPayState.nominal - activeOrderPayState.total;
                    toastMsg += ` (Kembalian: Rp ${kembalian.toLocaleString('id-ID')})`;
                }

                if (window.showToast) {
                    window.showToast(toastMsg, 'success');
                }
                if (confirm(toastMsg + '\n\nCetak struk sekarang?')) {
                    window.open(`/kasir/order/${paidOrderId}/receipt`, '_blank');
                }
                window.reloadActiveOrdersCards(true);
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal memproses pembayaran.', 'danger');
        });
    };

    // --- 5. Split Bill (Pisah Bon Tanpa Reload) ---
    window.openSplitModal = function(orderId, btnElement) {
        splitOrderId = orderId;
        const btn = btnElement ? (btnElement.closest('button') || btnElement) : null;
        let details = [];
        if (btn) {
            let rawData = btn.getAttribute('data-details') || '';
            try {
                details = JSON.parse(rawData);
            } catch (e) {
                try {
                    // Fallback jika ada HTML entity decoding issue
                    const txt = document.createElement('textarea');
                    txt.innerHTML = rawData;
                    details = JSON.parse(txt.value);
                } catch (e2) {
                    console.error('Error parse data-details:', e2);
                    details = [];
                }
            }
        }
        splitDetails = details;
        
        const splitOrderSpan = document.getElementById('split-order-id');
        if (splitOrderSpan) splitOrderSpan.innerText = orderId;

        let html = '';
        if (details.length === 0) {
            html = '<div class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-circle me-1"></i> Data item pesanan tidak ditemukan atau gagal dimuat.</div>';
        } else {
            details.forEach(item => {
                const menuName = item.menu ? item.menu.nama_menu : (item.nama_menu || 'Menu');
                const subtotal = Number(item.subtotal || 0).toLocaleString('id-ID');
                html += `
                <div class="d-flex align-items-center mb-2 p-2 rounded" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07);">
                    <div class="form-check flex-grow-1 mb-0">
                        <input class="form-check-input split-cb" type="checkbox" value="${item.id}" id="chk_${item.id}" style="cursor: pointer;">
                        <label class="form-check-label text-light fw-semibold" for="chk_${item.id}" style="cursor: pointer;">
                            ${menuName}
                            <div class="small text-warning fw-normal">Rp ${subtotal}</div>
                        </label>
                    </div>
                    <div style="width: 75px;">
                        <input type="number" class="form-control text-white border-secondary form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled style="background-color: #0d1117;">
                    </div>
                    <span class="ms-2 small text-white-50">/ ${item.jumlah}</span>
                </div>
                `;
            });
        }
        
        const splitContainer = document.getElementById('split-items-container');
        if (splitContainer) splitContainer.innerHTML = html;

        document.querySelectorAll('.split-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                const qtyInput = document.getElementById('qty_' + this.value);
                if (qtyInput) qtyInput.disabled = !this.checked;
            });
        });

        window.openModalById('splitModal');
    };

    window.executeSplit = function() {
        let itemsToSplit = [];
        document.querySelectorAll('.split-cb:checked').forEach(cb => {
            let idDetail = cb.value;
            let qtyEl = document.getElementById('qty_' + idDetail);
            let qty = qtyEl ? qtyEl.value : 1;
            itemsToSplit.push({
                id_detail: idDetail,
                jumlah: parseInt(qty)
            });
        });

        if (itemsToSplit.length === 0) {
            if (window.showToast) window.showToast('Pilih minimal 1 item untuk dipisah.', 'warning');
            else alert('Pilih minimal 1 item untuk dipisah.');
            return;
        }

        let isAll = true;
        splitDetails.forEach(sd => {
            let found = itemsToSplit.find(i => i.id_detail == sd.id);
            if (!found || found.jumlah < sd.jumlah) {
                isAll = false;
            }
        });

        if (isAll) {
            if (window.showToast) window.showToast('Tidak bisa memisah semua item sekaligus.', 'warning');
            else alert('Anda tidak bisa memisah semua item.');
            return;
        }

        if (!confirm('Pisahkan item terpilih ke pesanan (Bon) baru?')) return;

        fetch(`/kasir/order/${splitOrderId}/split`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ split_items: itemsToSplit })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                if (window.showToast) window.showToast(data.error, 'danger');
                else alert(data.error);
            } else if (data.errors) {
                alert(Object.values(data.errors).flat().join('\n'));
            } else {
                window.closeModalById('splitModal');
                if (window.showToast) window.showToast(data.message || 'Bon berhasil dipisah!', 'success');
                window.reloadActiveOrdersCards(true);
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Terjadi kesalahan saat memisah bon.', 'danger');
        });
    };

    // --- 6. Print Thermal & Bukti Bayar ---
    window.printThermal = function(id) {
        if (!confirm('Kirim struk ini ke Printer Thermal Jaringan?')) return;
        
        fetch(`/kasir/order/${id}/print-thermal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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

    window.verifyPayment = function(id, imgUrl) {
        const orderIdEl = document.getElementById('verify-order-id');
        if (orderIdEl) orderIdEl.innerText = id;

        const imgEl = document.getElementById('verify-payment-image');
        if (imgEl) imgEl.src = imgUrl;
        
        let verifyForm = document.getElementById('verifyPaymentForm');
        if (verifyForm) verifyForm.action = '/kasir/order/' + id + '/verify-payment';
        
        let rejectForm = document.getElementById('rejectPaymentForm');
        if (rejectForm) rejectForm.action = '/kasir/order/' + id + '/reject-payment';
        
        window.openModalById('verifyPaymentModal');
    };

    window.rejectPayment = function() {
        if (!confirm('Yakin menolak bukti pembayaran ini? Pesanan akan dikembalikan ke status belum dibayar.')) return;
        
        const rejectForm = document.getElementById('rejectPaymentForm');
        const url = rejectForm ? rejectForm.action : '';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ _method: 'PUT' })
        })
        .then(res => res.json())
        .then(data => {
            window.closeModalById('verifyPaymentModal');
            if (window.showToast) window.showToast(data.message || 'Bukti pembayaran ditolak.', 'info');
            window.reloadActiveOrdersCards(true);
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal menolak pembayaran.', 'danger');
        });
    };

    // Form Intercept saat submit verifikasi bukti transfer
    document.addEventListener('DOMContentLoaded', function() {
        const verifyForm = document.getElementById('verifyPaymentForm');
        if (verifyForm) {
            verifyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const url = this.action;
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'PUT' })
                })
                .then(res => res.json())
                .then(data => {
                    window.closeModalById('verifyPaymentModal');
                    if (window.showToast) window.showToast(data.message || 'Pembayaran berhasil diverifikasi!', 'success');
                    window.reloadActiveOrdersCards(true);
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
</script>
