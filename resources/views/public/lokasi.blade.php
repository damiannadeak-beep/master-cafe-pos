@extends('layouts.app')

@section('content')
<div class="container my-3 my-md-5 py-2 py-md-3 lokasi-page">
    @php
        $judul = \App\Models\Setting::getVal('lokasi_judul') ?? 'Panduan Rute & Navigasi';
        $deskripsi = \App\Models\Setting::getVal('lokasi_deskripsi') ?? 'Panduan rute jalan, patokan kedatangan, dan peta interaktif presisi menuju Master Cafe.';
        $namaTempat = \App\Models\Setting::getVal('lokasi_utama_nama') ?? 'Master Cafe';
        $alamat = \App\Models\Setting::getVal('lokasi_utama_alamat') ?? "Jl. Bantan, Senggoro, Kec. Bengkalis, Kab. Bengkalis, Riau 28711";
        $panduan = \App\Models\Setting::getVal('lokasi_panduan');

        $gmapsUrl = \App\Models\Setting::getVal('lokasi_gmaps_url') ?? 'https://maps.google.com/maps?q=Senggoro,%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed';
        if (preg_match('/src="([^"]+)"/', $gmapsUrl, $match)) {
            $gmapsUrl = $match[1];
        }

        $directGmaps = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($namaTempat . ' ' . $alamat);
    @endphp

    <!-- Header Halaman: Bersih & Terarah -->
    <div class="row mb-4 mb-md-5">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.28);">
                <i class="bi bi-compass text-warning" style="font-size: 0.85rem;"></i>
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.75rem; letter-spacing: 1.5px;">Panduan Navigasi</span>
            </div>
            <h1 class="fw-bold text-white mb-2" style="font-size: clamp(1.8rem, 3.5vw, 2.5rem); letter-spacing: -0.5px;">{{ $judul }}</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6; max-width: 640px;">{{ $deskripsi }}</p>
        </div>
    </div>

    <!-- Konten Utama: 2 Kolom Asimetris (Informasi Rute di Kiri, Peta Lega di Kanan) -->
    <div class="row g-4 align-items-stretch">
        
        <!-- KOLOM KIRI: Detail Alamat, Patokan & Akses Kedatangan -->
        <div class="col-lg-5 col-xl-5 d-flex flex-column gap-3">
            
            <!-- Kartu 1: Alamat Presisi & Salin Alamat -->
            <div class="card border-0 rounded-4 p-4 text-white shadow-sm" style="background-color: #141820; border: 1px solid #232936 !important;">
                <div class="d-flex align-items-start gap-3 mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3);">
                        <i class="bi bi-geo-alt-fill fs-5"></i>
                    </div>
                    <div>
                        <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.8px; font-size: 0.72rem;">Titik Destinasi</span>
                        <h4 class="fw-bold text-white mb-0" style="font-size: 1.25rem;">{{ $namaTempat }}</h4>
                    </div>
                </div>

                <p class="text-secondary small mb-3 lh-base" style="font-size: 0.88rem; color: #cbd5e1 !important;">
                    {!! nl2br(e($alamat)) !!}
                </p>

                <button type="button" onclick="copyAddressText('{{ addslashes($alamat) }}')" class="btn btn-sm w-100 py-2.5 rounded-3 fw-medium d-flex align-items-center justify-content-center gap-2" style="background: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid #30363d; font-size: 0.85rem;">
                    <i class="bi bi-copy"></i>
                    <span>Salin Alamat Lengkap</span>
                </button>
            </div>

            <!-- Kartu 2: Patokan Rute & Akses Kedatangan (Fresh & Berbeda) -->
            <div class="card border-0 rounded-4 p-4 text-white shadow-sm flex-grow-1" style="background-color: #141820; border: 1px solid #232936 !important;">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-signpost-split text-warning fs-5"></i>
                    <h5 class="fw-semibold text-white mb-0" style="font-size: 1rem;">Patokan Rute &amp; Akses Jalan</h5>
                </div>

                <div class="mb-3">
                    <p class="text-secondary small mb-0 lh-base" style="font-size: 0.86rem; color: #94a3b8 !important;">
                        @if(!empty($panduan) && $panduan !== $alamat)
                            {{ $panduan }}
                        @else
                            Terletak di pinggir jalan utama Jl. Bantan, Senggoro. Mudah diakses dari arah pusat kota Bengkalis dengan lintasan jalan aspal mulus dua arah.
                        @endif
                    </p>
                </div>

                <hr class="my-3 border-secondary border-opacity-25">

                <!-- Informasi Akses Parkir Langsung -->
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(34, 197, 94, 0.12); color: #4ade80;">
                        <i class="bi bi-p-square fs-5"></i>
                    </div>
                    <div>
                        <span class="fw-semibold text-white d-block" style="font-size: 0.85rem;">Akses Parkir Kendaraan</span>
                        <span class="text-secondary small d-block" style="font-size: 0.8rem; line-height: 1.4;">
                            Tersedia area parkir motor &amp; mobil langsung di pelataran depan kafe, aman dan leluasa.
                        </span>
                    </div>
                </div>

                <!-- Tombol Navigasi Langsung -->
                <div class="mt-4 pt-2">
                    <a href="{{ $directGmaps }}" target="_blank" class="btn w-100 py-3 rounded-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none" style="background: var(--gradient-bronze); border: none; font-size: 0.92rem;">
                        <i class="bi bi-cursor-fill"></i>
                        <span>Buka Rute di Google Maps (GPS)</span>
                    </a>
                </div>
            </div>

        </div>

        <!-- KOLOM KANAN: Peta Google Maps Kanvas Lega (Tanpa Efek Rontgen Gelap) -->
        <div class="col-lg-7 col-xl-7">
            <div class="card border-0 rounded-4 overflow-hidden shadow-sm h-100 position-relative map-wrapper" style="background-color: #141820; border: 1px solid #232936 !important; min-height: 480px;">
                
                <!-- Chip Floating Status Peta di Bagian Atas -->
                <div class="position-absolute top-0 start-0 m-3 z-3 d-flex align-items-center gap-2 px-3 py-2 rounded-pill shadow-sm" style="background: rgba(14, 18, 23, 0.85); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.12); font-size: 0.8rem; color: #f8fafc;">
                    <span class="live-dot"></span>
                    <span class="fw-medium">{{ $namaTempat }}</span>
                    <span class="text-secondary">&bull; Senggoro, Bengkalis</span>
                </div>

                <!-- Google Maps Frame: Jernih, Terang, & Jelas Dibaca -->
                <iframe 
                    src="{{ $gmapsUrl }}" 
                    class="w-100 h-100" 
                    style="border: 0; min-height: 480px; display: block;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>

                <!-- Tombol Floating Cepat di Pojok Bawah Peta -->
                <div class="position-absolute bottom-0 end-0 m-3 z-3">
                    <a href="{{ $directGmaps }}" target="_blank" class="btn btn-sm btn-dark text-white rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-lg" style="background: rgba(14, 18, 23, 0.9); border: 1px solid rgba(255, 255, 255, 0.18); font-size: 0.82rem;">
                        <i class="bi bi-box-arrow-up-right text-warning"></i>
                        <span>Perbesar Peta</span>
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
    .lokasi-page {
        font-family: 'Poppins', sans-serif !important;
    }
    .lokasi-page .card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .lokasi-page .card:hover {
        border-color: rgba(192, 142, 92, 0.35) !important;
    }
    .live-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: #22c55e;
        box-shadow: 0 0 8px #22c55e;
        display: inline-block;
    }
</style>

<script>
    function copyAddressText(text) {
        if (!text) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof window.showToast === 'function') {
                    window.showToast('Alamat lengkap berhasil disalin!', 'success');
                } else {
                    alert('Alamat lengkap berhasil disalin!');
                }
            }).catch(function() {
                fallbackCopy(text);
            });
        } else {
            fallbackCopy(text);
        }
    }

    function fallbackCopy(text) {
        const temp = document.createElement('textarea');
        temp.value = text;
        temp.style.position = 'fixed';
        temp.style.opacity = '0';
        document.body.appendChild(temp);
        temp.focus();
        temp.select();
        try {
            document.execCommand('copy');
            if (typeof window.showToast === 'function') {
                window.showToast('Alamat lengkap berhasil disalin!', 'success');
            } else {
                alert('Alamat lengkap berhasil disalin!');
            }
        } catch (e) {
            alert('Gagal menyalin alamat.');
        }
        document.body.removeChild(temp);
    }
</script>
@endsection
