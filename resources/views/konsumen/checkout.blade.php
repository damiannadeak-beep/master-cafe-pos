@extends('layouts.app') 

@section('content')
@php
    $effectiveTotal = (isset($cumulativeTableTotal) && $cumulativeTableTotal > 0) ? (int)$cumulativeTableTotal : (int)$pembayaran->total_bayar;
@endphp
<style>
    .upload-container {
        border: 2px dashed #21262d;
        border-radius: 1rem;
        padding: 2rem;
        text-align: center;
        background: #0e1217;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .upload-container:hover {
        border-color: #c08e5c;
        background: #161b22;
    }
    .upload-preview {
        max-height: 200px;
        border-radius: 0.5rem;
        display: none;
        margin: 0 auto;
    }
</style>

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
                            <span class="text-secondary">Total Tagihan</span>
                            <span style="color: #c08e5c;">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>

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
                                    ⚡ Bayar Sekarang via QRIS / VA <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            @else
                                @if(app()->isProduction())
                                    <div class="alert alert-warning small py-2 px-3 rounded-3 mb-0 text-start" style="background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3); color: #feefc3; font-size: 0.85rem;">
                                        <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> Pembayaran Online (Midtrans) tidak dapat dihubungi. Silakan pilih opsi <strong>Bayar Tunai di Meja</strong> di bawah ini.
                                    </div>
                                @else
                                    <form action="{{ url('konsumen/order/' . $pesanan->id . '/simulate-midtrans-pay' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST">
                                        @csrf
                                        @if($pesanan->order_token)
                                            <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                        @endif
                                        <button type="submit" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1rem;">
                                            ⚡ [Simulasi Testing] Bayar Sekarang via QRIS <i class="bi bi-arrow-right ms-1"></i>
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
                            <!-- Opsi 2: Pembayaran Tunai (Cash) Saat Makanan Diantar (Khusus Dine-In) -->
                            <div class="p-3 rounded-4 mb-3" style="background: #12161c; border: 1px solid rgba(255, 255, 255, 0.12);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center bg-dark border border-secondary" style="width: 44px; height: 44px; color: #22c55e;">
                                        <i class="bi bi-cash-stack" style="font-size: 1.35rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0">Bayar Tunai (Cash) di Meja</h6>
                                        <small class="text-white-50">Waitress akan mengantar pesanan & menjemput pembayaran</small>
                                    </div>
                                </div>

                                 @php
                                     $effectiveTotal = (isset($cumulativeTableTotal) && $cumulativeTableTotal > 0) ? (int)$cumulativeTableTotal : (int)$pembayaran->total_bayar;
                                     $hasPriorUnpaid = isset($priorUnpaidTotal) && $priorUnpaidTotal > 0;
                                     
                                     // Otomatis arahkan ke nominal uang yang pernah dipilih tamu sebelumnya jika mencukupi tagihan meja
                                     $hasPriorCashChoice = ($hasPriorUnpaid && isset($priorCashPrepared) && $priorCashPrepared >= $effectiveTotal);
                                     $selectedPreset = $hasPriorCashChoice ? (int)$priorCashPrepared : $effectiveTotal;
                                     $isDefaultPas = ($selectedPreset == $effectiveTotal);
                                     $defaultKembalian = max(0, $selectedPreset - $effectiveTotal);
                                     $nextCeil = $effectiveTotal > 100000 ? (int) (ceil(($effectiveTotal + 1000) / 50000) * 50000) : null;
                                     $isCustomPreset = (!in_array($selectedPreset, array_filter([$effectiveTotal, 50000, 100000, $nextCeil])));
                                 @endphp

                                 @if($hasPriorUnpaid)
                                     <div class="p-3 mb-3 rounded-3" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                                         <div class="d-flex align-items-center mb-1">
                                             <i class="bi bi-info-circle-fill text-info me-2 fs-5"></i>
                                             <span class="text-white fw-bold small">Akumulasi Tagihan Meja</span>
                                         </div>
                                         <div class="text-white-50 small mb-2" style="font-size: 0.8rem; line-height: 1.4;">
                                             Ada pesanan sebelumnya di meja Anda yang belum dibayar. Pilihan uang tunai disesuaikan dengan <strong>total seluruh tagihan meja</strong> agar waitress membawakan kembalian yang tepat sekaligus.
                                         </div>
                                         <div class="d-flex justify-content-between small text-white-50 mb-1">
                                             <span>Pesanan Sebelumnya:</span>
                                             <span class="text-white">Rp {{ number_format($priorUnpaidTotal, 0, ',', '.') }}</span>
                                         </div>
                                         <div class="d-flex justify-content-between small text-white-50 mb-1">
                                             <span>Pesanan Ini:</span>
                                             <span class="text-white">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</span>
                                         </div>
                                         <div class="d-flex justify-content-between border-top border-secondary border-opacity-50 pt-1 mt-1">
                                             <span class="text-info fw-bold small">Total Seluruh Meja:</span>
                                             <span class="text-info fw-bold">Rp {{ number_format($effectiveTotal, 0, ',', '.') }}</span>
                                         </div>
                                     </div>
                                 @endif

                                 <form id="cash-payment-form" action="{{ url('konsumen/order/' . $pesanan->id . '/choose-cash' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST">
                                     @csrf
                                     @if($pesanan->order_token)
                                         <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                     @endif
                                     <input type="hidden" id="is_uang_pas" name="is_uang_pas" value="{{ $isDefaultPas ? '1' : '0' }}">
                                     <input type="hidden" id="nominal_tunai_raw" name="nominal_tunai" value="{{ $selectedPreset }}">

                                     <div class="mb-3">
                                         <div class="d-flex justify-content-between align-items-center mb-2">
                                             <label class="form-label text-secondary small fw-bold mb-0">
                                                 <i class="bi bi-wallet2 me-1"></i> Siapkan Uang Pecahan Berapa?
                                             </label>
                                             @if($hasPriorCashChoice)
                                                 <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50" style="font-size: 0.68rem;">
                                                     <i class="bi bi-arrow-repeat me-1"></i>Otomatis dari Pesanan Awal
                                                 </span>
                                             @endif
                                         </div>
                                         <div class="d-flex flex-wrap gap-2 mb-2">
                                             <!-- Pilihan 1: Uang Pas -->
                                             <button type="button" class="btn btn-sm cash-preset-btn rounded-pill px-3 py-2 fw-bold {{ $selectedPreset == $effectiveTotal ? 'btn-outline-success active' : 'btn-outline-secondary' }}" onclick="selectCashPreset('pas', {{ $effectiveTotal }}, this)">
                                                 <i class="bi bi-check2-circle me-1"></i> Uang Pas (Rp {{ number_format($effectiveTotal, 0, ',', '.') }})
                                             </button>

                                             @if($effectiveTotal < 50000)
                                                 <button type="button" class="btn btn-sm cash-preset-btn rounded-pill px-3 py-2 fw-bold {{ $selectedPreset == 50000 ? 'btn-outline-success active' : 'btn-outline-secondary' }}" onclick="selectCashPreset('fixed', 50000, this)">
                                                     Rp 50.000
                                                 </button>
                                             @endif

                                             @if($effectiveTotal < 100000 && $effectiveTotal != 50000)
                                                 <button type="button" class="btn btn-sm cash-preset-btn rounded-pill px-3 py-2 fw-bold {{ $selectedPreset == 100000 ? 'btn-outline-success active' : 'btn-outline-secondary' }}" onclick="selectCashPreset('fixed', 100000, this)">
                                                     Rp 100.000
                                                 </button>
                                             @endif

                                             @if($effectiveTotal > 100000 && $nextCeil)
                                                 <button type="button" class="btn btn-sm cash-preset-btn rounded-pill px-3 py-2 fw-bold {{ $selectedPreset == $nextCeil ? 'btn-outline-success active' : 'btn-outline-secondary' }}" onclick="selectCashPreset('fixed', {{ $nextCeil }}, this)">
                                                     Rp {{ number_format($nextCeil, 0, ',', '.') }}
                                                 </button>
                                             @endif

                                             <!-- Pilihan Nominal Lain -->
                                             <button type="button" class="btn btn-sm cash-preset-btn rounded-pill px-3 py-2 fw-bold {{ $isCustomPreset ? 'btn-outline-success active' : 'btn-outline-secondary' }}" onclick="selectCashPreset('custom', 0, this)">
                                                 <i class="bi bi-pencil me-1"></i> Nominal Lain
                                             </button>
                                         </div>

                                         <!-- Input Custom Nominal -->
                                         <div id="custom-nominal-container" class="mt-2" style="{{ $isCustomPreset ? 'display: block;' : 'display: none;' }}">
                                             <div class="input-group">
                                                 <span class="input-group-text bg-dark border-secondary text-white-50">Rp</span>
                                                 <input type="number" id="custom_nominal_input" class="form-control bg-dark border-secondary text-white" placeholder="Contoh: 100000" value="{{ $isCustomPreset ? $selectedPreset : '' }}" min="{{ $effectiveTotal }}" step="1000" oninput="onCustomNominalChange(this.value)">
                                             </div>
                                             <small class="text-white-50 mt-1 d-block" style="font-size: 0.75rem;">Ketik nominal uang kertas yang Anda siapkan.</small>
                                         </div>
                                     </div>

                                     <!-- Live Summary Kembalian -->
                                     <div id="kembalian-card" class="p-3 rounded-3 mb-3" style="background: {{ $defaultKembalian > 0 ? 'rgba(234, 179, 8, 0.09)' : 'rgba(34, 197, 94, 0.08)' }}; border: 1px dashed {{ $defaultKembalian > 0 ? 'rgba(234, 179, 8, 0.4)' : 'rgba(34, 197, 94, 0.4)' }};">
                                         <div class="d-flex justify-content-between align-items-center mb-1">
                                             <span class="text-white-50 small">Uang yang disiapkan:</span>
                                             <span id="label-uang-disiapkan" class="text-white fw-bold">Rp {{ number_format($selectedPreset, 0, ',', '.') }}</span>
                                         </div>
                                         <div class="d-flex justify-content-between align-items-center">
                                             <span class="text-white-50 small">Kembalian Waitress:</span>
                                             @if($defaultKembalian > 0)
                                                 <span id="label-kembalian" class="text-warning fw-bold fs-6">Rp {{ number_format($defaultKembalian, 0, ',', '.') }}</span>
                                             @else
                                                 <span id="label-kembalian" class="text-success fw-bold fs-6">Rp 0 (Uang Pas)</span>
                                             @endif
                                         </div>
                                         <div id="kembalian-alert-msg" class="mt-2 pt-2 border-top border-secondary border-opacity-25 small text-white-50" style="font-size: 0.75rem;">
                                             @if($defaultKembalian > 0)
                                                 <i class="bi bi-bell-fill text-warning me-1"></i> <strong>Waitress akan menyiapkan kembalian Rp {{ number_format($defaultKembalian, 0, ',', '.') }}</strong> untuk seluruh tagihan meja sebelum naik ke meja Anda.
                                             @else
                                                 <i class="bi bi-info-circle text-info me-1"></i> Waitress akan langsung membawakan pesanan ke meja Anda tanpa kembalian.
                                             @endif
                                         </div>
                                     </div>

                                     <button type="submit" id="btn-submit-cash" class="btn btn-outline-success btn-lg w-100 fw-bold rounded-pill btn-touch" style="font-size: 0.95rem;">
                                         💵 Pesan & Bayar Tunai ke Waitress <i class="bi bi-arrow-right ms-1"></i>
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
    function previewFile(input) {
        const file = input.files[0];
        const preview = document.getElementById('preview-image');
        const placeholder = document.getElementById('upload-placeholder');
        const submitBtn = document.getElementById('btn-submit');
        const uploadBox = document.getElementById('upload-box');
        
        if (file) {
            if(file.size > 2 * 1024 * 1024) {
                alert('Ukuran gambar terlalu besar! Maksimal 2MB.');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                uploadBox.style.padding = '1rem';
                submitBtn.disabled = false;
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            placeholder.style.display = 'block';
            uploadBox.style.padding = '2rem';
            submitBtn.disabled = true;
        }
    }

    const TOTAL_TAGIHAN = {{ (int)$effectiveTotal }};

    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(number);
    }

    function selectCashPreset(type, amount, btnElement) {
        // Update styling of buttons
        document.querySelectorAll('.cash-preset-btn').forEach(btn => {
            btn.classList.remove('btn-outline-success', 'active');
            btn.classList.add('btn-outline-secondary');
        });
        btnElement.classList.remove('btn-outline-secondary');
        btnElement.classList.add('btn-outline-success', 'active');

        const customContainer = document.getElementById('custom-nominal-container');
        const isUangPasInput = document.getElementById('is_uang_pas');
        const nominalRawInput = document.getElementById('nominal_tunai_raw');
        const customInput = document.getElementById('custom_nominal_input');

        if (type === 'pas') {
            customContainer.style.display = 'none';
            isUangPasInput.value = '1';
            nominalRawInput.value = TOTAL_TAGIHAN;
            updateKembalianUI(TOTAL_TAGIHAN, 0, true);
        } else if (type === 'fixed') {
            customContainer.style.display = 'none';
            isUangPasInput.value = '0';
            nominalRawInput.value = amount;
            const kembalian = Math.max(0, amount - TOTAL_TAGIHAN);
            updateKembalianUI(amount, kembalian, false);
        } else if (type === 'custom') {
            customContainer.style.display = 'block';
            customInput.focus();
            isUangPasInput.value = '0';
            if (customInput.value) {
                onCustomNominalChange(customInput.value);
            } else {
                updateKembalianUI(0, 0, false, true);
            }
        }
    }

    function onCustomNominalChange(val) {
        const nominal = parseInt(val) || 0;
        const nominalRawInput = document.getElementById('nominal_tunai_raw');
        nominalRawInput.value = nominal;

        if (nominal < TOTAL_TAGIHAN) {
            updateKembalianUI(nominal, 0, false, false, true);
        } else {
            const kembalian = nominal - TOTAL_TAGIHAN;
            updateKembalianUI(nominal, kembalian, kembalian === 0);
        }
    }

    function updateKembalianUI(nominal, kembalian, isPas, isNeedInput = false, isUnderpaid = false) {
        const labelUang = document.getElementById('label-uang-disiapkan');
        const labelKembalian = document.getElementById('label-kembalian');
        const alertMsg = document.getElementById('kembalian-alert-msg');
        const card = document.getElementById('kembalian-card');
        const submitBtn = document.getElementById('btn-submit-cash');

        if (!card) return;

        if (isUnderpaid) {
            labelUang.innerText = 'Rp ' + formatRupiah(nominal);
            labelKembalian.innerHTML = '<span class="text-danger">⚠️ Uang Kurang</span>';
            alertMsg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Nominal uang disiapkan kurang dari total tagihan (Rp ' + formatRupiah(TOTAL_TAGIHAN) + ').</span>';
            card.style.background = 'rgba(239, 68, 68, 0.08)';
            card.style.borderColor = 'rgba(239, 68, 68, 0.4)';
            submitBtn.disabled = true;
            return;
        }

        if (isNeedInput) {
            labelUang.innerText = '-';
            labelKembalian.innerText = '-';
            alertMsg.innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Silakan ketik jumlah uang yang Anda siapkan di atas.';
            card.style.background = 'rgba(255, 255, 255, 0.04)';
            card.style.borderColor = 'rgba(255, 255, 255, 0.2)';
            submitBtn.disabled = true;
            return;
        }

        submitBtn.disabled = false;
        labelUang.innerText = 'Rp ' + formatRupiah(nominal);

        if (isPas || kembalian === 0) {
            labelKembalian.innerHTML = '<span class="text-success">Rp 0 (Uang Pas)</span>';
            alertMsg.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i> Waitress akan langsung mengantar pesanan tanpa perlu kembalian.';
            card.style.background = 'rgba(34, 197, 94, 0.08)';
            card.style.borderColor = 'rgba(34, 197, 94, 0.4)';
        } else {
            labelKembalian.innerHTML = '<span class="text-warning fw-bold fs-6">Rp ' + formatRupiah(kembalian) + '</span>';
            alertMsg.innerHTML = '<i class="bi bi-bell-fill text-warning me-1"></i> <strong>Waitress akan menyiapkan uang kembalian Rp ' + formatRupiah(kembalian) + '</strong> sebelum naik ke meja Anda.';
            card.style.background = 'rgba(234, 179, 8, 0.09)';
            card.style.borderColor = 'rgba(234, 179, 8, 0.4)';
        }
    }

    document.getElementById('cash-payment-form')?.addEventListener('submit', function(e) {
        const isPas = document.getElementById('is_uang_pas').value === '1';
        const nominal = parseInt(document.getElementById('nominal_tunai_raw').value) || 0;
        
        if (!isPas && nominal < TOTAL_TAGIHAN) {
            e.preventDefault();
            alert('Nominal uang yang disiapkan tidak boleh kurang dari total tagihan (Rp ' + formatRupiah(TOTAL_TAGIHAN) + ').');
            return false;
        }

        let confirmMsg = 'Kirim pesanan dengan pembayaran Tunai (Cash)?';
        if (isPas) {
            confirmMsg = 'Konfirmasi: Anda membayar dengan Uang Pas Rp ' + formatRupiah(TOTAL_TAGIHAN) + ' saat makanan diantar?';
        } else {
            const kembalian = nominal - TOTAL_TAGIHAN;
            confirmMsg = 'Konfirmasi: Uang yang Anda siapkan Rp ' + formatRupiah(nominal) + ' (Waitress akan membawakan kembalian Rp ' + formatRupiah(kembalian) + ')?';
        }

        if (!confirm(confirmMsg)) {
            e.preventDefault();
            return false;
        }
    });
</script>
@endsection