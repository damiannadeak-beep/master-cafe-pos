<script>
    // JS Logic untuk Update Status dan Pay Order (sama dengan di POS)
    function updateOrderStatus(id, status) {
        if(!confirm('Ubah status pesanan ini?')) return;
        
        fetch(`/kasir/order/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert('Status diperbarui!');
                location.reload();
            }
        });
    }

    function voidOrder(id) {
        let alasan = prompt('Masukkan alasan mem-VOID pesanan ini (Wajib):');
        if(!alasan) return; // batalkan jika tidak mengisi alasan
        
        let password = prompt('Otorisasi Diperlukan. Masukkan password akun Anda:');
        if(!password) return; // batalkan jika tidak mengisi password
        
        if(!confirm('Anda yakin ingin mem-VOID pesanan ini? Stok akan dikembalikan dan pesanan dibatalkan.')) return;
        
        fetch(`/kasir/order/${id}/void`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ alasan: alasan, password: password })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert(data.message);
                location.reload();
            }
        });
    }

    let currentOrderId = null;
    let paymentModal = null;
    let qrisModal = null;
    let splitModal = null;
    let splitOrderId = null;
    let splitDetails = [];

    document.addEventListener('DOMContentLoaded', function() {
        paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        qrisModal = new bootstrap.Modal(document.getElementById('qrisScanModal'));
        splitModal = new bootstrap.Modal(document.getElementById('splitModal'));
    });

    function payOrder(id) {
        currentOrderId = id;
        document.getElementById('email_pelanggan').value = ''; // Reset input email
        paymentModal.show();
    }

    function processPayment(method) {
        paymentModal.hide();
        if (method === 'qris') {
            qrisModal.show();
        } else {
            executePayment('cash');
        }
    }

    function executePayment(method) {
        if(qrisModal) qrisModal.hide();
        let emailVal = document.getElementById('email_pelanggan').value;

        fetch(`/kasir/order/${currentOrderId}/pay`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ metode: method, email_pelanggan: emailVal })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                if (confirm('Pembayaran berhasil! Cetak struk sekarang?')) {
                    window.open(`/kasir/order/${currentOrderId}/receipt`, '_blank');
                }
                location.reload();
            }
        });
    }

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
                    <label class="form-check-label" for="chk_${item.id}">
                        ${item.menu ? item.menu.nama_menu : 'Menu'} (Rp ${item.subtotal.toLocaleString('id-ID')})
                    </label>
                </div>
                <div style="width: 80px;">
                    <input type="number" class="form-control text-white border-secondary  form-control-sm text-center split-qty" id="qty_${item.id}" value="1" min="1" max="${item.jumlah}" disabled>
                </div>
                <span class="ms-2 small text-white-50">/ ${item.jumlah}</span>
            </div>
            `;
        });
        document.getElementById('split-items-container').innerHTML = html;

        // Add event listeners to checkboxes
        document.querySelectorAll('.split-cb').forEach(cb => {
            cb.addEventListener('change', function() {
                document.getElementById('qty_' + this.value).disabled = !this.checked;
            });
        });

        splitModal.show();
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
            alert('Pilih minimal 1 item untuk dipisah.');
            return;
        }

        // prevent splitting ALL items
        let isAll = true;
        splitDetails.forEach(sd => {
            let found = itemsToSplit.find(i => i.id_detail == sd.id);
            if (!found || found.jumlah < sd.jumlah) {
                isAll = false;
            }
        });

        if (isAll) {
            alert('Anda tidak bisa memisah semua item. Itu sama saja dengan memindahkan pesanan utuh.');
            return;
        }

        if(!confirm('Pisahkan item terpilih ke pesanan (Bon) baru? Diskon/Promo dari pesanan ini akan dihapus jika ada.')) return;

        fetch(`/kasir/order/${splitOrderId}/split`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ split_items: itemsToSplit })
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert(data.message);
                location.reload();
            }
        });
    }

    function printThermal(id) {
        if(!confirm('Kirim struk ini ke Printer Thermal Jaringan?')) return;
        
        fetch(`/kasir/order/${id}/print-thermal`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) alert(data.error);
            else {
                alert(data.message);
            }
        });
    }
</script>