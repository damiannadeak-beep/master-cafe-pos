@extends('layouts.app') 

@section('content')
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

                            @if(!empty($snapToken))
                                <button id="pay-button" onclick="payWithSnap()" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1rem;">
                                    ⚡ Bayar Sekarang via QRIS / VA <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            @else
                                <form action="{{ url('konsumen/order/' . $pesanan->id . '/simulate-midtrans-pay' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST">
                                    @csrf
                                    @if($pesanan->order_token)
                                        <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                    @endif
                                    <button type="submit" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1rem;">
                                        ⚡ Bayar Sekarang via QRIS / VA <i class="bi bi-arrow-right ms-1"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        @if(($pesanan->tipe_pesanan ?? '') === 'takeaway')
                            <!-- Kebijakan Wajib Bayar Lunas di Depan untuk Takeaway -->
                            <div class="alert border-0 rounded-4 text-center mb-0 p-3" style="background-color: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.3) !important; color: #c08e5c;">
                                <small><i class="bi bi-shield-lock-fill me-1"></i> Pesanan Bawa Pulang (Takeaway) <strong>wajib dibayar lunas di depan</strong> via QRIS / E-Wallet / Virtual Account agar dapur dapat langsung memasak & membungkus pesanan Anda.</small>
                            </div>
                        @else
                            <!-- Opsi 2: Pembayaran Tunai (Cash) Saat Makanan Diantar (Khusus Dine-In) -->
                            <div class="p-3 rounded-4 mb-3" style="background: #12161c; border: 1px solid rgba(255, 255, 255, 0.1);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center bg-dark border border-secondary" style="width: 44px; height: 44px; color: #22c55e;">
                                        <i class="bi bi-cash-stack" style="font-size: 1.35rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0">Bayar Tunai (Cash)</h6>
                                        <small class="text-white-50">Bayar uang tunai ke Waitress saat pesanan diantarkan ke meja</small>
                                    </div>
                                </div>

                                <form action="{{ url('konsumen/order/' . $pesanan->id . '/choose-cash' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST" onsubmit="return confirm('Pesan sekarang dan bayar tunai ke Waitress saat makanan tiba?')">
                                    @csrf
                                    @if($pesanan->order_token)
                                        <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                    @endif
                                    <button type="submit" class="btn btn-outline-light btn-lg w-100 fw-bold rounded-pill btn-touch" style="font-size: 0.95rem; border-color: rgba(255, 255, 255, 0.2);">
                                        💵 Bayar Cash Saat Makanan Diantar <i class="bi bi-person-badge ms-1"></i>
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
                    window.location.href = "{{ url('/tracking/' . ($pesanan->order_token ?? '')) }}?paid=1";
                },
                onPending: function(result) {
                    window.location.href = "{{ url('/tracking/' . ($pesanan->order_token ?? '')) }}?pending=1";
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
</script>
@endsection