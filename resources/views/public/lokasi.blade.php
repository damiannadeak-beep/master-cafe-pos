@extends('layouts.app')

@section('content')
<!-- Header Banner -->
<div class="pt-5 pb-5 mb-5 position-relative overflow-hidden rounded-bottom-4 shadow-lg" style="background-color: #0e1217; border-bottom: 1px solid #21262d;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at top right, rgba(178, 122, 77, 0.15) 0%, transparent 70%); pointer-events: none;"></div>
    <div class="position-absolute bottom-0 end-0 w-100 h-100" style="background: radial-gradient(circle at bottom left, rgba(178, 122, 77, 0.1) 0%, transparent 60%); pointer-events: none;"></div>
    <div class="container text-center position-relative z-index-1 py-3">
        <h1 class="display-5 fw-bold mb-3 text-white" style="font-family: 'Rye', serif;">{{ \App\Models\Setting::getVal('lokasi_judul') ?? 'Titik Temu Kita' }}</h1>
        <p class="fs-6 text-light opacity-75 mx-auto mb-0" style="max-width: 600px; font-weight: 300;">
            {{ \App\Models\Setting::getVal('lokasi_deskripsi') ?? 'Temukan lokasi Master Cafe. Suasana hangat dan hidangan istimewa telah menanti kedatangan Anda.' }}
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    
    <div class="row g-4 align-items-center mb-5">
        <!-- Info Cards -->
        <div class="col-lg-4">
            <div class="d-flex flex-column gap-4">
                
                <!-- Card Alamat -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden hover-lift" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-body p-4 d-flex align-items-start">
                        <div class="rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="background-color: rgba(178, 122, 77, 0.15); color: #c08e5c; width: 60px; height: 60px; flex-shrink: 0; border: 1px solid rgba(178, 122, 77, 0.3);">
                            <i class="bi bi-geo-alt-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2 text-white" style="font-family: 'Outfit', sans-serif;">Lokasi Utama</h5>
                            <p class="mb-0 lh-base text-secondary small">
                                <strong class="text-light">{{ \App\Models\Setting::getVal('lokasi_utama_nama') ?? 'Master Cafe' }}</strong><br>
                                {!! nl2br(e(\App\Models\Setting::getVal('lokasi_utama_alamat') ?? "Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711")) !!}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Card Jam Operasional -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden hover-lift" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="card-body p-4 d-flex align-items-start">
                        <div class="rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="background-color: rgba(217, 119, 6, 0.15); color: #d97706; width: 60px; height: 60px; flex-shrink: 0; border: 1px solid rgba(217, 119, 6, 0.3);">
                            <i class="bi bi-clock-fill fs-3"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-2 text-white" style="font-family: 'Outfit', sans-serif;">Jam Operasional</h5>
                            <p class="mb-0 fw-bold fs-6" style="color: #c08e5c;">
                                {{ \App\Models\Setting::getVal('lokasi_jam_operasional') ?? 'Buka Setiap Hari: 10:00 AM - 23:00 PM' }}
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Card Panduan -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #161b22 0%, #22262d 100%); border: 1px solid rgba(178, 122, 77, 0.3) !important;">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-2" style="color: #c08e5c; font-family: 'Outfit', sans-serif;"><i class="bi bi-info-circle-fill me-2"></i>Panduan Menuju Lokasi</h6>
                        <p class="text-secondary small mb-0">
                            {{ \App\Models\Setting::getVal('lokasi_panduan') ?? 'Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711' }}
                        </p>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Google Maps -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-0 position-relative" style="min-height: 500px; background-color: #14171c;">
                    @php
                        $gmapsUrl = \App\Models\Setting::getVal('lokasi_gmaps_url') ?? 'https://maps.google.com/maps?q=Senggoro,%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed';
                        if (preg_match('/src="([^"]+)"/', $gmapsUrl, $match)) {
                            $gmapsUrl = $match[1];
                        }
                    @endphp
                    <iframe 
                        src="{{ $gmapsUrl }}" 
                        class="w-100 h-100 position-absolute top-0 start-0" 
                        style="border:0; filter: contrast(1.1) opacity(0.9);" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    
                    <!-- Floating Badge on Map -->
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-4 px-4 py-2 rounded-pill shadow-lg fw-bold d-flex align-items-center" style="background-color: #161b22; color: #c08e5c; border: 1px solid #21262d; z-index: 10;">
                        <span class="spinner-grow spinner-grow-sm text-danger me-2" role="status"></span>
                        Posisi Kami di Sini
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-8px);
        box-shadow: 0 1.5rem 3rem rgba(0,0,0,0.4) !important;
        border-color: rgba(178, 122, 77, 0.5) !important;
    }
</style>
@endsection
