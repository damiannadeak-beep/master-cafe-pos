@extends('layouts.app')

@section('content')
@php
    $namaTempat = \App\Models\Setting::getVal('lokasi_utama_nama') ?? 'Master Cafe';
    $alamat = \App\Models\Setting::getVal('lokasi_utama_alamat') ?? "Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711";
    $jamBuka = \App\Models\Setting::getVal('lokasi_jam_operasional') ?? 'Setiap Hari: 10:00 - 23:00 WIB';
@endphp

<!-- 1. Hero Section: Editorial Modern Layout -->
<section class="py-5 position-relative overflow-hidden border-bottom" style="background-color: #0e1217; border-color: #21262d !important;">
    <div class="position-absolute top-0 start-50 translate-middle-x w-100 h-100" style="background: radial-gradient(circle at 30% 20%, rgba(192, 142, 92, 0.08) 0%, transparent 60%); pointer-events: none;"></div>
    
    <div class="container py-2 py-lg-4 position-relative z-index-1">
        <div class="row align-items-center g-4 g-lg-5">
            
            <!-- Kolom Teks Editorial -->
            <div class="col-lg-6">
                <span class="text-uppercase fw-semibold d-inline-block mb-2" style="color: #c08e5c; font-size: 0.8rem; letter-spacing: 2px;">
                    Specialty Coffee & Space • Bengkalis
                </span>
                
                <h1 class="display-4 fw-bold text-white mb-3" style="line-height: 1.15; letter-spacing: -0.5px;">
                    Cita Rasa Otentik, Ruang Ternyaman di Bengkalis
                </h1>
                
                <p class="text-secondary fs-6 mb-4 text-justify" style="line-height: 1.75; font-weight: 300; max-width: 520px; text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important; -webkit-hyphens: auto !important; hyphens: auto !important;">
                    Selamat datang di {{ $namaTempat }}. Tempat di mana kopi berkualitas racikan barista, hidangan segar, dan atmosfer tenang berpadu untuk setiap momen kerja dan kebersamaan Anda.
                </p>

                <!-- Tombol Aksi Utama Tunggal -->
                <div>
                    <a href="/katalog" class="btn px-4 py-2.5 rounded-3 fw-semibold text-white d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm" style="background: var(--gradient-bronze); border: none; font-size: 0.92rem;">
                        <i class="bi bi-book"></i> Buka Katalog Menu
                    </a>
                </div>
            </div>

            <!-- Kolom Visual Fotografi Kafe Nyata (Master Cafe Bengkalis) -->
            <div class="col-lg-6">
                <div class="position-relative rounded-4 overflow-hidden shadow-lg hover-lift" style="border: 1px solid rgba(255, 255, 255, 0.12); aspect-ratio: 4/3; background-color: #161b22;">
                    <img src="{{ asset('images/cafe_exterior.jpg') }}" alt="Gedung & Suasana Master Cafe Bengkalis" class="w-100 h-100" style="object-fit: cover; object-position: center;">
                    
                    <!-- Overlay Gradien Lembut Bawah -->
                    <div class="position-absolute bottom-0 start-0 w-100 p-3 p-md-4" style="background: linear-gradient(180deg, transparent 0%, rgba(14, 18, 23, 0.9) 100%);">
                        <div class="d-flex align-items-center justify-content-between text-white">
                            <div>
                                <span class="d-block small text-white-50" style="font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase;">Gedung & Suasana Kafe</span>
                                <span class="fw-semibold small">Lantai 1 Indoor & Rooftop Lantai 2</span>
                            </div>
                            <span class="text-white-50 small fw-medium" style="font-size: 0.75rem;">
                                Senggoro, Bengkalis
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 2. Structured Info Bar: Rapi, Terstruktur & Responsif di Semua Perangkat -->
<section class="border-bottom overflow-hidden" style="background-color: #12161d; border-color: #21262d !important;">
    <div class="container py-2 py-md-3">
        <div class="row g-2 g-md-0 text-white align-items-center">
            
            <!-- Info 1: Jam Operasional -->
            <div class="col-6 col-md-3 border-md-end" style="border-color: #21262d !important;">
                <div class="p-2 p-md-3 d-flex align-items-center gap-2 gap-sm-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c;">
                        <i class="bi bi-clock fs-5"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1" style="min-width: 0;">
                        <span class="text-secondary small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;">Jam Buka</span>
                        <span class="fw-semibold text-white d-block" style="font-size: 0.8rem; line-height: 1.25; word-break: break-word;">{{ $jamBuka }}</span>
                    </div>
                </div>
            </div>

            <!-- Info 2: Lokasi Kafe -->
            <div class="col-6 col-md-3 border-md-end" style="border-color: #21262d !important;">
                <div class="p-2 p-md-3 d-flex align-items-center gap-2 gap-sm-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c;">
                        <i class="bi bi-geo-alt fs-5"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1" style="min-width: 0;">
                        <span class="text-secondary small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;">Alamat Kafe</span>
                        <span class="fw-semibold text-white d-block" style="font-size: 0.8rem; line-height: 1.25; word-break: break-word;">Senggoro, Bengkalis</span>
                    </div>
                </div>
            </div>

            <!-- Info 3: Fasilitas Kerja -->
            <div class="col-6 col-md-3 border-md-end" style="border-color: #21262d !important;">
                <div class="p-2 p-md-3 d-flex align-items-center gap-2 gap-sm-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c;">
                        <i class="bi bi-wifi fs-5"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1" style="min-width: 0;">
                        <span class="text-secondary small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;">Konektivitas</span>
                        <span class="fw-semibold text-white d-block" style="font-size: 0.8rem; line-height: 1.25; word-break: break-word;">Free WiFi & Colokan</span>
                    </div>
                </div>
            </div>

            <!-- Info 4: Layanan Pemesanan -->
            <div class="col-6 col-md-3">
                <div class="p-2 p-md-3 d-flex align-items-center gap-2 gap-sm-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c;">
                        <i class="bi bi-qr-code fs-5"></i>
                    </div>
                    <div class="min-w-0 flex-grow-1" style="min-width: 0;">
                        <span class="text-secondary small d-block" style="font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.5px;">Pemesanan</span>
                        <span class="fw-semibold text-white d-block" style="font-size: 0.8rem; line-height: 1.25; word-break: break-word;">Pesan QR & Takeaway</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    .text-justify, p.text-justify {
        text-align: justify !important;
        text-justify: inter-word !important;
        text-align-last: left !important;
        -webkit-hyphens: auto !important;
        hyphens: auto !important;
    }
    @media (min-width: 768px) {
        .border-md-end {
            border-right: 1px solid #21262d !important;
        }
    }
