<script>
    let currentOrderId = null;
    let paymentModal = null;
    let qrisModal = null;
    let splitModal = null;
    let verifyPaymentModal = null;
    let splitOrderId = null;
    let splitDetails = [];
    let isReloadingCards = false;

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
            // Hindari menimpa jika kasir sedang membuka modal input
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
    function updateOrderStatus(id, status, btnElement) {
        const card = document.getElementById('order-card-' + id);
        const badgeContainer = card ? card.querySelector('.card-header .badge') : null;

        // ⚡ OPTIMISTIC UPDATE: Langsung ubah badge & tombol di layar kasir dalam 0 milidetik!
        if (status === 'processing') {
            if (badgeContainer) {
                badgeContainer.outerHTML = '<span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>';
            }
            if (btnElement) {
                btnElement.outerHTML = `<button class="btn btn-sm btn-success w-100 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="updateOrderStatus(${id}, 'completed', this)">
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
                // Revert jika ada error dari server
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
    }

    // --- 3. VOID Pesanan (Batalkan Tanpa Reload) ---
    function voidOrder(id) {
        let alasan = prompt('Masukkan alasan mem-VOID pesanan ini (Wajib):');
        if (!alasan) return;
        
        let password = prompt('Otorisasi Diperlukan. Masukkan password akun Anda:');
        if (!password) return;
        
        if (!confirm('Anda yakin ingin mem-VOID pesanan ini? Stok akan dikembalikan dan pesanan dibatalkan.')) return;

        const card = document.getElementById('order-card-' + id);
        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0.4';
            card.style.pointerEvents = 'none';
        }
        
        fetch(`/kasir/order/${id}/void`, {
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
                if (window.showToast) window.showToast(data.error, 'danger');
                else alert(data.error);
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            } else if (data.errors) {
                alert(Object.values(data.errors).flat().join('\n'));
                if (card) {
                    card.style.opacity = '1';
                    card.style.pointerEvents = 'auto';
                }
            } else {
                if (window.showToast) {
                    window.showToast(data.message || `Pesanan #${id} berhasil dibatalkan (VOID).`, 'success');
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
            if (window.showToast) window.showToast('Terjadi kesalahan jaringan saat mem-void pesanan.', 'danger');
            if (card) {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }
        });
    }

    // --- Helper Modal Bootstrap On-Demand (Aman & Pasti Terbuka) ---
    function getModal(elementId) {
        const el = document.getElementById(elementId);
        if (!el) return null;
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        }
        return null;
    }

    // --- 4. Modal Pembayaran & Eksekusi Pembayaran ---
    document.addEventListener('DOMContentLoaded', function() {
        // Intercept Form Verifikasi Bukti Bayar agar tidak reload halaman
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
                    const vModal = getModal('verifyPaymentModal');
                    if (vModal) vModal.hide();
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

    function payOrder(id) {
        currentOrderId = id;
        const emailInput = document.getElementById('email_pelanggan');
        if (emailInput) emailInput.value = '';
        
        const modal = getModal('paymentModal');
        if (modal) {
            modal.show();
        } else {
            console.error('Modal payment tidak dapat dibuka, ID element: paymentModal');
        }
    }

    function processPayment(method) {
        const pModal = getModal('paymentModal');
        if (pModal) pModal.hide();
        
        if (method === 'qris') {
            const qModal = getModal('qrisScanModal');
            if (qModal) qModal.show();
        } else {
            executePayment('cash');
        }
    }

    function executePayment(method) {
        const qModal = getModal('qrisScanModal');
        if (qModal) qModal.hide();
        
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
    }

    // --- 5. Split Bill (Pisah Bon Tanpa Reload) ---
    function openSplitModal(orderId, btnElement) {
        splitOrderId = orderId;
        let details = JSON.parse(btnElement.getAttribute('data-details'));
        splitDetails = details;
        document.getElementById('split-order-id').innerText = orderId;

        let html = '';
        details.forEach(item => {
            html += `
            <div class="d-flex align-items-center mb-2">
                <div class="form-check flex-grow-1">
                    <input class="form-check-input split-cb" type="checkbox" value="${item.id}" id="chk_${item.id}">
                    <label class="form-check-label text-light" for="chk_${item.id}">
                        ${item.menu ? item.menu.nama_menu : 'Menu'} (Rp ${item.subtotal.toLocaleString('id-ID')})
                    </label>
                </div>
                <div style="width: 80px;">
                    <input type="number" class="form-control text-white border-secondary form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled>
                </div>
                <span class="ms-2 small text-white-50">/ ${item.jumlah}</span>
            </div>
            `;
        });
        document.getElementById('split-items-container').innerHTML = html;

        document.querySelectorAll('.split-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                document.getElementById('qty_' + this.value).disabled = !this.checked;
            });
        });

        const sModal = getModal('splitModal');
        if (sModal) sModal.show();
    }

    function executeSplit() {
        let itemsToSplit = [];
        document.querySelectorAll('.split-cb:checked').forEach(cb => {
            let idDetail = cb.value;
            let qty = document.getElementById('qty_' + idDetail).value;
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
                const sModal = getModal('splitModal');
                if (sModal) sModal.hide();
                if (window.showToast) window.showToast(data.message || 'Bon berhasil dipisah!', 'success');
                window.reloadActiveOrdersCards(true);
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Terjadi kesalahan saat memisah bon.', 'danger');
        });
    }

    // --- 6. Print Thermal & Bukti Bayar ---
    function printThermal(id) {
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
    }

    function verifyPayment(id, imgUrl) {
        document.getElementById('verify-order-id').innerText = id;
        document.getElementById('verify-payment-image').src = imgUrl;
        
        let verifyForm = document.getElementById('verifyPaymentForm');
        if (verifyForm) verifyForm.action = '/kasir/order/' + id + '/verify-payment';
        
        let rejectForm = document.getElementById('rejectPaymentForm');
        if (rejectForm) rejectForm.action = '/kasir/order/' + id + '/reject-payment';
        
        const vModal = getModal('verifyPaymentModal');
        if (vModal) vModal.show();
    }

    function rejectPayment() {
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
            const vModal = getModal('verifyPaymentModal');
            if (vModal) vModal.hide();
            if (window.showToast) window.showToast(data.message || 'Bukti pembayaran ditolak.', 'info');
            window.reloadActiveOrdersCards(true);
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal menolak pembayaran.', 'danger');
        });
    }
</script>
