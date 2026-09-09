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
            // Hindari menimpa jika kasir sedang membuka modal input/pembayaran/void
            const anyModalOpen = document.querySelector('.modal.show');
            if (!anyModalOpen) {
                container.innerHTML = html;
            }
            if (typeof fetchActiveOrdersCount === 'function') {
                fetchActiveOrdersCount();
            }
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

    // Auto-sync berkala tiap 6 detik jika kasir sedang di tab aktif dan idle
    setInterval(() => {
        if (!document.hidden && !document.querySelector('.modal.show') && !isReloadingCards) {
            window.reloadActiveOrdersCards(true);
        }
    }, 6000);

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
                badgeContainer.outerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> SIAP DIHIDANGKAN</span>';
            }
            if (btnElement) {
                btnElement.remove();
            }
            if (window.showToast) window.showToast(`Pesanan #${id} selesai dimasak!`, 'success');
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
        if (passInput) passInput.value = '';

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
            if (icon) icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            if (icon) icon.className = 'bi bi-eye';
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
    window.payOrder = function(id) {
        currentOrderId = id;
        
        const idDisplay = document.getElementById('payment-order-id-display');
        if (idDisplay) idDisplay.innerText = id;

        const emailInput = document.getElementById('email_pelanggan');
        if (emailInput) emailInput.value = '';
        
        window.openModalById('paymentModal');
    };

    window.processPayment = function(method) {
        window.closeModalById('paymentModal');
        
        if (method === 'qris') {
            window.openModalById('qrisScanModal');
        } else {
            window.executePayment('cash');
        }
    };

    window.executePayment = function(method) {
        window.closeModalById('qrisScanModal');
        
        let emailInput = document.getElementById('email_pelanggan');
        let emailVal = emailInput ? emailInput.value : '';

        fetch(`/kasir/order/${currentOrderId}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ metode: method, email_pelanggan: emailVal })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                if (window.showToast) window.showToast(data.error, 'danger');
                else alert(data.error);
            } else {
                const paidOrderId = currentOrderId;
                if (window.showToast) {
                    window.showToast(`Pembayaran pesanan #${paidOrderId} berhasil!`, 'success');
                }
                if (confirm('Pembayaran berhasil! Cetak struk sekarang?')) {
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
        let details = [];
        try {
            details = JSON.parse(btnElement.getAttribute('data-details'));
        } catch (e) {
            console.error('Error parse data-details:', e);
            details = [];
        }
        splitDetails = details;
        
        const splitOrderSpan = document.getElementById('split-order-id');
        if (splitOrderSpan) splitOrderSpan.innerText = orderId;

        let html = '';
        details.forEach(item => {
            html += `
            <div class="d-flex align-items-center mb-2">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input split-cb" type="checkbox" value="${item.id}" id="chk_${item.id}">
                    <label class="form-check-label text-light" for="chk_${item.id}">
                        ${item.menu ? item.menu.nama_menu : 'Menu'} (Rp ${Number(item.subtotal).toLocaleString('id-ID')})
                    </label>
                </div>
                <div style="width: 80px;">
                    <input type="number" class="form-control text-white border-secondary form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled>
                </div>
                <span class="ms-2 small text-white-50">/ ${item.jumlah}</span>
            </div>
            `;
        });
        
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
