@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-4 text-center p-4 p-md-5" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="mb-4 d-flex justify-content-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 90px; height: 90px; background-color: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3);">
                        <i class="bi bi-qr-code text-danger" style="font-size: 42px;"></i>
                    </div>
                </div>

                <h3 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif !important;">Meja Tidak Ditemukan</h3>
                <p class="text-white-50 mb-4" style="font-size: 0.95rem; line-height: 1.6;">
                    Stiker QR meja yang Anda pindai tidak terdaftar di sistem Master Cafe atau sudah dinonaktifkan.
                </p>

                <div class="alert alert-warning border-0 rounded-3 text-start small mb-4 p-3" style="background-color: rgba(245, 158, 11, 0.1); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2) !important;">
                    <i class="bi bi-lightbulb-fill me-2"></i>
                    <strong>Tips untuk Anda:</strong>
                    <ul class="mb-0 ps-3 mt-1 text-white-50">
                        <li>Pastikan memindai stiker QR yang terpasang di atas meja Anda.</li>
                        <li>Atau tanyakan nomor meja yang benar kepada pelayan kafe kami.</li>
                    </ul>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ url('/konsumen/menu-takeaway') }}" class="btn btn-primary py-2 fw-semibold rounded-pill">
                        <i class="bi bi-bag-check me-2"></i> Pesan Bawa Pulang (Takeaway)
                    </a>
                    <a href="{{ url('/katalog') }}" class="btn btn-outline-secondary py-2 rounded-pill text-white">
                        <i class="bi bi-book me-2"></i> Lihat Katalog Menu
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-link text-white-50 text-decoration-none small mt-2">
                        Kembali ke Halaman Utama
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
