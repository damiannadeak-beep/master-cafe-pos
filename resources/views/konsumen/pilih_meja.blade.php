@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-2 text-white" style="font-family: 'Rye', serif;">Pilih Meja</h3>
                    <p class="text-secondary mb-3 small">Pilih nomor meja tempat Anda duduk agar pesanan dapat diantar dengan tepat oleh pelayan.</p>
                    <div class="alert border-0 rounded-3 mb-4 py-2 px-3 small d-flex align-items-center" style="background-color: rgba(192, 142, 92, 0.12); color: #e2b488; border: 1px solid rgba(192, 142, 92, 0.25) !important;">
                        <i class="bi bi-qr-code-scan fs-5 me-2"></i>
                        <span><strong>Tips:</strong> Anda juga bisa langsung scan stiker QR di atas meja fisik dengan kamera HP Anda untuk langsung memesan!</span>
                    </div>

                    <!-- Opsi Pesan Nanti -->
                    <div class="card border-0 shadow-lg mb-5 hover-lift rounded-4" style="background: linear-gradient(135deg, #c08e5c 0%, #986c43 100%);">
                        <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
                            <div class="mb-3 mb-md-0 text-center text-md-start">
                                <h5 class="mb-1 fw-bold text-white" style="font-family: 'Outfit', sans-serif;"><i class="bi bi-geo-alt me-2"></i>Belum Tiba di Lokasi?</h5>
                                <p class="mb-0 small text-white opacity-75">Pesan dulu sekarang, cari meja kosong saat tiba di Master Cafe!</p>
                            </div>
                            <a href="{{ route('menu_nanti') }}" class="btn fw-bold px-4 rounded-pill hover-scale btn-touch" style="background-color: #161b22; color: #c08e5c; border: 1px solid #161b22;">Pesan Sekarang</a>
                        </div>
                    </div>

                    <h5 class="fw-bold text-white mb-3" style="font-family: 'Outfit', sans-serif;">Daftar Meja</h5>
                    <div class="row g-3">
                        @forelse($mejas as $meja)
                            <div class="col-12 col-md-6">
                                <div class="card border-0 shadow-sm rounded-4 h-100 hover-lift" style="background-color: #14171c; border: 1px solid #21262d !important;">
                                    <div class="card-body p-3 p-md-4 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1 text-white fw-bold" style="font-family: 'Outfit', sans-serif;">
                                                <i class="bi bi-display me-2 text-secondary"></i> {{ $meja->nama_meja_atau_nomor }}
                                            </h5>
                                            <small class="text-secondary" style="font-size: 0.8rem;">{{ $meja->keterangan ?? 'Siap digunakan untuk pemesanan.' }}</small>
                                        </div>
                                        <a href="{{ URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]) }}" class="btn fw-bold px-3 rounded-3 hover-scale btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-size: 0.9rem;">
                                            Pilih Meja
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert border-0 text-center p-4 rounded-4" style="background-color: rgba(217, 119, 6, 0.1); color: #d97706; border: 1px solid rgba(217, 119, 6, 0.2) !important;">
                                    <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                                    Tidak ada meja tersedia saat ini atau sistem sedang dimuat ulang.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease !important;
    }
    .hover-lift:hover {
        box-shadow: 0 10px 20px rgba(0,0,0,0.3) !important;
        transform: translateY(-4px) !important;
        border-color: rgba(178, 122, 77, 0.5) !important;
    }
    .hover-scale {
        transition: transform 0.2s ease;
    }
    .hover-scale:hover {
        transform: scale(1.05);
    }
</style>
@endsection
