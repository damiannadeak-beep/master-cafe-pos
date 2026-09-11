@extends('layouts.app')

@section('content')
<style>
    .tracking-card {
        background-color: #161b22;
        border: 1px solid #21262d !important;
        border-radius: 1.25rem;
    }
    .stepper-item {
        position: relative;
        display: flex;
        gap: 1rem;
        padding-bottom: 1.5rem;
    }
    .stepper-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 1.2rem;
        top: 2.2rem;
        bottom: 0;
        width: 2px;
        background-color: #30363d;
    }
    .stepper-item.active:not(:last-child)::before {
        background: var(--gradient-bronze);
    }
    .stepper-icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #21262d;
        color: #8b949e;
        flex-shrink: 0;
        z-index: 2;
        transition: all 0.3s ease;
    }
    .stepper-item.active .stepper-icon {
        background: var(--gradient-bronze);
        color: white;
        box-shadow: 0 0 15px rgba(192, 142, 92, 0.4);
    }
    .stepper-item.completed .stepper-icon {
        background-color: #238636;
        color: white;
    }
    .pulse-animation {
        animation: pulseGlow 1.8s infinite;
    }
    @keyframes pulseGlow {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(192, 142, 92, 0.5); }
        70% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(192, 142, 92, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(192, 142, 92, 0); }
    }
    .rating-star {
        font-size: 1.8rem;
        color: #484f58;
        cursor: pointer;
        transition: color 0.2s, transform 0.2s;
    }
    .rating-star:hover, .rating-star.active {
        color: #e3b341;
        transform: scale(1.15);
    }
</style>