</style>

<!-- 3. Tentang Kami & Tim: Editorial Story Section -->
<section class="py-5 border-bottom" style="background-color: #0e1217; border-color: #21262d !important;">
    <div class="container py-3">
        <div class="row align-items-center g-4 g-lg-5">
            
            <!-- Foto Tim & Keluarga Master Cafe -->
            <div class="col-lg-6 order-2 order-lg-1">
                <div class="rounded-4 overflow-hidden shadow-lg position-relative hover-lift" style="border: 1px solid rgba(255, 255, 255, 0.12); aspect-ratio: 16/10; background-color: #161b22;">
                    <img src="{{ asset('images/cafe_team.jpg') }}" alt="Tim & Keluarga Master Cafe Bengkalis" class="w-100 h-100" style="object-fit: cover; object-position: center 30%;">
                    
                    <!-- Overlay Caption -->
                    <div class="position-absolute bottom-0 start-0 w-100 p-3" style="background: linear-gradient(180deg, transparent 0%, rgba(14, 18, 23, 0.92) 100%);">
                        <div class="d-flex align-items-center justify-content-between text-white">
                            <div>
                                <span class="d-block small text-white-50" style="font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase;">Keramahan Bengkalis</span>
                                <span class="fw-semibold small">Keluarga Besar {{ $namaTempat }}</span>
                            </div>
                            <span class="text-white-50 small fw-medium" style="font-size: 0.75rem;">
                                Warm Hospitality
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Narasi Tentang Tim & Pelayanan -->
            <div class="col-lg-6 order-1 order-lg-2">
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.78rem; letter-spacing: 1.5px;">Tentang Kami & Tim</span>
                <h2 class="fw-bold text-white mt-1 mb-3 display-6">Ketulusan di Balik Setiap Sajian</h2>
                <p class="text-secondary small mb-4 lh-base text-justify" style="font-size: 0.92rem; line-height: 1.75; text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important; -webkit-hyphens: auto !important; hyphens: auto !important;">
                    {{ $namaTempat }} tumbuh dari semangat kebersamaan dan dedikasi untuk menghadirkan tempat berkumpul paling nyaman di Bengkalis. Di balik setiap cangkir kopi nikmat dan hidangan lezat yang tersaji, ada tim yang bekerja dengan senyuman tulus dan komitmen untuk membuat kunjungan Anda selalu berkesan.
                </p>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background-color: #161b22; border: 1px solid #21262d;">
                            <h6 class="fw-semibold text-white mb-1"><i class="bi bi-people-fill text-warning me-2"></i>Pelayanan Hangat</h6>
                            <p class="text-secondary small mb-0 text-justify" style="font-size: 0.78rem; text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">Menyambut setiap pengunjung layaknya keluarga dengan ramah dan penuh perhatian.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background-color: #161b22; border: 1px solid #21262d;">
                            <h6 class="fw-semibold text-white mb-1"><i class="bi bi-heart-fill text-danger me-2"></i>Dibuat Sepenuh Hati</h6>
                            <p class="text-secondary small mb-0 text-justify" style="font-size: 0.78rem; text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">Racikan kopi barista dan sajian dapur diolah segar dengan standar mutu terbaik.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>



