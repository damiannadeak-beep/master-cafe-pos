@extends('layouts.app')

@section('content')
<style>
    /* Premium Dark & Bronze Hero Section */
    .hero-mastercafe {
        padding: 6rem 0 5rem 0;
        background: linear-gradient(180deg, #111418 0%, #1a1d24 100%);
        border-bottom: 1px solid #2a2d32;
        position: relative;
        overflow: hidden;
    }

    /* Abstract circles for background texture */
    .hero-mastercafe::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(178,122,77,0.15) 0%, rgba(17,20,24,0) 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .hero-mastercafe::after {
        content: '';
        position: absolute;
        bottom: -20%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(178,122,77,0.1) 0%, rgba(17,20,24,0) 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .hero-content {
        position: relative;
        z-index: 1;
    }

    .badge-premium {
        background: rgba(178, 122, 77, 0.1);
        color: #b27a4d;
        border: 1px solid rgba(178, 122, 77, 0.3);
        font-size: 0.88rem;
        font-weight: 600;
        padding: 0.45rem 1.25rem;
        border-radius: 999px;
        letter-spacing: 1px;
    }

    .hero-title-premium {
        font-weight: 400;
        font-size: 4.5rem;
        color: #ffffff;
        line-height: 1.1;
        text-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }

    .hero-title-cursive {
        color: #b27a4d;
        font-size: 3.5rem;
        display: block;
        margin-top: -10px;
        text-shadow: 0 4px 15px rgba(178,122,77,0.3);
    }

    .hero-sub-premium {
        color: #a0aec0;
        font-size: 1.1rem;
        line-height: 1.75;
        max-width: 660px;
    }

    /* Action Buttons */
    .btn-premium-primary {
        background: #b27a4d;
        color: #ffffff !important;
        font-weight: 600;
        padding: 0.9rem 2.25rem;
        border-radius: 999px;
        border: none;
        box-shadow: 0 8px 20px rgba(178, 122, 77, 0.25);
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }

    .btn-premium-primary:hover {
        background: #96653f;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(178, 122, 77, 0.4);
    }

    .btn-premium-outline {
        background: transparent;
        color: #b27a4d !important;
        font-weight: 600;
        padding: 0.9rem 2rem;
        border-radius: 999px;
        border: 1.5px solid #b27a4d;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }

    .btn-premium-outline:hover {
        background: rgba(178, 122, 77, 0.1);
        transform: translateY(-2px);
    }

    /* 3 Pilar Feature Cards */
    .pillar-card {
        background: #1a1d24;
        border: 1px solid #2a2d32;
        border-radius: 1.5rem;
        padding: 2.25rem 1.75rem;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .pillar-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(178, 122, 77, 0.15);
        border-color: #b27a4d;
    }

    .pillar-icon-box {
        width: 65px;
        height: 65px;
        border-radius: 50%;
        background: rgba(178, 122, 77, 0.1);
        border: 1px solid rgba(178, 122, 77, 0.3);
        color: #b27a4d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        box-shadow: 0 0 20px rgba(178, 122, 77, 0.2);
    }

    .text-bronze {
        color: #b27a4d !important;
    }
</style>

<!-- Hero Section -->
<section class="hero-mastercafe">
    <div class="container text-center hero-content">
        <div class="d-inline-block mb-4">
            <span class="badge-premium">
                <i class="bi bi-star-fill me-2"></i> EST. 2024
            </span>
        </div>

        <h1 class="hero-title-premium mb-2">
            MASTER
            <span class="font-cursive hero-title-cursive">Cafe</span>
        </h1>

        <p class="hero-sub-premium mx-auto mb-4 mt-4">
            Pengalaman bersantai dengan sentuhan klasik dan modern. Nikmati hidangan spesial kami melalui layanan pemesanan digital yang mulus dan elegan.
        </p>
        <p class="mx-auto mb-5 text-light fw-semibold" style="letter-spacing: 1px;">
            <i class="bi bi-clock text-bronze me-1"></i> Buka Setiap Hari: 10:00 AM - 23:00 PM
        </p>

        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="/katalog" class="btn btn-premium-primary text-decoration-none">
                <i class="bi bi-book-half me-2"></i> Eksplorasi Menu
            </a>
            <a href="/lokasi" class="btn btn-premium-outline text-decoration-none">
                <i class="bi bi-geo-alt me-2"></i> Kunjungi Kami
            </a>
        </div>
    </div>
</section>

<!-- 3 Pilar Pengalaman -->
<section class="py-5" style="background-color: #111418;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="font-cursive text-bronze fs-2 d-block mb-2">Layanan Spesial</span>
            <h2 class="fw-bold mb-0 text-white" style="font-family: 'Rye', serif; font-size: 2.5rem;">Cita Rasa & Kenyamanan</h2>
        </div>

        <div class="row g-4 justify-content-center">
            
            <!-- Pilar 1 -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100 text-center">
                    <div class="pillar-icon-box mx-auto">
                        <i class="bi bi-cup-hot fs-2"></i>
                    </div>
                    <h4 class="text-white mb-3" style="font-family: 'Rye', serif;">Sajian Premium</h4>
                    <p class="text-secondary mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Kopi dan hidangan racikan khusus yang dibuat dengan bahan berkualitas tinggi untuk memanjakan lidah Anda.
                    </p>
                </div>
            </div>

            <!-- Pilar 2 -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100 text-center">
                    <div class="pillar-icon-box mx-auto">
                        <i class="bi bi-qr-code-scan fs-2"></i>
                    </div>
                    <h4 class="text-white mb-3" style="font-family: 'Rye', serif;">Pesan Dari Meja</h4>
                    <p class="text-secondary mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Pindai kode QR di meja Anda dan nikmati kemudahan memesan menu secara digital tanpa perlu beranjak.
                    </p>
                </div>
            </div>

            <!-- Pilar 3 -->
            <div class="col-lg-4 col-md-6">
                <div class="pillar-card h-100 text-center">
                    <div class="pillar-icon-box mx-auto">
                        <i class="bi bi-moon-stars fs-2"></i>
                    </div>
                    <h4 class="text-white mb-3" style="font-family: 'Rye', serif;">Suasana Klasik</h4>
                    <p class="text-secondary mb-0" style="line-height: 1.75; font-size: 0.95rem;">
                        Ruangan bernuansa hangat dan temaram yang dirancang untuk memberikan kenyamanan maksimal saat berkumpul.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Tentang Kami Singkat -->
<section class="py-5 border-top" style="background-color: #1a1d24; border-color: #2a2d32 !important;">
    <div class="container text-center py-4">
        <h3 class="text-bronze mb-4" style="font-family: 'Rye', serif;">Tinggalkan Keramaian, Temukan Ketenangan.</h3>
        <p class="text-secondary mx-auto" style="max-width: 700px; line-height: 1.8;">
            Berdiri sejak 2024, Master Cafe lahir dari visi untuk menciptakan ruang di mana tradisi rasa bertemu dengan kepraktisan teknologi modern. Kami percaya setiap cangkir kopi memiliki cerita, dan kami ingin menjadi bagian dari cerita Anda.
        </p>
    </div>
</section>
@endsection
