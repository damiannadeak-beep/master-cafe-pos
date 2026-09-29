@extends('layouts.app')

@section('content')
<div class="container mt-3 mt-md-5 mb-4 mb-md-5 py-0 py-md-3">
    @php
        $judul = \App\Models\Setting::getVal('lokasi_judul') ?? 'Lokasi & Jam Buka';
        $deskripsi = \App\Models\Setting::getVal('lokasi_deskripsi') ?? 'Kunjungi Master Cafe untuk menikmati kopi dan hidangan istimewa dalam suasana yang tenang dan nyaman.';
        $namaTempat = \App\Models\Setting::getVal('lokasi_utama_nama') ?? 'Master Cafe';
        $alamat = \App\Models\Setting::getVal('lokasi_utama_alamat') ?? "Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711";
        $jamBuka = \App\Models\Setting::getVal('lokasi_jam_operasional') ?? 'Setiap Hari: 10:00 - 23:00 WIB';
        $panduan = \App\Models\Setting::getVal('lokasi_panduan');

        $gmapsUrl = \App\Models\Setting::getVal('lokasi_gmaps_url') ?? 'https://maps.google.com/maps?q=Senggoro,%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed';
        if (preg_match('/src="([^"]+)"/', $gmapsUrl, $match)) {
            $gmapsUrl = $match[1];
        }

        $directGmaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($namaTempat . ' ' . $alamat);
    @endphp

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Header Ringkas & Natural -->
            <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.8rem; letter-spacing: 1.5px;">Master Cafe</span>
                <h2 class="fw-bold text-white mt-1 mb-2">{{ $judul }}</h2>
                <p class="text-secondary small mb-0">{{ $deskripsi }}</p>
            </div>

            <!-- List Informasi Lokasi & Jam Buka -->
            <div class="list-group list-group-flush rounded-3 overflow-hidden mb-4" style="background-color: #161b22; border: 1px solid #21262d;">
                
                <!-- Alamat & Navigasi -->
                <div class="list-group-item bg-transparent text-white p-3 p-md-4 border-secondary border-opacity-25">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3">
                        <div>
                            <span class="text-secondary small d-block mb-1">Alamat Kafe</span>
                            <h5 class="fw-semibold text-white mb-1">{{ $namaTempat }}</h5>
                            <p class="text-secondary small mb-0 lh-base">
                                {!! nl2br(e($alamat)) !!}
                            </p>
                            @if(!empty($panduan) && $panduan !== $alamat)
                                <p class="text-secondary small mt-2 mb-0 fst-italic">
                                    <i class="bi bi-info-circle me-1 text-warning"></i> {{ $panduan }}
                                </p>
                            @endif
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ $directGmaps }}" target="_blank" class="btn btn-sm btn-outline-light px-3 py-2 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt text-warning"></i> Petunjuk Arah
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Jam Operasional -->
                <div class="list-group-item bg-transparent text-white p-3 p-md-4 border-0">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <span class="text-secondary small d-block mb-1">Jam Operasional</span>
                            <span class="fs-6 fw-semibold text-white d-inline-flex align-items-center gap-2">
                                <i class="bi bi-clock" style="color: #c08e5c;"></i> {{ $jamBuka }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Embed Google Maps Interaktif (Stiled Dark Invert Seamless) -->
            <div class="rounded-3 overflow-hidden mb-4 dark-gmap-container position-relative" style="border: 1px solid #21262d; background-color: #161b22; height: 380px;">
                <iframe 
                    src="{{ $gmapsUrl }}" 
                    class="w-100 h-100" 
                    style="border: 0; display: block;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>

        </div>
    </div>
</div>

<style>
    .dark-gmap-container iframe {
        filter: grayscale(85%) invert(90%) contrast(90%);
        transition: filter 0.4s ease;
    }
    .dark-gmap-container:hover iframe {
        filter: grayscale(0%) invert(0%) contrast(100%);
    }
</style>
@endsection
