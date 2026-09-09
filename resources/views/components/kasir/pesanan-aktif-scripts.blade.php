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

    // Auto-sync berkala tiap 5 detik jika kasir sedang di tab aktif
    setInterval(() => {
        if (!document.hidden && !document.querySelector('.modal.show')) {
            window.reloadActiveOrdersCards(true);
        }
    }, 5000);

    // --- 2. Update Status Pesanan (Proses Masak / Selesai) ---
    function updateOrderStatus(id, status, btnElement) {
        let originalHtml = '';
        if (btnElement) {
            originalHtml = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';
        }

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
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalHtml;
                }
            } else {
                const label = status === 'processing' ? 'sedang dimasak' : 'siap dihidangkan';
                if (window.showToast) {
                    window.showToast(`Pesanan #${id} ${label}!`, 'success');
                }
                // Update kartu realtime tanpa reload halaman
                window.reloadActiveOrdersCards(true);
            }
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal mengupdate status pesanan.', 'danger');
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
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

    // --- 4. Modal Pembayaran & Eksekusi Pembayaran ---
    document.addEventListener('DOMContentLoaded', function() {
        const pModalEl = document.getElementById('paymentModal');
        if (pModalEl) paymentModal = new bootstrap.Modal(pModalEl);

        const qModalEl = document.getElementById('qrisScanModal');
        if (qModalEl) qrisModal = new bootstrap.Modal(qModalEl);

        const sModalEl = document.getElementById('splitModal');
        if (sModalEl) splitModal = new bootstrap.Modal(sModalEl);

        const vModalEl = document.getElementById('verifyPaymentModal');
        if (vModalEl) verifyPaymentModal = new bootstrap.Modal(vModalEl);

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
                    if (verifyPaymentModal) verifyPaymentModal.hide();
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
        document.getElementById('email_pelanggan').value = '';
        if (paymentModal) paymentModal.show();
    }

    function processPayment(method) {
        if (paymentModal) paymentModal.hide();
        if (method === 'qris') {
            if (qrisModal) qrisModal.show();
        } else {
            executePayment('cash');
        }
    }

    function executePayment(method) {
        if (qrisModal) qrisModal.hide();
        let emailVal = document.getElementById('email_pelanggan').value;

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

        if (splitModal) splitModal.show();
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
                if (splitModal) splitModal.hide();
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
        verifyForm.action = '/kasir/order/' + id + '/verify-payment';
        
        let rejectForm = document.getElementById('rejectPaymentForm');
        rejectForm.action = '/kasir/order/' + id + '/reject-payment';
        
        if (verifyPaymentModal) verifyPaymentModal.show();
    }

    function rejectPayment() {
        if (!confirm('Yakin menolak bukti pembayaran ini? Pesanan akan dikembalikan ke status belum dibayar.')) return;
        
        const rejectForm = document.getElementById('rejectPaymentForm');
        const url = rejectForm.action;

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
            if (verifyPaymentModal) verifyPaymentModal.hide();
            if (window.showToast) window.showToast(data.message || 'Bukti pembayaran ditolak.', 'info');
            window.reloadActiveOrdersCards(true);
        })
        .catch(err => {
            console.error(err);
            if (window.showToast) window.showToast('Gagal menolak pembayaran.', 'danger');
        });
    }
</script>