<div class="container mt-3 mt-md-4 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            @if($pesanan->status === 'completed')
                <!-- Banner Pesanan Selesai & Tombol Selesai Utama -->
                <div class="alert border-0 rounded-4 p-4 mb-4 text-center shadow-lg" style="background: rgba(35, 134, 54, 0.15); border: 1px solid rgba(46, 160, 67, 0.35) !important;">
                    <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                    <h5 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Pesanan Selesai & Pembayaran Lunas</h5>
                    <p class="text-secondary small mb-3">Terima kasih telah berkunjung ke Master Cafe. Klik tombol di bawah untuk menyelesaikan sesi pesanan Anda.</p>
                    <button onclick="finishCustomerSession()" class="btn btn-md rounded-pill px-4 py-2 fw-bold shadow-sm" style="background: var(--gradient-bronze); color: white; border: none;">
                        <i class="bi bi-check2-circle me-1"></i> Selesai
                    </button>
                    <p class="text-white-50 small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Butuh struk? Silakan minta ke kasir kami.
                    </p>
                </div>
            @endif

            <!-- Card Header Status Meja -->
            <div class="card tracking-card shadow-lg p-3 p-md-4 mb-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="background: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3); font-size: 0.8rem;">
                            <i class="bi bi-geo-alt-fill me-1"></i> {{ ($pesanan->tipe_pesanan ?? '') === 'takeaway' ? '🥡 Bawa Pulang (Takeaway)' : ($meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Pesanan Cafe') }}
                        </span>
                        <h4 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                            Order #{{ $pesanan->id }}
                        </h4>
                        <p class="text-secondary small mb-0">
                            Atas Nama: <strong class="text-white">{{ $pesanan->customer_name }}</strong>
                            @if(!empty($pesanan->guest_phone))
                                &bull; <i class="bi bi-whatsapp text-success me-1"></i><strong class="text-white">{{ $pesanan->guest_phone }}</strong>
                            @endif
                            &bull; {{ $pesanan->created_at->translatedFormat('d M Y, H:i') }}
                        </p>
                    </div>

                    <div class="text-end">
                        <div id="badge-status-container">
                            @if($pesanan->status === 'completed')
                                <button onclick="finishCustomerSession()" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                                    <i class="bi bi-check-all me-1"></i> Selesai
                                </button>
                            @elseif($pesanan->status === 'processing')
                                <span class="badge bg-primary bg-opacity-25 text-primary border border-primary px-3 py-2 rounded-pill fs-6 pulse-animation">
                                    <i class="bi bi-cup-hot-fill me-1"></i> Sedang Disiapkan
                                </span>
                            @elseif($pesanan->status === 'cancelled')
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-2 rounded-pill fs-6">
                                    <i class="bi bi-x-circle me-1"></i> Dibatalkan
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-2 rounded-pill fs-6">
                                    <i class="bi bi-clock-history me-1"></i> Menunggu
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tombol Panggil Pelayan Cepat -->
                @if($meja)
                    <div class="mt-3 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-secondary">Butuh bantuan atau ingin menambah pesanan?</small>
                        <button id="btnCallBell" onclick="callWaiter({{ $meja->id }})" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">
                            <i class="bi bi-bell-fill me-1"></i> Panggil Pelayan
                        </button>
                    </div>
                @endif
            </div>

            <!-- Visual Stepper Progress -->
            <div class="card tracking-card shadow-sm p-4 mb-4">
                <h6 class="text-white fw-bold mb-4 border-bottom border-secondary border-opacity-25 pb-2" style="font-family: 'Outfit', sans-serif;">
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
                    <div class="stepper-item {{ $isPaid ? 'completed' : ($isPendingVerif ? 'active pulse-animation' : 'active') }}" id="step-payment">
                        <div class="stepper-icon">
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
                                        <a href="{{ url('/tracking/' . $pesanan->order_token . '?paid=1') }}" class="btn btn-sm btn-success rounded-pill px-2 py-1 fw-bold shadow-sm" style="font-size: 0.75rem;">
                                            <i class="bi bi-check-circle-fill me-1"></i> Konfirmasi Lunas
                                        </a>
                                        <a href="{{ url('/konsumen/checkout/' . $pesanan->id . '?token=' . $pesanan->order_token) }}" class="btn btn-sm rounded-pill px-2 py-1 fw-bold" style="background: var(--gradient-bronze); color: white; border: none; font-size: 0.75rem;">
                                            ⚡ Bayar Sekarang
                                        </a>
                                    </div>
                                @endif
                            </div>
                            <small class="text-secondary d-block mt-1" id="payment-status-text">
                                @if($isPaid)
                                    Lunas via {{ strtoupper($pembayaran->metode ?? 'QRIS') }}
                                @elseif($isPendingVerif)
                                    Bukti transfer telah dikirim, menunggu konfirmasi kasir
                                @else
                                    Dapat dibayar di kasir saat santai atau via QRIS HP
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Step 3: Dapur & Bar -->
                    @php
                        $isCooking = ($pesanan->status === 'processing');
                        $isDone = ($pesanan->status === 'completed');
                    @endphp
                    <div class="stepper-item {{ $isDone ? 'completed' : ($isCooking ? 'active pulse-animation' : '') }}" id="step-kitchen">
                        <div class="stepper-icon">
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

            <!-- Ringkasan Menu Pesanan -->
            <div class="card tracking-card shadow-sm p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                    <h6 class="text-white fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-receipt me-1" style="color: #c08e5c;"></i> Rincian Menu Pesanan
                    </h6>
                    <span class="badge rounded-pill bg-dark border border-secondary text-secondary">
                        {{ $pesanan->detail_pesanan->sum('jumlah') }} Item
                    </span>
                </div>

                <div class="list-group list-group-flush">
                    @foreach($pesanan->detail_pesanan as $detail)
                        <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary border-opacity-10">
                            <div>
                                <h6 class="text-white mb-0 fs-6">{{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Item' }}</h6>
                                @if(!empty($detail->catatan))
                                    <small class="text-warning d-block" style="font-size: 0.76rem;">
                                        <i class="bi bi-pencil-square me-1"></i>{{ $detail->catatan }}
                                    </small>
                                @endif
                                @if(!empty($detail->selected_variants))
                                    <small class="text-secondary d-block" style="font-size: 0.74rem;">
                                        {{ is_array($detail->selected_variants) ? implode(', ', $detail->selected_variants) : $detail->selected_variants }}
                                    </small>
                                @endif
                            </div>
                            <span class="text-white fw-semibold small">
                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="mt-3 pt-2">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span>
                    </div>
                    @if($pesanan->discount_amount > 0)
                        <div class="d-flex justify-content-between small text-danger mb-1">
                            <span>Diskon Promo</span>
                            <span>- Rp {{ number_format($pesanan->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between fw-bold fs-5 text-white mt-2 pt-2 border-top border-secondary border-opacity-25">
                        <span style="color: #c08e5c;">Total Tagihan</span>
                        <span style="color: #c08e5c;">
                            Rp {{ number_format($pembayaran->total_bayar ?? ($pesanan->total - $pesanan->discount_amount), 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Card Rating & Feedback Tamu (Hanya Tampil Saat Pesanan Selesai / Completed) -->
            @if($pesanan->status === 'completed')
            <div class="card tracking-card shadow-sm p-4 mb-4 text-center" id="rating-container">
                <h6 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    Bagaimana Pengalaman Anda?
                </h6>
                <p class="text-secondary small mb-3">Beri ulasan untuk membantu Master Cafe selalu melayani lebih baik.</p>

                @if($pesanan->rating)
                    <div class="p-3 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star-fill text-warning fs-5"></i>
                            @endfor
                        </div>
                        <p class="text-white small mb-1">"{{ $pesanan->rating->komentar ?: 'Layanan sangat memuaskan!' }}"</p>
                        <small class="text-success fw-semibold"><i class="bi bi-check-circle me-1"></i>Terima kasih atas ulasan Anda!</small>
                    </div>
                @else
                    <form id="formRating" onsubmit="submitRating(event)">
                        @csrf
                        <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                        <input type="hidden" name="order_token" value="{{ $pesanan->order_token }}">
                        <input type="hidden" name="rating" id="ratingValue" value="5">

                        <div class="d-flex justify-content-center gap-2 mb-3" id="starGroup">
                            <i class="bi bi-star-fill rating-star active" data-val="1" onclick="setRating(1)"></i>
                            <i class="bi bi-star-fill rating-star active" data-val="2" onclick="setRating(2)"></i>
                            <i class="bi bi-star-fill rating-star active" data-val="3" onclick="setRating(3)"></i>
                            <i class="bi bi-star-fill rating-star active" data-val="4" onclick="setRating(4)"></i>
                            <i class="bi bi-star-fill rating-star active" data-val="5" onclick="setRating(5)"></i>
                        </div>

                        <div class="mb-3">
                            <textarea name="komentar" id="ratingComment" class="form-control rounded-3 text-white" 
                                      placeholder="Tulis kesan atau saran Anda (opsional)..." rows="2" 
                                      style="background-color: #0e1217; border: 1px solid #30363d; font-size: 0.9rem;"></textarea>
                        </div>

                        <div class="d-flex justify-content-center gap-2 align-items-center">
                            <button type="submit" id="btnSubmitRating" class="btn btn-sm rounded-pill px-4 fw-bold shadow-sm" 
                                    style="background: var(--gradient-bronze); color: white; border: none;">
                                Kirim Ulasan <i class="bi bi-send-fill ms-1"></i>
                            </button>
                            <button type="button" onclick="finishCustomerSession()" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                                Nanti Saja / Selesai
                            </button>
                        </div>
                    </form>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>

<script>
    const orderToken = "{{ $pesanan->order_token }}";
    const pesananId = {{ $pesanan->id }};
    let bellCooldown = 0;

    // Fungsi Utama: Menyelesaikan Sesi Pesanan Konsumen & Kembali ke Menu Meja yang Sama
    function finishCustomerSession() {
        try {
            localStorage.removeItem('active_guest_order');
            localStorage.removeItem('master_cafe_guest_name');
            localStorage.removeItem('master_cafe_guest_phone');
        } catch(e) {}
        @if($meja)
            window.location.href = "{{ url('/konsumen/menu/' . $meja->id) }}";
        @else
            window.location.href = "{{ url('/katalog') }}";
        @endif
    }

    // 1. Fungsi Panggil Pelayan dengan Cooldown 2 Menit
    function callWaiter(mejaId) {
        const btn = document.getElementById('btnCallBell');
        if (!btn || btn.disabled) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Memanggil...';

        fetch("{{ url('/call-bell') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ id_meja: mejaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                startCooldown(60);
            } else {
                alert('🔔 ' + (data.message || 'Pelayan telah dipanggil dan segera menuju ke meja Anda.'));
                startCooldown(120);
            }
        })
        .catch(err => {
            alert('Gagal memanggil pelayan. Silakan panggil pelayan di dekat meja.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Panggil Pelayan';
        });
    }

    function startCooldown(seconds) {
        const btn = document.getElementById('btnCallBell');
        if (!btn) return;
        bellCooldown = seconds;
        btn.disabled = true;
        
        const timer = setInterval(() => {
            bellCooldown--;
            if (bellCooldown <= 0) {
                clearInterval(timer);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-bell-fill me-1"></i> Panggil Pelayan';
            } else {
                btn.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Tunggu (${bellCooldown}s)`;
            }
        }, 1000);
    }

    // 2. Rating Handler
    function setRating(val) {
        document.getElementById('ratingValue').value = val;
        document.querySelectorAll('.rating-star').forEach(star => {
            const sVal = parseInt(star.getAttribute('data-val'));
            if (sVal <= val) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    function submitRating(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitRating');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mengirim...';

        const payload = {
            id_pesanan: pesananId,
            order_token: orderToken,
            rating: document.getElementById('ratingValue').value,
            komentar: document.getElementById('ratingComment').value
        };

        fetch("{{ url('/rating/store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('rating-container');
            container.innerHTML = `
                <div class="p-3 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                    <i class="bi bi-check-circle-fill text-success fs-2 mb-2 d-block"></i>
                    <h6 class="text-white fw-bold">Ulasan Terkirim!</h6>
                    <p class="text-secondary small mb-2">${data.message || 'Terima kasih banyak atas feedback Anda untuk Master Cafe.'}</p>
                    <button onclick="finishCustomerSession()" class="btn btn-sm btn-success rounded-pill px-4 fw-bold mt-2">
                        <i class="bi bi-check2-circle me-1"></i> Selesai & Kembali ke Menu
                    </button>
                </div>`;
        })
        .catch(err => {
            alert('Terjadi kesalahan saat mengirim ulasan.');
            btn.disabled = false;
            btn.innerHTML = 'Kirim Ulasan <i class="bi bi-send-fill ms-1"></i>';
        });
    }

    // 3. Real-Time Status Synchronization (Reverb WebSocket + Polling 5s Fallback)
    let currentStatus = "{{ $pesanan->status }}";
    let currentPayStatus = "{{ $pembayaran->status ?? 'unpaid' }}";

    function updateStatusUI(data) {
        if (data.status !== currentStatus || data.payment_status !== currentPayStatus) {
            currentStatus = data.status;
            currentPayStatus = data.payment_status;
            // Refresh halaman agar stepper & badge ter-render sempurna
            window.location.reload();
        }
    }

    function pollOrderStatus() {
        if (document.hidden) return;
        fetch("{{ url('/api/tracking/' . $pesanan->order_token . '/status') }}")
            .then(res => res.json())
            .then(data => {
                updateStatusUI(data);
            })
            .catch(err => console.log('Polling sync error:', err));
    }

    // Fast real-time polling (3 detik) agar status pesanan ter-update otomatis tanpa refresh
    let trackingInterval = setInterval(pollOrderStatus, 3000);

    // Cleanup active order LocalStorage jika status sudah completed / cancelled
    document.addEventListener('DOMContentLoaded', () => {
        if (currentStatus === 'completed' || currentStatus === 'cancelled') {
            try {
                localStorage.removeItem('active_guest_order');
                const banner = document.getElementById('active-order-recovery-banner');
                if (banner) banner.style.display = 'none';
            } catch(e) {}
        }

        if (window.Echo) {
            window.Echo.channel('kasir-notifications')
                .listen('.PesananBaru', (e) => {
                    if (e.id == pesananId) pollOrderStatus();
                })
                .listen('.MejaStatusUpdated', (e) => {
                    pollOrderStatus();
                });
        }
    });
</script>
@endsection
