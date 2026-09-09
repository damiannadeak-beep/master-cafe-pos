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
                            <h6 class="fw-bold">Menunggu Verifikasi Kasir</h6>
                            <small>Bukti pembayaran Anda sedang dicek oleh kasir. Silakan tunggu di meja Anda.</small>
                        </div>
                        <div class="d-grid mt-4">
                            <a href="{{ $pesanan->order_token ? url('/tracking/' . $pesanan->order_token) : (auth()->check() ? url('/konsumen/profil') : url('/')) }}" class="btn btn-outline-secondary btn-lg fw-bold rounded-pill btn-touch">
                                Lihat Status Pesanan <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    @else
                        <!-- Pilihan Pembayaran Utama: Midtrans Snap QRIS (Jika Aktif) -->
                        @if(!empty($snapToken))
                            <div class="p-3 rounded-4 mb-3" style="background: linear-gradient(135deg, rgba(192, 142, 92, 0.12) 0%, rgba(22, 27, 34, 0.9) 100%); border: 1px solid rgba(192, 142, 92, 0.4);">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: var(--gradient-bronze); width: 44px; height: 44px; color: white;">
                                        <i class="bi bi-qr-code-scan" style="font-size: 1.35rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-white fw-bold mb-0">Pembayaran Cepat QRIS</h6>
                                        <small class="text-secondary">GoPay, ShopeePay, OVO, DANA, BCA & E-Wallet</small>
                                    </div>
                                </div>
                                <button id="pay-button" onclick="payWithSnap()" class="btn btn-lg w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 1.05rem;">
                                    ⚡ Bayar Sekarang via QRIS <i class="bi bi-arrow-right ms-1"></i>
                                </button>
                            </div>
                        @endif

                        <!-- Tombol Bayar Nanti di Kasir -->
                        <div class="d-grid mb-4">
                            <a href="{{ $pesanan->order_token ? url('/tracking/' . $pesanan->order_token) : (auth()->check() ? url('/konsumen/profil') : url('/')) }}" class="btn btn-outline-secondary btn-lg rounded-pill fw-bold py-2 btn-touch">
                                💵 Bayar Nanti di Kasir
                            </a>
                            <small class="text-muted text-center mt-1" style="font-size: 0.76rem;">
                                Pesanan Anda langsung masuk ke dapur/bar. Pembayaran dapat diselesaikan di kasir nanti.
                            </small>
                        </div>

                        <!-- Opsi Tambahan: Transfer Bank / QRIS Statis Manual -->
                        <div class="accordion" id="manualPaymentAccordion">
                            <div class="accordion-item border-0 rounded-3 overflow-hidden" style="background-color: #0e1217; border: 1px solid #21262d !important;">
                                <h2 class="accordion-header" id="headingManual">
                                    <button class="accordion-button collapsed py-2 px-3 text-secondary small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseManual" aria-expanded="false" aria-controls="collapseManual" style="background-color: #0e1217; color: #8b949e !important;">
                                        <i class="bi bi-receipt me-2"></i> Pilihan Lain: Transfer Manual & Unggah Bukti
                                    </button>
                                </h2>
                                <div id="collapseManual" class="accordion-collapse collapse" aria-labelledby="headingManual" data-bs-parent="#manualPaymentAccordion">
                                    <div class="accordion-body p-3 border-top border-secondary">
                                        <div class="text-center mb-3">
                                            <p class="text-secondary small mb-2">Pindai QRIS Master Cafe di bawah ini:</p>
                                            <img src="{{ asset('storage/qris/E7fbrwtuYBtpOeuCIA6jOc0RB1NPQ24812gFJmre.jpg') }}" alt="QRIS Master Cafe" class="img-fluid rounded-3 shadow-sm" style="max-width: 200px; border: 3px solid #c08e5c;">
                                            <p class="text-white mt-2 small fw-bold mb-0">A.N. MASTER CAFE</p>
                                        </div>

                                        <form action="{{ url('konsumen/order/' . $pesanan->id . '/upload-bukti' . ($pesanan->order_token ? '?token=' . $pesanan->order_token : '')) }}" method="POST" enctype="multipart/form-data" id="form-upload">
                                            @csrf
                                            @if($pesanan->order_token)
                                                <input type="hidden" name="token" value="{{ $pesanan->order_token }}">
                                            @endif
                                            <label class="form-label text-secondary fw-bold small">Unggah Bukti Transfer</label>
                                            
                                            <div class="upload-container mb-3" id="upload-box" onclick="document.getElementById('bukti_bayar').click()">
                                                <img id="preview-image" class="upload-preview mb-2" src="" alt="Preview">
                                                <div id="upload-placeholder">
                                                    <i class="bi bi-cloud-arrow-up text-secondary d-block mb-2" style="font-size: 2rem;"></i>
                                                    <span class="text-white fw-semibold small">Klik untuk pilih gambar struk</span>
                                                    <br>
                                                    <small class="text-secondary" style="font-size: 0.72rem;">JPG, PNG (Max 2MB)</small>
                                                </div>
                                                <input type="file" id="bukti_bayar" name="bukti_bayar" class="d-none" accept="image/jpeg,image/png,image/jpg" required onchange="previewFile(this)">
                                            </div>
                                            
                                            @error('bukti_bayar')
                                                <div class="text-danger small fw-bold mb-3">{{ $message }}</div>
                                            @enderror

                                            <button type="submit" id="btn-submit" class="btn w-100 fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none;" disabled>
                                                Kirim Bukti Pembayaran <i class="bi bi-send ms-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
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