<!-- 5. Layanan & Panduan Memesan di Kafe -->
<section class="py-5 border-bottom" style="background-color: #0e1217; border-color: #21262d !important;">
    <div class="container py-2">
        <div class="text-center mb-5">
            <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.75rem; letter-spacing: 1.5px;">Kemudahan Layanan</span>
            <h3 class="fw-bold text-white mt-1 mb-2">Cara Memesan Saat Berkunjung</h3>
            <p class="text-secondary small mb-0 mx-auto" style="max-width: 500px;">
                Nikmati kenyamanan memesan mandiri tanpa perlu antre di kasir.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Langkah 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 rounded-3 p-4 h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 44px; height: 44px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </div>
                        <span class="fw-bold text-white-50" style="font-size: 1.1rem;">01</span>
                    </div>
                    <h6 class="fw-semibold text-white mb-1">Pilih Meja & Scan QR</h6>
                    <span class="text-secondary d-block mb-3" style="font-size: 0.78rem;">Pesan langsung dari smartphone</span>
                    <p class="text-secondary small mb-0 lh-base text-justify" style="text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">
                        Silakan pilih tempat duduk favorit Anda, lalu pindai stiker kode QR di atas meja dengan kamera ponsel untuk memilih menu.
                    </p>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 rounded-3 p-4 h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 44px; height: 44px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-cup-hot fs-5"></i>
                        </div>
                        <span class="fw-bold text-white-50" style="font-size: 1.1rem;">02</span>
                    </div>
                    <h6 class="fw-semibold text-white mb-1">Pesanan Diproses & Diantar</h6>
                    <span class="text-secondary d-block mb-3" style="font-size: 0.78rem;">Duduk santai di tempat Anda</span>
                    <p class="text-secondary small mb-0 lh-base text-justify" style="text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">
                        Pesanan langsung masuk ke sistem dapur dan barista. Pelayan akan mengantarkan sajian lezat langsung ke meja Anda.
                    </p>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 rounded-3 p-4 h-100" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width: 44px; height: 44px; background-color: rgba(192, 142, 92, 0.12); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                            <i class="bi bi-bag-check fs-5"></i>
                        </div>
                        <span class="fw-bold text-white-50" style="font-size: 1.1rem;">03</span>
                    </div>
                    <h6 class="fw-semibold text-white mb-1">Tersedia Juga Bawa Pulang</h6>
                    <span class="text-secondary d-block mb-3" style="font-size: 0.78rem;">Takeaway cepat & praktis</span>
                    <p class="text-secondary small mb-0 lh-base text-justify" style="text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">
                        Ingin menikmati kopi dalam perjalanan? Anda dapat memesan langsung untuk dibawa pulang melalui kasir kafe kami.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- 5. Section Kunjungan: Selaras & Menyatu dengan Desain Halaman -->
<section class="py-5 border-top" style="background-color: #12161d; border-color: #21262d !important;">
    <div class="container py-2">
        <div class="row align-items-center justify-content-between g-4">
            <div class="col-lg-8">
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.75rem; letter-spacing: 1.5px;">Kunjungi Kami</span>
                <h4 class="fw-bold text-white mt-1 mb-2">Suasana Hangat Menanti Anda</h4>
                <p class="text-secondary small mb-2 lh-base text-justify" style="text-align: justify !important; text-justify: inter-word !important; text-align-last: left !important;">
                    {{ $alamat }}
                </p>
                <div class="d-flex align-items-center gap-2 text-secondary small" style="font-size: 0.82rem;">
                    <i class="bi bi-clock" style="color: #c08e5c;"></i>
                    <span>{{ $jamBuka }}</span>
                </div>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="/lokasi" class="btn px-4 py-2.5 rounded-3 fw-semibold text-white d-inline-flex align-items-center gap-2 text-decoration-none shadow-sm" style="background: var(--gradient-bronze); border: none; font-size: 0.9rem;">
                    <i class="bi bi-geo-alt"></i> Petunjuk Arah & Peta
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
