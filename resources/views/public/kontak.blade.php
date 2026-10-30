@extends('layouts.app')

@section('content')
<div class="container my-3 my-md-5 py-2 py-md-3 kontak-page">
    @php
        $waRaw = \App\Models\Setting::getVal('kontak_wa') ?? '+62 812-3456-7890';
        $waClean = preg_replace('/[^0-9]/', '', $waRaw);
        if (str_starts_with($waClean, '0')) {
            $waClean = '62' . substr($waClean, 1);
        }
        $emailRaw = \App\Models\Setting::getVal('kontak_email') ?? 'halo@mastercafe.com';

        $sosmedDynamic = json_decode(\App\Models\Setting::getVal('kontak_sosmed_dynamic') ?? '[]', true);
        if (empty($sosmedDynamic)) {
            $igRaw = \App\Models\Setting::getVal('kontak_ig');
            $tiktokRaw = \App\Models\Setting::getVal('kontak_tiktok');
            if (!empty($igRaw)) {
                $sosmedDynamic[] = ['platform' => 'Instagram', 'url' => 'https://instagram.com/'.ltrim($igRaw ?? 'mastercafe24', '@'), 'label' => $igRaw ?? '@mastercafe24', 'icon' => 'bi-instagram'];
            }
            if (!empty($tiktokRaw)) {
                $sosmedDynamic[] = ['platform' => 'TikTok', 'url' => 'https://tiktok.com/@'.ltrim($tiktokRaw, '@'), 'label' => $tiktokRaw, 'icon' => 'bi-tiktok'];
            }
        }
    @endphp

    <!-- Header Halaman: Bersih & Menyambut -->
    <div class="row mb-4 mb-md-5">
        <div class="col-lg-8">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.28);">
                <i class="bi bi-chat-dots text-warning" style="font-size: 0.85rem;"></i>
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.75rem; letter-spacing: 1.5px;">Saluran Komunikasi Resmi</span>
            </div>
            <h1 class="fw-bold text-white mb-2" style="font-size: clamp(1.8rem, 3.5vw, 2.5rem); letter-spacing: -0.5px;">Hubungi Master Cafe</h1>
            <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6; max-width: 640px;">
                Punya pertanyaan seputar ketersediaan meja, booking acara, atau ingin menjalin kerjasama kemitraan? Tim kami siap melayani Anda.
            </p>
        </div>
    </div>

    <!-- 1. Saluran Utama: WhatsApp & Email (Berdampingan Rapi) -->
    <div class="row g-4 mb-4 mb-md-5">
        
        <!-- Kartu 1: WhatsApp Layanan Pelanggan & Reservasi (Featured) -->
        <div class="col-md-6 col-lg-6">
            <div class="card border-0 rounded-4 p-4 text-white shadow-sm h-100 d-flex flex-column justify-content-between" style="background-color: #141820; border: 1px solid #232936 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3);">
                            <i class="bi bi-whatsapp fs-4"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1.5" style="background: rgba(34, 197, 94, 0.12); border: 1px solid rgba(34, 197, 94, 0.25); color: #4ade80; font-size: 0.75rem;">
                            <span class="live-dot-green"></span>
                            Respon Cepat
                        </span>
                    </div>

                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.8px; font-size: 0.72rem;">Saluran Komunikasi Cepat</span>
                    <h3 class="fw-bold text-white mt-1 mb-2" style="font-size: 1.35rem;">WhatsApp Reservasi</h3>
                    <p class="text-secondary small mb-3 lh-base" style="font-size: 0.88rem; color: #cbd5e1 !important;">
                        Layanan cepat untuk reservasi meja rombongan, booking tempat acara/arisan, dan konfirmasi ketersediaan menu.
                    </p>

                    <div class="mb-4 pt-1">
                        <span class="text-secondary d-block mb-1" style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Resmi:</span>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-white" style="font-size: 1.35rem; letter-spacing: 0.5px;">{{ $waRaw }}</span>
                            <button type="button" onclick="copyContact('{{ addslashes($waRaw) }}', 'Nomor WhatsApp')" class="btn btn-sm text-secondary p-0 border-0 d-inline-flex align-items-center gap-1 text-decoration-none copy-link-btn" title="Salin Nomor WhatsApp">
                                <i class="bi bi-copy" style="font-size: 0.85rem;"></i>
                                <span style="font-size: 0.78rem;">Salin</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="https://wa.me/{{ $waClean }}?text={{ urlencode('Halo Master Cafe, saya ingin reservasi meja / bertanya informasi seputar kafe.') }}" target="_blank" class="btn w-100 py-3 rounded-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none" style="background-color: #22c55e; border: none; font-size: 0.92rem;">
                        <i class="bi bi-whatsapp fs-5"></i>
                        <span>Mulai Chat WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Kartu 2: Email Resmi & Korespondensi Bisnis -->
        <div class="col-md-6 col-lg-6">
            <div class="card border-0 rounded-4 p-4 text-white shadow-sm h-100 d-flex flex-column justify-content-between" style="background-color: #141820; border: 1px solid #232936 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3);">
                            <i class="bi bi-envelope-at-fill fs-4"></i>
                        </div>
                        <span class="badge rounded-pill px-3 py-1.5 fw-medium" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #dfb082; font-size: 0.75rem;">
                            Korespondensi Formal
                        </span>
                    </div>

                    <span class="text-secondary small text-uppercase fw-semibold" style="letter-spacing: 0.8px; font-size: 0.72rem;">Saluran Bisnis & Administrasi</span>
                    <h3 class="fw-bold text-white mt-1 mb-2" style="font-size: 1.35rem;">Email Resmi</h3>
                    <p class="text-secondary small mb-3 lh-base" style="font-size: 0.88rem; color: #cbd5e1 !important;">
                        Khusus untuk pengiriman proposal event, katering skala besar, penawaran sponsorship, kemitraan, atau urusan bisnis lainnya.
                    </p>

                    <div class="mb-4 pt-1">
                        <span class="text-secondary d-block mb-1" style="font-size: 0.76rem; text-transform: uppercase; letter-spacing: 0.5px;">Alamat Email Resmi:</span>
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold text-white text-truncate" style="font-size: 1.25rem; letter-spacing: 0.2px;">{{ $emailRaw }}</span>
                            <button type="button" onclick="copyContact('{{ addslashes($emailRaw) }}', 'Alamat Email')" class="btn btn-sm text-secondary p-0 border-0 d-inline-flex align-items-center gap-1 text-decoration-none copy-link-btn flex-shrink-0" title="Salin Alamat Email">
                                <i class="bi bi-copy" style="font-size: 0.85rem;"></i>
                                <span style="font-size: 0.78rem;">Salin</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div>
                    <a href="mailto:{{ $emailRaw }}?subject={{ urlencode('Inquiry Kerjasama / Informasi - Master Cafe') }}" class="btn w-100 py-3 rounded-3 fw-semibold text-white d-flex align-items-center justify-content-center gap-2 shadow-sm text-decoration-none" style="background: var(--gradient-bronze); border: none; font-size: 0.92rem;">
                        <i class="bi bi-send-fill fs-5"></i>
                        <span>Kirim Pesan Email</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- 2. Media Sosial Resmi: Menyesuaikan Dinamis Sesuai Pengaturan Admin Owner -->
    <div class="mt-4 pt-2">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom border-secondary border-opacity-25">
            <div>
                <h4 class="fw-bold text-white mb-1" style="font-size: 1.25rem;">Media Sosial Resmi</h4>
                <p class="text-secondary small mb-0">Ikuti update kegiatan, dokumentasi suasana kafe, dan rilisan menu terbaru kami.</p>
            </div>
            <span class="badge rounded-pill px-3 py-1.5 fw-medium text-secondary" style="background: rgba(255, 255, 255, 0.05); border: 1px solid #232936; font-size: 0.75rem; width: fit-content;">
                Akun Terverifikasi
            </span>
        </div>

        @if(!empty($sosmedDynamic) && count($sosmedDynamic) > 0)
            <div class="sosmed-grid">
                @foreach($sosmedDynamic as $sosmed)
                    @php
                        $platform = $sosmed['platform'] ?? 'Media Sosial';
                        $label = $sosmed['label'] ?? '';
                        $rawUrl = $sosmed['url'] ?? '#';
                        $url = (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://')) ? $rawUrl : 'https://' . ltrim($rawUrl, '/');

                        // Warna & Ikon Dinamis Berdasarkan Platform
                        $platformLower = strtolower($platform);
                        $icon = 'bi-link-45deg';
                        $iconColor = '#c08e5c';
                        $iconBg = 'rgba(192, 142, 92, 0.12)';
                        $accentBorder = 'rgba(192, 142, 92, 0.25)';

                        if (str_contains($platformLower, 'instagram')) {
                            $icon = 'bi-instagram';
                            $iconColor = '#f43f5e';
                            $iconBg = 'rgba(244, 63, 94, 0.12)';
                            $accentBorder = 'rgba(244, 63, 94, 0.25)';
                        } elseif (str_contains($platformLower, 'tiktok')) {
                            $icon = 'bi-tiktok';
                            $iconColor = '#38bdf8';
                            $iconBg = 'rgba(56, 189, 248, 0.12)';
                            $accentBorder = 'rgba(56, 189, 248, 0.25)';
                        } elseif (str_contains($platformLower, 'facebook')) {
                            $icon = 'bi-facebook';
                            $iconColor = '#60a5fa';
                            $iconBg = 'rgba(96, 165, 250, 0.12)';
                            $accentBorder = 'rgba(96, 165, 250, 0.25)';
                        } elseif (str_contains($platformLower, 'youtube')) {
                            $icon = 'bi-youtube';
                            $iconColor = '#f87171';
                            $iconBg = 'rgba(248, 113, 113, 0.12)';
                            $accentBorder = 'rgba(248, 113, 113, 0.25)';
                        } elseif (str_contains($platformLower, 'twitter') || str_contains($platformLower, 'x')) {
                            $icon = 'bi-twitter-x';
                            $iconColor = '#f1f5f9';
                            $iconBg = 'rgba(241, 245, 249, 0.12)';
                            $accentBorder = 'rgba(241, 245, 249, 0.25)';
                        }
                    @endphp

                    <div class="card border-0 rounded-4 p-3.5 text-white shadow-sm sosmed-card" style="background-color: #141820; border: 1px solid #232936 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: {{ $iconBg }}; color: {{ $iconColor }}; border: 1px solid {{ $accentBorder }};">
                                <i class="bi {{ $icon }} fs-5"></i>
                            </div>
                            <button type="button" onclick="copyContact('{{ addslashes($label) }}', '{{ $platform }}')" class="btn btn-copy-chip d-inline-flex align-items-center gap-1 py-1 px-2.5 rounded-2" title="Salin {{ $platform }}">
                                <i class="bi bi-copy" style="font-size: 0.75rem;"></i>
                                <span style="font-size: 0.75rem;">Salin</span>
                            </button>
                        </div>

                        <span class="text-secondary small fw-medium" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">{{ $platform }} Resmi</span>
                        <div class="fw-semibold text-white text-truncate my-1" style="font-size: 0.95rem;">
                            {{ $label }}
                        </div>

                        <div class="mt-3 pt-2">
                            <a href="{{ $url }}" target="_blank" class="btn btn-sm w-100 py-2 rounded-3 fw-medium d-flex align-items-center justify-content-center gap-1.5 text-decoration-none" style="background: rgba(255, 255, 255, 0.05); color: #f8fafc; border: 1px solid #30363d; font-size: 0.82rem;">
                                <span>Kunjungi Profil</span>
                                <i class="bi bi-box-arrow-up-right" style="font-size: 0.75rem;"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 rounded-4 p-4 text-center" style="background-color: #141820; border: 1px solid #232936 !important;">
                <p class="text-secondary mb-0 small">Kanal media sosial resmi sedang disiapkan oleh pengelola Master Cafe.</p>
            </div>
        @endif
    </div>

</div>

<style>
    .kontak-page {
        font-family: 'Poppins', sans-serif !important;
    }
    .kontak-page .card {
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .kontak-page .card:hover {
        border-color: rgba(192, 142, 92, 0.35) !important;
        transform: translateY(-2px);
    }
    .copy-link-btn {
        transition: color 0.2s ease, transform 0.2s ease;
    }
    .copy-link-btn:hover {
        color: #f8fafc !important;
        transform: translateY(-1px);
    }
    .sosmed-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    .live-dot-green {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #22c55e;
        box-shadow: 0 0 6px #22c55e;
        display: inline-block;
    }
</style>

<script>
    function copyContact(text, label) {
        if (!text) return;
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof window.showToast === 'function') {
                    window.showToast(`${label} berhasil disalin!`, 'success');
                } else {
                    alert(`${label} berhasil disalin!`);
                }
            }).catch(function() {
                fallbackCopyContact(text, label);
            });
        } else {
            fallbackCopyContact(text, label);
        }
    }

    function fallbackCopyContact(text, label) {
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
                window.showToast(`${label} berhasil disalin!`, 'success');
            } else {
                alert(`${label} berhasil disalin!`);
            }
        } catch (e) {
            alert(`Gagal menyalin ${label}.`);
        }
        document.body.removeChild(temp);
    }
</script>
@endsection
