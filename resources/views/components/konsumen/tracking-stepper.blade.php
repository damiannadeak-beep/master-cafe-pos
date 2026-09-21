<!-- Visual Stepper Progress -->
<div class="card tracking-card shadow-sm p-4 mb-4">
    <h6 class="text-white fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2">
        <i class="bi bi-activity me-1" style="color: #c08e5c;"></i> Progres Pesanan Real-Time
    </h6>

    <div class="stepper">
        <!-- Step 1: Pesanan Dibuat -->
        <div class="stepper-item completed" id="step-created">
            <div class="stepper-icon">
                <i class="bi bi-check2"></i>
            </div>
            <div>
                <h6 class="text-white fw-bold mb-0">Pesanan Diterima</h6>
                <small class="text-secondary">Pesanan berhasil masuk ke sistem Master Cafe</small>
            </div>
        </div>

        <!-- Step 2: Pembayaran -->
        @php
            $isPaid = ($pembayaran && $pembayaran->status === 'paid');
            $isPendingVerif = ($pembayaran && $pembayaran->status === 'pending_verification');
        @endphp
        <div class="stepper-item {{ $isPaid ? 'completed' : 'active' }}" id="step-payment">
            <div class="stepper-icon {{ $isPendingVerif ? 'pulse-icon' : '' }}">
                @if($isPaid)
                    <i class="bi bi-check-circle-fill"></i>
                @elseif($isPendingVerif)
                    <i class="bi bi-hourglass-split"></i>
                @else
                    <i class="bi bi-credit-card"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h6 class="text-white fw-bold mb-0">
                        {{ $isPaid ? 'Pembayaran Lunas' : ($isPendingVerif ? 'Verifikasi Pembayaran' : 'Menunggu Pembayaran') }}
                    </h6>
                    @if(!$isPaid && !$isPendingVerif)
                        <div class="d-flex gap-1 align-items-center flex-wrap">
                            <a href="{{ url('/konsumen/checkout/' . $pesanan->id . '?token=' . $pesanan->order_token) }}" class="btn btn-sm rounded-pill px-2 py-1 fw-bold" style="background: var(--gradient-bronze); color: white; border: none; font-size: 0.75rem;">
                                ⚡ Bayar Online (QRIS)
                            </a>
                        </div>
                    @endif
                </div>
                <small class="text-secondary d-block mt-1" id="payment-status-text">
                    @if($isPaid)
                        Lunas via {{ strtoupper($pembayaran->metode ?? 'QRIS') }}
                    @elseif($isPendingVerif)
                        Bukti transfer telah dikirim, menunggu konfirmasi kasir
                    @elseif(($pesanan->tipe_pesanan ?? '') === 'takeaway')
                        @if(($pembayaran->metode ?? '') === 'cash')
                            Silakan selesaikan pembayaran tunai di kasir agar pesanan bungkus Anda segera diserahkan
                        @else
                            Wajib bayar lunas via QRIS agar pesanan segera diproses dan dibungkus dapur
                        @endif
                    @else
                        Dapat dibayar di kasir saat santai atau via QRIS HP
                    @endif
                </small>

                @if(!$isPaid && !$isPendingVerif && !empty($remainingSeconds) && $remainingSeconds > 0)
                    <div class="mt-2 py-1 px-2 rounded-3 d-inline-flex align-items-center gap-2" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                        <i class="bi bi-clock-history text-danger"></i>
                        <span class="small">Sisa Waktu Bayar: <strong id="tracking-timer-text" class="font-monospace">--:--</strong></span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Step 3: Dapur & Bar -->
        @php
            $isCooking = ($pesanan->status === 'processing');
            $isDone = ($pesanan->status === 'completed');
        @endphp
        <div class="stepper-item {{ $isDone ? 'completed' : ($isCooking ? 'active' : '') }}" id="step-kitchen">
            <div class="stepper-icon {{ $isCooking ? 'pulse-icon' : '' }}">
                @if($isDone)
                    <i class="bi bi-check2-all"></i>
                @elseif($isCooking)
                    <i class="bi bi-cup-hot-fill"></i>
                @else
                    <i class="bi bi-clock"></i>
                @endif
            </div>
            <div>
                <h6 class="text-white fw-bold mb-0">Disiapkan Barista & Koki</h6>
                <small class="text-secondary" id="kitchen-status-text">
                    {{ $isDone ? 'Semua hidangan selesai dimasak dan disajikan' : ($isCooking ? 'Minuman dan makanan Anda sedang diracik dengan sepenuh hati' : 'Dalam antrean persiapan dapur') }}
                </small>
            </div>
        </div>

        <!-- Step 4: Selesai -->
        <div class="stepper-item {{ $isDone ? 'completed' : '' }}" id="step-completed">
            <div class="stepper-icon">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <h6 class="text-white fw-bold mb-0">Pesanan Selesai</h6>
                <small class="text-secondary">Selamat menikmati waktu santai Anda di Master Cafe!</small>
            </div>
        </div>
    </div>
</div>
