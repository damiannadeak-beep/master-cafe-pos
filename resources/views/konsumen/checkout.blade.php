@extends('layouts.app') 

@section('content')
@php
    $selfTotal = (int)$pembayaran->total_bayar;
    $hasPriorUnpaid = isset($priorUnpaidTotal) && $priorUnpaidTotal > 0;
    $tableTotal = $hasPriorUnpaid ? (int)$cumulativeTableTotal : $selfTotal;

    $defaultScope = $hasPriorUnpaid ? 'table' : 'self';
@endphp

<div class="container mt-4 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header border-0 py-4 text-center" style="background: var(--gradient-bronze); border-radius: 1rem 1rem 0 0;">
                    <i class="bi bi-qr-code-scan text-white mb-2 d-block" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-0 text-white fw-bold" style="font-family: 'Rye', serif;">Pembayaran Pesanan</h4>
                    <p class="text-white-50 mb-0 small">ID Pesanan: #{{ $pesanan->id }}</p>
                </div>
                <div class="card-body p-4">
                    <!-- Ringkasan Belanja -->
                    <div class="p-3 rounded-4 mb-4" style="background-color: #0e1217; border: 1px solid #21262d;">
                        <h6 class="text-secondary mb-3 fw-bold border-bottom border-secondary pb-2">Ringkasan Belanja</h6>
                        <ul class="list-unstyled mb-3">
                            @foreach($pesanan->detail_pesanan as $detail)
                            <li class="d-flex justify-content-between mb-2 small text-white">
                                <span>{{ $detail->jumlah }}x {{ $detail->menu->nama_menu }}</span>
                                <span>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                        </ul>
                        <div class="d-flex justify-content-between fw-bold fs-5 mt-3 pt-3 border-top border-secondary">
                            <span class="text-secondary">Total Tagihan Pesanan Ini</span>
                            <span style="color: #c08e5c;">Rp {{ number_format($selfTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    @if($hasPriorUnpaid)
                        <!-- Selektor Cakupan Tagihan Meja (Bayar Sendiri vs Gabung Meja) -->
                        <div class="card border-0 rounded-4 mb-4" style="background: #0e1217; border: 1px solid #30363d !important;">
                            <div class="card-body p-3 p-md-4">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center" style="background: rgba(13, 202, 240, 0.12); color: #0dcaf0; border: 1px solid rgba(13, 202, 240, 0.35); font-size: 0.74rem;">
                                        <i class="bi bi-people-fill me-1"></i> Multi-Pesanan Meja
                                    </span>
                                    <small class="text-white-50" style="font-size: 0.75rem;">Meja memiliki pesanan lain</small>
                                </div>
                                <h6 class="fw-bold text-white mb-1" style="font-size: 0.95rem;">Pilih Tagihan yang Ingin Anda Bayar:</h6>
                                <p class="text-white-50 small mb-3" style="font-size: 0.78rem;">Pilih apakah Anda hanya membayar pesanan Anda sendiri (Split Bill) atau mentraktir seluruh pesanan di meja ini.</p>

                                <div class="d-flex flex-column gap-2">
                                    <!-- Opsi A: Bayar Sendiri (Split Bill) -->
                                    <label class="p-3 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer payment-scope-card" 
                                           id="scope-card-self" 
                                           onclick="switchPaymentScope('self')"
                                           style="cursor: pointer; background: {{ $defaultScope === 'self' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(255, 255, 255, 0.02)' }}; border-color: {{ $defaultScope === 'self' ? '#22c55e !important' : '#21262d !important' }}; transition: all 0.2s;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="radio" name="scope_radio" id="radio-scope-self" value="self" {{ $defaultScope === 'self' ? 'checked' : '' }} class="form-check-input mt-0" style="cursor: pointer;">
                                            <div>
                                                <div class="fw-bold text-white small">Bayar Pesanan Saya Saja (Pisah Bill)</div>
                                                <small class="text-white-50 d-block" style="font-size: 0.74rem;">Hanya pesanan yang Anda pesan di HP ini</small>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-success fs-6 text-nowrap">Rp {{ number_format($selfTotal, 0, ',', '.') }}</span>
                                    </label>

                                    <!-- Opsi B: Bayar Seluruh Meja (Traktir / Tambah Menu) -->
                                    <label class="p-3 rounded-3 border d-flex align-items-center justify-content-between cursor-pointer payment-scope-card" 
                                           id="scope-card-table" 
                                           onclick="switchPaymentScope('table')"
                                           style="cursor: pointer; background: {{ $defaultScope === 'table' ? 'rgba(234, 179, 8, 0.12)' : 'rgba(255, 255, 255, 0.02)' }}; border-color: {{ $defaultScope === 'table' ? '#eab308 !important' : '#21262d !important' }}; transition: all 0.2s;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input type="radio" name="scope_radio" id="radio-scope-table" value="table" {{ $defaultScope === 'table' ? 'checked' : '' }} class="form-check-input mt-0" style="cursor: pointer;">
                                            <div>
                                                <div class="fw-bold text-white small">Bayar Sekaligus Seluruh Meja (Traktir / Tambah Menu)</div>
                                                <small class="text-white-50 d-block" style="font-size: 0.74rem;">Pesanan baru Anda + pesanan meja sebelumnya yang belum lunas</small>
                                            </div>
                                        </div>
                                        <span class="fw-bold text-warning fs-6 text-nowrap">Rp {{ number_format($tableTotal, 0, ',', '.') }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($pembayaran->status == 'pending_verification')
                        <div class="alert alert-warning text-center rounded-4" style="background-color: #2b200b; color: #ffc107; border: 1px solid #c08e5c;">
                            <i class="bi bi-hourglass-split d-block mb-2" style="font-size: 2rem;"></i>
                            <h6 class="fw-bold">Menunggu Konfirmasi Waitress</h6>
                            <small>Bukti pembayaran Anda sedang diverifikasi oleh sistem. Silakan tunggu di meja Anda.</small>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="{{ $pesanan->order_token ? url('/tracking/' . $pesanan->order_token) : (auth()->check() ? url('/konsumen/profil') : url('/')) }}" class="btn btn-outline-secondary btn-lg fw-bold rounded-pill btn-touch">
                                Lihat Status Pesanan <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    @else
                        <!-- Opsi 1: Pembayaran QRIS & VA Midtrans -->
                        <div class="p-3 rounded-4 mb-3" style="background: linear-gradient(135deg, rgba(192, 142, 92, 0.12) 0%, rgba(22, 27, 34, 0.9) 100%); border: 1px solid rgba(192, 142, 92, 0.4);">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: var(--gradient-bronze); width: 44px; height: 44px; color: white;">
                                    <i class="bi bi-qr-code-scan" style="font-size: 1.35rem;"></i>
                                </div>
                                <div>
                                    <h6 class="text-white fw-bold mb-0">Bayar Online Instan (QRIS / VA)</h6>
                                    <small class="text-secondary">GoPay, ShopeePay, OVO, DANA, BCA, Mandiri & E-Wallet</small>
                                </div>
                            </div>

                            @if(!empty($snapError))
                                <div class="alert alert-warning small py-2 px-3 rounded-3 mb-3 text-start" style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.3); color: #f8d7da; font-size: 0.8rem;">
                                    <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> <strong>Midtrans Alert:</strong> {{ $snapError }}
                                </div>
                            @endif

                            @if(!empty($snapToken))
                                <button id="pay-button" onclick="payWithSnap()" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1rem;">
                                    âš¡ Bayar Sekarang via QRIS / VA <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            @else
                                @if(app()->isProduction())
                                    <div class="alert alert-warning small py-2 px-3 rounded-3 mb-0 text-start" style="background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3); color: #feefc3; font-size: 0.85rem;">
                                        <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> Pembayaran Online (Midtrans) tidak dapat dihubungi. Silakan pilih opsi <strong>Bayar Tunai</strong> di bawah ini.
                                    </div>
                                @else
                                    <form action="{{ url('konsumen/order/' . $pesanan->id . '/simulate-midtrans-pay' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST">
                                        @csrf
                                        @if($pesanan->order_token)
                                            <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                        @endif
                                        <button type="submit" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1rem;">
                                            âš¡ [Simulasi Testing] Bayar Sekarang via QRIS <i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>

                        @if(($pesanan->tipe_pesanan ?? '') === 'takeaway')
                            <!-- Kebijakan Wajib Bayar Lunas di Depan untuk Takeaway -->
                            <div class="alert border-0 rounded-4 text-center mb-0 p-3" style="background-color: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.3) !important; color: #c08e5c;">
                                <small><i class="bi bi-shield-lock-fill me-1"></i> Pesanan Bawa Pulang (Takeaway) <strong>wajib dibayar lunas di depan</strong> via QRIS / E-Wallet / Virtual Account agar dapur dapat langsung memasak & membungkus pesanan Anda.</small>
                            </div>
                        @else
                            <!-- Opsi 2: Pembayaran Tunai (Cash) - Pilih Mode -->
                            <div class="p-3 rounded-4 mb-3" style="background: #12161c; border: 1px solid rgba(255, 255, 255, 0.12);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center bg-dark border border-secondary" style="width: 44px; height: 44px; color: #22c55e;">
                                        <i class="bi bi-cash-stack" style="font-size: 1.35rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0">Bayar Tunai (Cash)</h6>
                                        <small class="text-white-50">Pilih cara Anda membayar secara tunai</small>
                                    </div>
                                </div>

                                <form id="cash-payment-form" action="{{ url('konsumen/order/' . $pesanan->id . '/choose-cash' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST">
                                    @csrf
                                    @if($pesanan->order_token)
                                        <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                    @endif
                                    <input type="hidden" id="payment_scope" name="payment_scope" value="{{ $defaultScope }}">
                                    <input type="hidden" id="cash_mode" name="cash_mode" value="">

                                    <!-- Pilihan Mode Cash -->
                                    <div class="d-flex flex-column gap-2 mb-3">
                                        <!-- Opsi A: Datang ke Kasir -->
                                        <label class="p-3 rounded-3 border d-flex align-items-start gap-3 cash-mode-card" 
                                               id="mode-card-kasir"
                                               onclick="selectCashMode('kasir')"
                                               style="cursor: pointer; background: rgba(255, 255, 255, 0.02); border-color: #21262d !important; transition: all 0.2s;">
                                            <input type="radio" name="cash_mode_radio" id="radio-mode-kasir" value="kasir" class="form-check-input mt-1" style="cursor: pointer;">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-white small d-flex align-items-center gap-2">
                                                    <i class="bi bi-shop text-info"></i> Datang ke Kasir
                                                </div>
                                                <small class="text-white-50 d-block mt-1" style="font-size: 0.74rem;">Saya akan bayar langsung di meja kasir setelah makan / kapanpun siap</small>
                                            </div>
                                        </label>

                                        <!-- Opsi B: Bayar Pas di Meja -->
                                        <label class="p-3 rounded-3 border d-flex align-items-start gap-3 cash-mode-card" 
                                               id="mode-card-bayar_pas"
                                               onclick="selectCashMode('bayar_pas')"
                                               style="cursor: pointer; background: rgba(255, 255, 255, 0.02); border-color: #21262d !important; transition: all 0.2s;">
                                            <input type="radio" name="cash_mode_radio" id="radio-mode-bayar_pas" value="bayar_pas" class="form-check-input mt-1" style="cursor: pointer;">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-white small d-flex align-items-center gap-2">
                                                    <i class="bi bi-cash-coin text-success"></i> Bayar Pas di Meja
                                                </div>
                                                <small class="text-white-50 d-block mt-1" style="font-size: 0.74rem;">Waitress akan mengantar pesanan & menjemput uang tunai di meja saya</small>
                                            </div>
                                        </label>
                                    </div>

                                    <!-- Catatan Opsional (Bawa Duit Berapa) -->
                                    <div id="cash-note-container" class="mb-3" style="display: none;">
                                        <label class="form-label small text-secondary fw-bold mb-1">
                                            <i class="bi bi-pencil-square me-1"></i> Catatan Opsional
                                        </label>
                                        <input type="text" id="catatan_cash" name="catatan_cash" class="form-control bg-dark border-secondary text-white" 
                                               placeholder="Contoh: Bawa duit 50rb, Minta kembalian, dll" 
                                               maxlength="200"
                                               style="font-size: 0.9rem;">
                                        <small class="text-white-50 mt-1 d-block" style="font-size: 0.72rem;">Opsional: beri tahu waitress berapa uang yang Anda bawa atau catatan lainnya</small>
                                    </div>

                                    <!-- Info Tagihan -->
                                    <div id="cash-summary-card" class="p-3 rounded-3 mb-3" style="background: rgba(255, 255, 255, 0.04); border: 1px dashed rgba(255, 255, 255, 0.15); display: none;">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-white-50 small">Total Tagihan:</span>
                                            <span id="label-tagihan-target" class="text-white fw-bold">Rp {{ number_format($selfTotal, 0, ',', '.') }}</span>
                                        </div>
                                        <div id="cash-mode-info" class="mt-2 pt-2 border-top border-secondary border-opacity-25 small" style="font-size: 0.8rem;">
                                        </div>
                                    </div>

                                    <button type="submit" id="btn-submit-cash" class="btn btn-outline-success btn-lg w-100 fw-bold rounded-pill btn-touch" style="font-size: 0.95rem;" disabled>
                                        <i class="bi bi-cash-stack me-1"></i> Pesan & Bayar Tunai <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </form>
                            </div>

                            <div class="alert alert-info border-0 rounded-4 text-center mb-0" style="background-color: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2) !important; color: #93c5fd;">
                                <small><i class="bi bi-shield-check me-1"></i> Pesanan langsung diteruskan ke dapur untuk dimasak setelah pilihan bayar dipilih.</small>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($snapToken))
    <script src="{{ !empty($isProduction) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" data-client-key="{{ $clientKey ?? '' }}"></script>
    <script>
        function payWithSnap() {
            if (typeof window.snap === 'undefined') {
                alert('Modul pembayaran Midtrans sedang disiapkan, silakan klik ulang.');
                return;
            }
            window.snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    var orderId = (result && result.order_id) ? result.order_id : '';
                    window.location.href = "{{ url('/tracking/' . ($pesanan->order_token ?? '')) }}?paid=1&order_id=" + encodeURIComponent(orderId) + "&transaction_status=settlement&status_code=200";
                },
                onPending: function(result) {
                    var orderId = (result && result.order_id) ? result.order_id : '';
                    window.location.href = "{{ url('/tracking/' . ($pesanan->order_token ?? '')) }}?pending=1&order_id=" + encodeURIComponent(orderId);
                },
                onError: function(result) {
                    alert('Pembayaran belum berhasil diselesaikan. Anda tetap dapat membayar langsung di kasir.');
                },
                onClose: function() {
                    console.log('Pelanggan menutup popup Snap tanpa menyelesaikan transaksi.');
                }
            });
        }
    </script>
