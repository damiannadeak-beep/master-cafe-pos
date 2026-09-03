@extends('layouts.app')

@section('content')
<div class="container mt-4 mb-5 pb-5">
    <div class="text-center mb-5 mt-4">
        <h2 class="fw-bold text-white" style="font-family: 'Rye', serif;">Hubungi Kami</h2>
        <p class="fs-6 text-light opacity-75 mx-auto" style="max-width: 500px; font-weight: 300;">Punya pertanyaan, kritik, atau saran? Kami dengan senang hati akan mendengarkan Anda.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-lg p-4 p-md-5 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="row g-4">
                    
                    <div class="col-md-5 border-end border-secondary">
                        <h5 class="fw-bold mb-4 text-white" style="font-family: 'Outfit', sans-serif;">Informasi Kontak</h5>
                        
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

                        <a href="https://wa.me/{{ $waClean }}" target="_blank" class="text-decoration-none hover-lift d-block rounded p-2" style="transition: all 0.3s;">
                            <div class="d-flex align-items-center mb-2 item-kontak">
                                <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="background-color: rgba(72, 187, 120, 0.15); color: #48bb78; width: 45px; height: 45px;">
                                    <i class="bi bi-whatsapp fs-4"></i>
                                </div>
                                <div>
                                    <small class="d-block" style="color: #8b949e;">WhatsApp</small>
                                    <span class="fw-bold text-white">{{ $waRaw }}</span>
                                </div>
                            </div>
                        </a>

                        <a href="mailto:{{ $emailRaw }}" class="text-decoration-none hover-lift d-block rounded p-2 mt-2" style="transition: all 0.3s;">
                            <div class="d-flex align-items-center mb-2 item-kontak">
                                <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="background-color: rgba(178, 122, 77, 0.15); color: #c08e5c; width: 45px; height: 45px;">
                                    <i class="bi bi-envelope fs-4"></i>
                                </div>
                                <div>
                                    <small class="d-block" style="color: #8b949e;">Email</small>
                                    <span class="fw-bold text-white">{{ $emailRaw }}</span>
                                </div>
                            </div>
                        </a>

                        @foreach($sosmedDynamic as $sosmed)
                            <a href="{{ $sosmed['url'] }}" target="_blank" class="text-decoration-none hover-lift d-block rounded p-2 mt-2" style="transition: all 0.3s;">
                                <div class="d-flex align-items-center mb-2 item-kontak">
                                    <div class="rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="background-color: rgba(226, 232, 240, 0.1); color: #e2e8f0; width: 45px; height: 45px;">
                                        <i class="bi {{ $sosmed['icon'] ?? 'bi-link-45deg' }} fs-4"></i>
                                    </div>
                                    <div>
                                        <small class="d-block" style="color: #8b949e;">{{ $sosmed['platform'] }}</small>
                                        <span class="fw-bold text-white">{{ $sosmed['label'] }}</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    <div class="col-md-7 ps-md-4 mt-4 mt-md-0 pt-2">
                        <h5 class="fw-bold mb-4 text-white" style="font-family: 'Outfit', sans-serif;">Kirim Pesan</h5>
                        <form>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-white mb-2" style="font-size: 0.9rem;">Nama Lengkap</label>
                                <input type="text" class="form-control border-secondary text-white" placeholder="Masukkan nama Anda" style="background-color: #0e1217 !important; padding: 12px 16px;">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-white mb-2" style="font-size: 0.9rem;">Alamat Email</label>
                                <input type="email" class="form-control border-secondary text-white" placeholder="nama@email.com" style="background-color: #0e1217 !important; padding: 12px 16px;">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold text-white mb-2" style="font-size: 0.9rem;">Pesan Anda</label>
                                <textarea class="form-control border-secondary text-white" rows="4" placeholder="Ketik pesan Anda di sini..." style="background-color: #0e1217 !important; padding: 12px 16px;"></textarea>
                            </div>
                            <button type="button" class="btn w-100 fw-bold py-2 text-white btn-touch" style="background-color: #c08e5c; border: none;" onclick="alert('Terima kasih! Pesan Anda (simulasi) telah terkirim.')">
                                Kirim Pesan Sekarang
                            </button>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
<style>
    .form-control:focus {
        background-color: #161b22 !important;
        border-color: #c08e5c !important;
        box-shadow: 0 0 0 0.25rem rgba(178, 122, 77, 0.25) !important;
        color: white !important;
    }
    .form-control::placeholder {
        color: #6c757d;
    }
    .hover-lift:hover {
        background-color: rgba(255,255,255,0.03);
    }
</style>
@endsection
