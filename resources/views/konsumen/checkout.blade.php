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
                            <a href="{{ url('/konsumen/profil') }}" class="btn btn-outline-secondary btn-lg fw-bold rounded-pill btn-touch">
                                Kembali ke Profil <i class="bi bi-arrow-right ms-2"></i>
                            </a>
                        </div>
                    @else
                        <!-- Area Pembayaran -->
                        <div class="text-center mb-4">
                            <p class="text-secondary small mb-3">Silakan pindai QRIS di bawah ini untuk membayar sesuai Total Tagihan.</p>
                            <img src="{{ asset('storage/qris/E7fbrwtuYBtpOeuCIA6jOc0RB1NPQ24812gFJmre.jpg') }}" alt="QRIS Master Cafe" class="img-fluid rounded-4 shadow" style="max-width: 250px; border: 4px solid #c08e5c;">
                            <p class="text-white mt-3 fw-bold">A.N. MASTER CAFE</p>
                        </div>

                        <!-- Form Upload Bukti -->
                        <form action="{{ url('konsumen/order/' . $pesanan->id . '/upload-bukti') }}" method="POST" enctype="multipart/form-data" id="form-upload">
                            @csrf
                            <label class="form-label text-secondary fw-bold small">Unggah Bukti Transfer</label>
                            
                            <div class="upload-container mb-3" id="upload-box" onclick="document.getElementById('bukti_bayar').click()">
                                <img id="preview-image" class="upload-preview mb-2" src="" alt="Preview">
                                <div id="upload-placeholder">
                                    <i class="bi bi-cloud-arrow-up text-secondary d-block mb-2" style="font-size: 2.5rem;"></i>
                                    <span class="text-white fw-semibold">Klik untuk pilih gambar</span>
                                    <br>
                                    <small class="text-secondary">JPG, PNG (Max 2MB)</small>
                                </div>
                                <input type="file" id="bukti_bayar" name="bukti_bayar" class="d-none" accept="image/jpeg,image/png,image/jpg" required onchange="previewFile(this)">
                            </div>
                            
                            @error('bukti_bayar')
                                <div class="text-danger small fw-bold mb-3">{{ $message }}</div>
                            @enderror

                            <div class="d-grid mt-4 gap-2">
                                <button type="submit" id="btn-submit" class="btn btn-lg fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none;" disabled>
                                    Kirim Bukti Pembayaran <i class="bi bi-send ms-2"></i>
                                </button>
                                <a href="{{ url('/konsumen/profil') }}" class="btn btn-outline-secondary rounded-pill fw-bold">Bayar Langsung di Kasir</a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function previewFile(input) {
        const file = input.files[0];
        const preview = document.getElementById('preview-image');
        const placeholder = document.getElementById('upload-placeholder');
        const submitBtn = document.getElementById('btn-submit');
        const uploadBox = document.getElementById('upload-box');
        
        if (file) {
            // Validasi ukuran (max 2MB)
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