@endif

<script>
    const SELF_TOTAL = {{ (int)$selfTotal }};
    const TABLE_TOTAL = {{ (int)$tableTotal }};
    let currentScope = '{{ $defaultScope }}';
    let TOTAL_TAGIHAN = (currentScope === 'table') ? TABLE_TOTAL : SELF_TOTAL;

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number);
    }

    function switchPaymentScope(scope) {
        currentScope = scope;
        const scopeInput = document.getElementById('payment_scope');
        if (scopeInput) scopeInput.value = scope;

        const cardSelf = document.getElementById('scope-card-self');
        const cardTable = document.getElementById('scope-card-table');
        const radioSelf = document.getElementById('radio-scope-self');
        const radioTable = document.getElementById('radio-scope-table');

        if (scope === 'table') {
            TOTAL_TAGIHAN = TABLE_TOTAL;
            if (radioTable) radioTable.checked = true;
            if (cardTable) {
                cardTable.style.background = 'rgba(234, 179, 8, 0.12)';
                cardTable.style.borderColor = '#eab308 !important';
            }
            if (cardSelf) {
                cardSelf.style.background = 'rgba(255, 255, 255, 0.02)';
                cardSelf.style.borderColor = '#21262d !important';
            }
        } else {
            TOTAL_TAGIHAN = SELF_TOTAL;
            if (radioSelf) radioSelf.checked = true;
            if (cardSelf) {
                cardSelf.style.background = 'rgba(34, 197, 94, 0.1)';
                cardSelf.style.borderColor = '#22c55e !important';
            }
            if (cardTable) {
                cardTable.style.background = 'rgba(255, 255, 255, 0.02)';
                cardTable.style.borderColor = '#21262d !important';
            }
        }

        // Update summary jika mode sudah dipilih
        const modeInput = document.getElementById('cash_mode');
        if (modeInput && modeInput.value) {
            updateCashSummary(modeInput.value);
        }
    }

    function selectCashMode(mode) {
        const modeInput = document.getElementById('cash_mode');
        modeInput.value = mode;

        // Update radio
        document.querySelectorAll('.cash-mode-card').forEach(card => {
            card.style.background = 'rgba(255, 255, 255, 0.02)';
            card.style.borderColor = '#21262d !important';
        });
        const activeCard = document.getElementById('mode-card-' + mode);
        const activeRadio = document.getElementById('radio-mode-' + mode);
        if (activeRadio) activeRadio.checked = true;

        if (mode === 'kasir') {
            activeCard.style.background = 'rgba(13, 202, 240, 0.1)';
            activeCard.style.borderColor = '#0dcaf0 !important';
        } else {
            activeCard.style.background = 'rgba(34, 197, 94, 0.1)';
            activeCard.style.borderColor = '#22c55e !important';
        }

        // Tampilkan catatan opsional
        const noteContainer = document.getElementById('cash-note-container');
        noteContainer.style.display = 'block';

        // Enable submit
        document.getElementById('btn-submit-cash').disabled = false;

        updateCashSummary(mode);
    }

    function updateCashSummary(mode) {
        const summaryCard = document.getElementById('cash-summary-card');
        const modeInfo = document.getElementById('cash-mode-info');
        const labelTagihan = document.getElementById('label-tagihan-target');
        summaryCard.style.display = 'block';

        labelTagihan.innerText = 'Rp ' + formatRupiah(TOTAL_TAGIHAN);

        if (mode === 'kasir') {
            summaryCard.style.background = 'rgba(13, 202, 240, 0.08)';
            summaryCard.style.borderColor = 'rgba(13, 202, 240, 0.3)';
            modeInfo.innerHTML = '<i class="bi bi-shop text-info me-1"></i> <span class="text-info">Anda akan bayar langsung di kasir.</span> Pesanan tetap langsung diproses dapur.';
        } else {
            summaryCard.style.background = 'rgba(34, 197, 94, 0.08)';
            summaryCard.style.borderColor = 'rgba(34, 197, 94, 0.3)';
            modeInfo.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> <span class="text-success">Waitress akan mengantar pesanan & menjemput uang tunai di meja.</span>';
        }
    }

    document.getElementById('cash-payment-form')?.addEventListener('submit', function(e) {
        const mode = document.getElementById('cash_mode').value;
        if (!mode) {
            e.preventDefault();
            alert('Silakan pilih cara bayar tunai terlebih dahulu.');
            return false;
        }

        let confirmMsg = '';
        if (mode === 'kasir') {
            confirmMsg = 'Konfirmasi: Anda akan bayar tunai langsung di kasir (Rp ' + formatRupiah(TOTAL_TAGIHAN) + ')?';
        } else {
            confirmMsg = 'Konfirmasi: Bayar tunai di meja saat makanan diantar (Rp ' + formatRupiah(TOTAL_TAGIHAN) + ')?';
        }

        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection
