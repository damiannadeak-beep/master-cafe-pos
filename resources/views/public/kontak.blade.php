@extends('layouts.app')

@section('content')
<div class="container my-5 py-3">
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

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <!-- Header Ringkas & Natural -->
            <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                <span class="text-uppercase fw-semibold" style="color: #c08e5c; font-size: 0.8rem; letter-spacing: 1.5px;">Master Cafe</span>
                <h2 class="fw-bold text-white mt-1 mb-2">Kontak & Reservasi</h2>
                <p class="text-secondary small mb-0">Silakan hubungi kami untuk informasi meja, acara, atau pertanyaan lainnya.</p>
            </div>

            <!-- List Kontak Sederhana & Otentik -->
            <div class="list-group list-group-flush rounded-3 overflow-hidden mb-4 contact-card-list" style="background-color: #161b22; border: 1px solid #21262d;">
                <!-- WhatsApp Item -->
                <div class="list-group-item bg-transparent text-white p-3 p-md-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <span class="text-secondary small d-block mb-1">WhatsApp Reservasi</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6 fw-medium text-white">{{ $waRaw }}</span>
                                <button type="button" class="btn btn-sm btn-icon btn-dark text-white-50 border-0 p-1" title="Salin Nomor WhatsApp" onclick="copyContactText('{{ $waRaw }}', 'Nomor WhatsApp berhasil disalin!')" style="width: 28px; height: 28px;">
                                    <i class="bi bi-copy" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="https://wa.me/{{ $waClean }}" target="_blank" class="btn btn-sm btn-outline-success px-3 py-2 d-inline-flex align-items-center gap-2">
                                <i class="bi bi-whatsapp"></i> Buka WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Email Item -->
                <div class="list-group-item bg-transparent text-white p-3 p-md-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <div>
                            <span class="text-secondary small d-block mb-1">Email Resmi</span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-6 fw-medium text-white">{{ $emailRaw }}</span>
                                <button type="button" class="btn btn-sm btn-icon btn-dark text-white-50 border-0 p-1" title="Salin Email" onclick="copyContactText('{{ $emailRaw }}', 'Alamat email berhasil disalin!')" style="width: 28px; height: 28px;">
                                    <i class="bi bi-copy" style="font-size: 0.8rem;"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <a href="mailto:{{ $emailRaw }}" class="btn btn-sm btn-outline-secondary px-3 py-2 d-inline-flex align-items-center gap-2 text-white" style="border-color: #30363d;">
                                <i class="bi bi-envelope"></i> Kirim Email
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Media Sosial Dinamis (Instagram, TikTok, Facebook, dll) -->
                @foreach($sosmedDynamic as $sosmed)
                    @php
                        $platform = $sosmed['platform'] ?? 'Sosial Media';
                        $label = $sosmed['label'] ?? '';
                        $rawUrl = $sosmed['url'] ?? '#';
                        $url = (str_starts_with($rawUrl, 'http://') || str_starts_with($rawUrl, 'https://')) ? $rawUrl : 'https://' . ltrim($rawUrl, '/');
                        $icon = $sosmed['icon'] ?? 'bi-link-45deg';

                        $btnClass = 'btn-outline-secondary text-white';
                        $btnStyle = 'border-color: #30363d; background-color: rgba(255, 255, 255, 0.03);';
                        $actionText = 'Buka ' . $platform;

                        if (stripos($platform, 'instagram') !== false) {
                            $icon = 'bi-instagram';
                            $btnClass = 'btn-outline-danger';
                            $btnStyle = 'border-color: rgba(225, 48, 108, 0.4); color: #f43f5e; background-color: rgba(225, 48, 108, 0.06);';
                            $actionText = 'Kunjungi Instagram';
                        } elseif (stripos($platform, 'tiktok') !== false) {
                            $icon = 'bi-tiktok';
                            $btnClass = 'btn-outline-info';
                            $btnStyle = 'border-color: rgba(0, 242, 254, 0.4); color: #38bdf8; background-color: rgba(0, 242, 254, 0.06);';
                            $actionText = 'Buka TikTok';
                        } elseif (stripos($platform, 'facebook') !== false) {
                            $icon = 'bi-facebook';
                            $btnClass = 'btn-outline-primary';
                            $btnStyle = 'border-color: rgba(24, 119, 242, 0.4); color: #60a5fa; background-color: rgba(24, 119, 242, 0.06);';
                            $actionText = 'Buka Facebook';
                        } elseif (stripos($platform, 'youtube') !== false) {
                            $icon = 'bi-youtube';
                            $btnClass = 'btn-outline-danger';
                            $btnStyle = 'border-color: rgba(255, 0, 0, 0.4); color: #f87171; background-color: rgba(255, 0, 0, 0.06);';
                            $actionText = 'Tonton YouTube';
                        } elseif (stripos($platform, 'twitter') !== false || stripos($platform, 'x') !== false) {
                            $icon = 'bi-twitter-x';
                            $btnClass = 'btn-outline-light text-white';
                            $btnStyle = 'border-color: rgba(255, 255, 255, 0.3); background-color: rgba(255, 255, 255, 0.06);';
                            $actionText = 'Kunjungi X';
                        }
                    @endphp
                    <div class="list-group-item bg-transparent text-white p-3 p-md-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                            <div>
                                <span class="text-secondary small d-block mb-1">{{ $platform }} Resmi</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fs-6 fw-medium text-white">{{ $label }}</span>
                                    <button type="button" class="btn btn-sm btn-icon btn-dark text-white-50 border-0 p-1" title="Salin {{ $platform }}" onclick="copyContactText('{{ $label }}', '{{ $platform }} berhasil disalin!')" style="width: 28px; height: 28px;">
                                        <i class="bi bi-copy" style="font-size: 0.8rem;"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <a href="{{ $url }}" target="_blank" class="btn btn-sm {{ $btnClass }} px-3 py-2 d-inline-flex align-items-center gap-2" style="{{ $btnStyle }}">
                                    <i class="bi {{ $icon }}"></i> {{ $actionText }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Section FAQ & Reservasi Acara -->
            <div class="card border-0 rounded-3 p-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <h6 class="fw-semibold text-white mb-3 d-flex align-items-center gap-2">
                    <i class="bi bi-question-circle" style="color: #c08e5c;"></i> Pertanyaan Umum & Reservasi Acara
                </h6>
                
                <div class="accordion accordion-flush" id="faqAccordion">
                    <div class="accordion-item bg-transparent text-white border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white shadow-none px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Apakah bisa melakukan reservasi tempat untuk acara / rapat?
                            </button>
                        </h2>
                        <div id="faq1" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 pt-0 text-secondary small lh-base">
                                Ya, kami menerima reservasi tempat untuk ulang tahun, rapat komunitas, atau acara khusus lainnya. Silakan hubungi kami via WhatsApp minimal 1 hari sebelumnya.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item bg-transparent text-white border-bottom border-secondary border-opacity-25">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed bg-transparent text-white shadow-none px-0 py-3 small fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Metode pembayaran apa saja yang didukung?
                            </button>
                        </h2>
                        <div id="faq2" class="collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body px-0 pt-0 text-secondary small lh-base">
                                Kami menerima pembayaran Tunai, QRIS (Gopay, OVO, Dana, ShopeePay, BCA Mobile, dll), serta Transfer Bank langsung di kasir atau sistem pemesanan online.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    .contact-card-list .list-group-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
    }
    .contact-card-list .list-group-item:last-child {
        border-bottom: none !important;
    }
    .accordion-button::after {
        filter: invert(1);
    }
    .accordion-button:not(.collapsed) {
        color: #c08e5c !important;
    }
</style>

<script>
    function copyContactText(text, msg) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                if (window.showToast) window.showToast(msg, 'success');
                else alert(msg);
            }).catch(() => {
                fallbackCopyText(text, msg);
            });
        } else {
            fallbackCopyText(text, msg);
        }
    }

    function fallbackCopyText(text, msg) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        if (window.showToast) window.showToast(msg, 'success');
        else alert(msg);
    }
</script>
@endsection


