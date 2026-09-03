@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-5">
                    <h2 class="fw-bold text-center mb-2 text-white" style="font-family: 'Rye', serif;">Pilih Jenis Pesanan</h2>
                    <p class="text-center text-secondary mb-5 small">Apakah Anda ingin memesan untuk dinikmati di tempat atau dibawa pulang?</p>
                    
                    <div class="row g-4">
                        <!-- Dine In Option -->
                        <div class="col-md-6">
                            <a href="{{ url('/konsumen/menu') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 text-center p-4 cursor-pointer hover-shadow rounded-4"
                                     style="background-color: #0e1217; border: 2px solid #21262d !important; transition: all 0.3s ease; cursor: pointer;">
                                    <div class="mb-3">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: rgba(178, 122, 77, 0.15); border: 1px solid rgba(178, 122, 77, 0.3);">
                                            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 48px; width: 48px; object-fit: cover; margin-bottom: 8px;">
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Makan di Tempat</h5>
                                    <p class="text-secondary small mb-0">Dinikmati langsung di meja restoran</p>
                                </div>
                            </a>
                        </div>

                        <!-- Takeaway Option -->
                        <div class="col-md-6">
                            <a href="{{ url('/konsumen/menu-takeaway') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 text-center p-4 cursor-pointer hover-shadow rounded-4"
                                     style="background-color: #0e1217; border: 2px solid #21262d !important; transition: all 0.3s ease; cursor: pointer;">
                                    <div class="mb-3">
                                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background-color: rgba(72, 187, 120, 0.15); border: 1px solid rgba(72, 187, 120, 0.3);">
                                            <i class="bi bi-bag-check text-white" style="font-size: 36px; color: #48bb78 !important;"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif;">Dibawa Pulang</h5>
                                    <p class="text-secondary small mb-0">Pesan untuk dinikmati di rumah</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease !important;
    }
    .hover-shadow:hover {
        box-shadow: 0 15px 30px rgba(0,0,0,0.4) !important;
        transform: translateY(-8px) !important;
        border-color: #c08e5c !important;
    }
</style>
@endsection
