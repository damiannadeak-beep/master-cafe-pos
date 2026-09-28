@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h1 class="h4 fw-bold text-white mb-0">Pengaturan Sistem</h1>
                <span class="text-secondary small fw-normal ms-2 fs-6">(Konfigurasi Utama)</span>
            </div>
            <p class="text-secondary small mb-0">Kelola identitas kafe, keamanan akun, integrasi pembayaran, absensi staf, dan backup database.</p>
        </div>
    </div>

    <!-- Interactive Navigation Tabs -->
    <div class="nav-tabs-wrapper mb-4">
        <ul class="nav nav-pills gap-1.5 p-1 rounded-3 overflow-auto flex-nowrap" id="settingsTab" role="tablist" style="background-color: #161b22; border: 1px solid #21262d; width: fit-content; max-width: 100%;">
            <li class="nav-item" role="presentation">
                <button class="nav-link active btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-profile" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-profile"
                        onclick="window.switchSettingsTab('tab-pane-profile')">
                    <i class="bi bi-shop me-1.5"></i> Profil & Akun
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-payment" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-payment"
                        onclick="window.switchSettingsTab('tab-pane-payment')">
                    <i class="bi bi-credit-card me-1.5"></i> Pembayaran & WA
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-printer" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-printer"
                        onclick="window.switchSettingsTab('tab-pane-printer')">
                    <i class="bi bi-printer me-1.5"></i> Printer Thermal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-lokasi" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-lokasi"
                        onclick="window.switchSettingsTab('tab-pane-lokasi')">
                    <i class="bi bi-geo-alt me-1.5"></i> Lokasi & Kontak
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-absensi" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-absensi"
                        onclick="window.switchSettingsTab('tab-pane-absensi')">
                    <i class="bi bi-calendar-check me-1.5"></i> Absensi & GPS
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn btn-sm fw-medium px-3 py-1.5 rounded-2 text-nowrap settings-nav-btn" 
                        id="tab-btn-backup" 
                        type="button" 
                        role="tab" 
                        data-tab-target="tab-pane-backup"
                        onclick="window.switchSettingsTab('tab-pane-backup')">
                    <i class="bi bi-shield-check me-1.5"></i> Backup Data
                </button>
            </li>
        </ul>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert" style="background-color: rgba(52, 211, 153, 0.1); border-color: rgba(52, 211, 153, 0.25); color: #34d399;">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="tab-content" id="settingsTabContent">
        <!-- ==================== TAB 1: PROFIL & AKUN ==================== -->
        <div class="tab-pane fade show active" id="tab-pane-profile" role="tabpanel" aria-labelledby="tab-btn-profile" tabindex="0">
            <div class="row g-4">
                @include("components.admin.settings-profile")
                @include("components.admin.settings-security")
            </div>
        </div>

        <!-- ==================== TAB 2: PEMBAYARAN & WA ==================== -->
        <div class="tab-pane fade" id="tab-pane-payment" role="tabpanel" aria-labelledby="tab-btn-payment" tabindex="0">
            <div class="row g-4">
                @include("components.admin.settings-payment-printer")
            </div>
        </div>

        <!-- ==================== TAB 3: PRINTER THERMAL ==================== -->
        <div class="tab-pane fade" id="tab-pane-printer" role="tabpanel" aria-labelledby="tab-btn-printer" tabindex="0">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-printer" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Pengaturan Printer Thermal (ESC/POS LAN/Wi-Fi)</h5>
                    </div>
                    <span class="text-secondary small fw-medium">Hardware Struk</span>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.settings.printer') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-printer')">
                        @csrf
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <h6 class="fw-bold mb-2 text-white fs-6">Koneksi Jaringan (Network)</h6>
                                <p class="text-secondary small mb-3">IP Address printer thermal yang terhubung dalam satu jaringan Wi-Fi/LAN dengan server POS ini.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label text-secondary small fw-medium mb-1">IP Address Printer</label>
                                    <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="printer_ip" value="{{ $settings['printer_ip'] ?? '' }}" placeholder="Contoh: 192.168.1.100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-secondary small fw-medium mb-1">Port Printer</label>
                                    <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="printer_port" value="{{ $settings['printer_port'] ?? '9100' }}" placeholder="Default: 9100">
                                </div>
                            </div>

                            <div class="col-lg-6 border-start-lg ps-lg-4" style="border-color: #21262d !important;">
                                <h6 class="fw-bold mb-2 text-white fs-6">Status Fitur Cetak Direct</h6>
                                <p class="text-secondary small mb-3">Jika dinonaktifkan, kasir akan menggunakan dialog cetak browser biasa.</p>
                                <div class="form-check form-switch p-0 mt-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input ms-0" type="checkbox" role="switch" id="printerActiveSwitch" name="printer_active" value="1" {{ isset($settings['printer_active']) && $settings['printer_active'] == '1' ? 'checked' : '' }} style="cursor: pointer; width: 2.5em; height: 1.3em;">
                                        <label class="form-check-label fw-medium text-white" for="printerActiveSwitch">Aktifkan Fitur Cetak Thermal ESC/POS</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                <i class="bi bi-check2"></i> Simpan Pengaturan Printer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 4: LOKASI & KONTAK ==================== -->
        <div class="tab-pane fade" id="tab-pane-lokasi" role="tabpanel" aria-labelledby="tab-btn-lokasi" tabindex="0">
            <div class="row g-4">
                <!-- Halaman Lokasi -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-geo-alt" style="color: #c08e5c;"></i>
                                <h5 class="mb-0 fw-bold text-white fs-6">Pengaturan Halaman Lokasi Konsumen</h5>
                            </div>
                            <span class="text-secondary small fw-medium">Publik</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.settings.lokasi') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-lokasi')">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Judul Halaman Lokasi</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_judul" value="{{ $settings['lokasi_judul'] ?? 'Lokasi & Jam Buka' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Deskripsi Halaman</label>
                                            <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_deskripsi" rows="3">{{ $settings['lokasi_deskripsi'] ?? 'Kunjungi Master Cafe untuk menikmati kopi dan hidangan istimewa dalam suasana yang tenang dan nyaman.' }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Nama Tempat / Landmark</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_utama_nama" value="{{ $settings['lokasi_utama_nama'] ?? 'Master Cafe' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Panduan Menuju Lokasi</label>
                                            <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_panduan" rows="2" placeholder="Misal: Seberang minimarket / patokan jalan (opsional)">{{ $settings['lokasi_panduan'] ?? '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 border-start-lg ps-lg-4" style="border-color: #21262d !important;">
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Alamat Lengkap</label>
                                            <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_utama_alamat" rows="2">{{ $settings['lokasi_utama_alamat'] ?? "Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711" }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Jam Operasional (Teks)</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_jam_operasional" value="{{ $settings['lokasi_jam_operasional'] ?? 'Setiap Hari: 10:00 - 23:00 WIB' }}">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Link Google Maps (Iframe SRC atau URL)</label>
                                            <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="lokasi_gmaps_url" rows="3" placeholder="https://maps.google.com/...">{{ $settings['lokasi_gmaps_url'] ?? 'https://maps.google.com/maps?q=Senggoro,%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed' }}</textarea>
                                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Mendukung URL biasa maupun kode iframe embed dari Google Maps.</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                        <i class="bi bi-check2"></i> Simpan Pengaturan Lokasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Halaman Kontak -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-lines-fill" style="color: #c08e5c;"></i>
                                <h5 class="mb-0 fw-bold text-white fs-6">Pengaturan Halaman Kontak</h5>
                            </div>
                            <span class="text-secondary small fw-medium">Publik</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.settings.kontak') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-lokasi')">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small fw-medium mb-1">Nomor WhatsApp Resmi</label>
                                        <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="kontak_wa" value="{{ $settings['kontak_wa'] ?? '+62 812-3456-7890' }}">
                                        <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Contoh format: +62 812-3456-7890</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small fw-medium mb-1">Alamat Email Resmi</label>
                                        <input type="email" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="kontak_email" value="{{ $settings['kontak_email'] ?? 'halo@mastercafe.com' }}">
                                    </div>
                                </div>

                                <div class="pt-4 mt-4 border-top" style="border-color: #21262d !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-white fs-6">Daftar Sosial Media Tambahan</h6>
                                            <span class="text-secondary small">Akun medsos resmi yang ditampilkan pada halaman kontak publik.</span>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 rounded-2" onclick="addSosmedRow()" style="border-color: #30363d; color: #d0d7de;">
                                            <i class="bi bi-plus-lg"></i> Tambah Sosmed
                                        </button>
                                    </div>
                                    <div id="sosmed-container">
                                        @php
                                            $sosmedList = json_decode($settings['kontak_sosmed_dynamic'] ?? '[]', true);
                                            if (empty($sosmedList) && (!empty($settings['kontak_ig']) || !empty($settings['kontak_tiktok']))) {
                                                if (!empty($settings['kontak_ig'])) {
                                                    $sosmedList[] = [
                                                        'platform' => 'Instagram',
                                                        'label' => $settings['kontak_ig'],
                                                        'url' => 'https://instagram.com/' . ltrim($settings['kontak_ig'], '@'),
                                                        'icon' => 'bi-instagram'
                                                    ];
                                                }
                                                if (!empty($settings['kontak_tiktok'])) {
                                                    $sosmedList[] = [
                                                        'platform' => 'TikTok',
                                                        'label' => $settings['kontak_tiktok'],
                                                        'url' => 'https://tiktok.com/@' . ltrim($settings['kontak_tiktok'], '@'),
                                                        'icon' => 'bi-tiktok'
                                                    ];
                                                }
                                            }
                                        @endphp

                                        @foreach($sosmedList as $index => $sosmed)
                                        <div class="row g-2 mb-3 align-items-end sosmed-row">
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary small fw-medium mb-1">Platform</label>
                                                <select class="form-select text-white sosmed-platform" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[platform][]" onchange="updateSosmedIcon(this)">
                                                    <option value="Instagram" data-icon="bi-instagram" {{ $sosmed['platform'] == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                                    <option value="TikTok" data-icon="bi-tiktok" {{ $sosmed['platform'] == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                                    <option value="Facebook" data-icon="bi-facebook" {{ $sosmed['platform'] == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                                    <option value="X/Twitter" data-icon="bi-twitter-x" {{ $sosmed['platform'] == 'X/Twitter' ? 'selected' : '' }}>X/Twitter</option>
                                                    <option value="YouTube" data-icon="bi-youtube" {{ $sosmed['platform'] == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                                    <option value="Lainnya" data-icon="bi-link-45deg" {{ $sosmed['platform'] == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label text-secondary small fw-medium mb-1">Label / Username</label>
                                                <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[label][]" value="{{ $sosmed['label'] }}" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="form-label text-secondary small fw-medium mb-1">URL Profil</label>
                                                <input type="url" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[url][]" value="{{ $sosmed['url'] }}" required>
                                            </div>
                                            <div class="col-md-1 text-end">
                                                <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="{{ $sosmed['icon'] ?? 'bi-link-45deg' }}">
                                                <button type="button" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" onclick="removeSosmedRow(this)" title="Hapus">
                                                    <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                        <i class="bi bi-check2"></i> Simpan Pengaturan Kontak
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 5: ABSENSI & GPS ==================== -->
        <div class="tab-pane fade" id="tab-pane-absensi" role="tabpanel" aria-labelledby="tab-btn-absensi" tabindex="0">
            <div class="row g-4">
                <!-- Shift & Toleransi -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-clock-history" style="color: #c08e5c;"></i>
                                <h5 class="mb-0 fw-bold text-white fs-6">Pengaturan Jam Shift & Toleransi Terlambat</h5>
                            </div>
                            <span class="text-secondary small fw-medium">SDM / Kasir</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.settings.absensi') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-absensi')">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small fw-medium mb-1">Jam Kerja Shift Pagi</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Mulai</span>
                                            <input type="time" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="shift_pagi_start" value="{{ $settings['shift_pagi_start'] ?? '08:00' }}" required>
                                            <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Selesai</span>
                                            <input type="time" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="shift_pagi_end" value="{{ $settings['shift_pagi_end'] ?? '17:00' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small fw-medium mb-1">Jam Kerja Shift Malam</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Mulai</span>
                                            <input type="time" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="shift_malam_start" value="{{ $settings['shift_malam_start'] ?? '17:00' }}" required>
                                            <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Selesai</span>
                                            <input type="time" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="shift_malam_end" value="{{ $settings['shift_malam_end'] ?? '00:00' }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small fw-medium mb-1">Toleransi Terlambat (Menit)</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="toleransi_terlambat" value="{{ $settings['toleransi_terlambat'] ?? '15' }}" min="0" required>
                                            <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Menit</span>
                                        </div>
                                        <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Batas waktu setelah jam shift dimulai sebelum status tercatat "Terlambat".</div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                        <i class="bi bi-check2"></i> Simpan Pengaturan Shift
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Koordinat Absensi Staf -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-pin-map" style="color: #c08e5c;"></i>
                                <h5 class="mb-0 fw-bold text-white fs-6">Titik Koordinat Absensi Staf Kasir</h5>
                            </div>
                            <span class="text-secondary small fw-medium">GPS Absensi</span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.settings.absensi') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-absensi')">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <h6 class="fw-bold mb-2 text-white fs-6">Koordinat Pusat Kafe</h6>
                                        <p class="text-secondary small mb-3">Titik koordinat (Latitude & Longitude) kafe. Staf kasir hanya bisa clock-in jika berada di dalam radius ini.</p>
                                        
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Latitude</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="warung_latitude" name="warung_latitude" value="{{ $settings['warung_latitude'] ?? '' }}" placeholder="Contoh: -6.200000">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Longitude</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="warung_longitude" name="warung_longitude" value="{{ $settings['warung_longitude'] ?? '' }}" placeholder="Contoh: 106.816666">
                                        </div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" onclick="getCurrentLocation()" style="border-color: #30363d; color: #d0d7de;">
                                            <i class="bi bi-crosshair text-danger"></i> Ambil Lokasi Saya Saat Ini
                                        </button>
                                        <div class="text-secondary small mt-1" id="location-status" style="font-size: 0.75rem;">Klik tombol di atas jika Anda sedang berada di kafe sekarang.</div>
                                    </div>

                                    <div class="col-lg-6 border-start-lg ps-lg-4" style="border-color: #21262d !important;">
                                        <h6 class="fw-bold mb-2 text-white fs-6">Radius Toleransi Absensi</h6>
                                        <p class="text-secondary small mb-3">Batas maksimal jarak (meter) dari titik koordinat. Rekomendasi: <strong>10 - 25 meter</strong> untuk mengantisipasi deviasi GPS HP.</p>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Radius Maksimal (Meter)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="absensi_radius_meter" value="{{ $settings['absensi_radius_meter'] ?? '15' }}" min="1" max="1000">
                                                <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Meter</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                        <i class="bi bi-check2"></i> Simpan Koordinat Absensi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Geofencing Pemesanan Meja -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-lock" style="color: #c08e5c;"></i>
                                <h5 class="mb-0 fw-bold text-white fs-6">Proteksi Lokasi Pemesanan Meja (Geofencing GPS)</h5>
                            </div>
                            <span class="badge rounded-2 px-2.5 py-1" style="{{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25);' : 'background-color: rgba(255, 255, 255, 0.05); color: #8b949e; border: 1px solid rgba(255, 255, 255, 0.1);' }}" id="geofenceStatusBadge">
                                {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'Proteksi Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('admin.settings.geofence') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-absensi')">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-lg-6">
                                        <h6 class="fw-bold mb-2 text-white fs-6">Status Proteksi Pemesanan Meja</h6>
                                        <p class="text-secondary small mb-3">
                                            Cegah pesanan fiktif dari foto QR meja. Jika aktif, pemesanan meja (Dine-In) hanya diproses jika HP pelanggan berada dalam radius kafe.
                                        </p>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="card p-3 text-center border h-100 geofence-card-select rounded-3" id="cardGeofenceOff" onclick="window.setGeofenceMode('0')" style="cursor: pointer; transition: all 0.2s ease; background-color: #0e1217; user-select: none;">
                                                    <input type="radio" name="geofence_active" value="0" class="d-none" id="radioGeofenceOff" onchange="window.setGeofenceMode('0')" {{ !isset($settings['geofence_active']) || $settings['geofence_active'] != '1' ? 'checked' : '' }}>
                                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                                        <i class="bi bi-x-circle fs-5 text-secondary me-2"></i>
                                                        <span class="fw-medium fs-6 text-white">NONAKTIF</span>
                                                    </div>
                                                    <div class="text-secondary small" style="font-size: 0.72rem;">Bebas pesan (Untuk Testing)</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card p-3 text-center border h-100 geofence-card-select rounded-3" id="cardGeofenceOn" onclick="window.setGeofenceMode('1')" style="cursor: pointer; transition: all 0.2s ease; background-color: #0e1217; user-select: none;">
                                                    <input type="radio" name="geofence_active" value="1" class="d-none" id="radioGeofenceOn" onchange="window.setGeofenceMode('1')" {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'checked' : '' }}>
                                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                                        <i class="bi bi-check-circle fs-5 me-2" style="color: #34d399;"></i>
                                                        <span class="fw-medium fs-6 text-white">AKTIF</span>
                                                    </div>
                                                    <div class="text-secondary small" style="font-size: 0.72rem;">Wajib berada di kafe</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="geofenceStatusBanner" class="p-3 rounded-3 mb-3 border" style="background-color: rgba(255, 255, 255, 0.02); border-color: #21262d !important;"></div>

                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Batas Radius Toleransi (Meter)</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d;" name="geofence_radius" value="{{ $settings['geofence_radius'] ?? '100' }}" min="10" max="5000">
                                                <span class="input-group-text bg-dark text-secondary border-secondary border-opacity-25 small">Meter</span>
                                            </div>
                                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Rekomendasi: <strong>50 - 100 meter</strong> (memberikan toleransi akurasi GPS HP).</div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 border-start-lg ps-lg-4" style="border-color: #21262d !important;">
                                        <h6 class="fw-bold mb-2 text-white fs-6">Titik Koordinat Lokasi Kafe</h6>
                                        <p class="text-secondary small mb-3">Titik pusat GPS kafe untuk verifikasi pesanan meja.</p>

                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Latitude Kafe</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="cafe_latitude" name="cafe_latitude" value="{{ $settings['cafe_latitude'] ?? ($settings['warung_latitude'] ?? '') }}" placeholder="Contoh: -6.2088">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label text-secondary small fw-medium mb-1">Longitude Kafe</label>
                                            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" id="cafe_longitude" name="cafe_longitude" value="{{ $settings['cafe_longitude'] ?? ($settings['warung_longitude'] ?? '') }}" placeholder="Contoh: 106.8456">
                                        </div>

                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" onclick="getCafeLocation()" style="border-color: #30363d; color: #d0d7de;">
                                                <i class="bi bi-crosshair text-danger"></i> Ambil Lokasi Kafe Saya
                                            </button>
                                            @php
                                                $cLat = $settings['cafe_latitude'] ?? ($settings['warung_latitude'] ?? '');
                                                $cLng = $settings['cafe_longitude'] ?? ($settings['warung_longitude'] ?? '');
                                            @endphp
                                            @if(!empty($cLat) && !empty($cLng))
                                                <a href="https://www.google.com/maps?q={{ $cLat }},{{ $cLng }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
                                                    <i class="bi bi-geo-alt" style="color: #c08e5c;"></i> Buka di Google Maps
                                                </a>
                                            @endif
                                        </div>
                                        <div class="text-secondary small" id="cafe-location-status" style="font-size: 0.75rem;"></div>
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                                        <i class="bi bi-check2"></i> Simpan Pengaturan Geofencing
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 6: BACKUP DATA ==================== -->
        <div class="tab-pane fade" id="tab-pane-backup" role="tabpanel" aria-labelledby="tab-btn-backup" tabindex="0">
            <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
                <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-database-check" style="color: #c08e5c;"></i>
                        <h5 class="mb-0 fw-bold text-white fs-6">Cadangan & Pemeliharaan Database</h5>
                    </div>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5 rounded-2" style="border-color: #30363d; color: #d0d7de;">
                        <i class="bi bi-folder2-open" style="color: #c08e5c;"></i> File Backup
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <p class="text-secondary small mb-1">
                                Backup database otomatis dijadwalkan oleh sistem setiap hari pukul <strong>03:00 WIB</strong>. Anda juga dapat membuat file cadangan secara instan kapan saja untuk mengamankan data transaksi dan master menu.
                            </p>
                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle me-1" style="color: #c08e5c;"></i> File cadangan tersimpan aman dan dapat langsung diunduh ke komputer Anda.
                            </div>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                            <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-backup')">
                                @csrf
                                <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Memproses...'; this.form.submit();">
                                    <i class="bi bi-cloud-arrow-down"></i> Backup Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Settings Navigation Tabs Styling */
#settingsTab .settings-nav-btn {
    color: #8b949e !important;
    background: transparent !important;
    border: 1px solid transparent !important;
    cursor: pointer !important;
    user-select: none !important;
    transition: all 0.18s ease;
}
#settingsTab .settings-nav-btn:hover {
    color: #ffffff !important;
    background: rgba(255, 255, 255, 0.05) !important;
}
#settingsTab .settings-nav-btn.active {
    background: rgba(192, 142, 92, 0.15) !important;
    color: #c08e5c !important;
    border: 1px solid rgba(192, 142, 92, 0.35) !important;
    box-shadow: 0 2px 8px rgba(192, 142, 92, 0.15) !important;
}

/* Explicit Tab Pane Visibility */
#settingsTabContent > .tab-pane {
    display: none !important;
}
#settingsTabContent > .tab-pane.active {
    display: block !important;
    animation: fadeInTab 0.18s ease-in-out;
}
@keyframes fadeInTab {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// Bulletproof Tab Switching System (Works on direct load & Turbo/SPA)
window.switchSettingsTab = function(targetId) {
    if (!targetId) return;
    targetId = targetId.replace('#', '');
    
    // 1. Deactivate all buttons
    const buttons = document.querySelectorAll('#settingsTab .settings-nav-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
        btn.setAttribute('aria-selected', 'false');
    });

    // 2. Hide all tab panes
    const panes = document.querySelectorAll('#settingsTabContent > .tab-pane');
    panes.forEach(function(pane) {
        pane.classList.remove('active', 'show');
        pane.style.setProperty('display', 'none', 'important');
    });

    // 3. Activate target button
    const targetBtn = document.querySelector(`#settingsTab button[data-tab-target="${targetId}"]`);
    if (targetBtn) {
        targetBtn.classList.add('active');
        targetBtn.setAttribute('aria-selected', 'true');
    }

    // 4. Show target pane
    const targetPane = document.getElementById(targetId);
    if (targetPane) {
        targetPane.style.setProperty('display', 'block', 'important');
        void targetPane.offsetWidth;
        targetPane.classList.add('active', 'show');
    }

    // 5. Persist active tab in sessionStorage and URL hash
    try {
        sessionStorage.setItem('admin_settings_active_tab', targetId);
        if (window.history && window.history.replaceState) {
            window.history.replaceState(null, null, '#' + targetId);
        } else {
            window.location.hash = '#' + targetId;
        }
    } catch(e) {}
};

// Immediate initialization for both direct loads and SPA swaps
(function initSettingsTabs() {
    let savedTab = null;
    try {
        if (window.location.hash) {
            savedTab = window.location.hash.replace('#', '');
        }
        if (!savedTab || !document.getElementById(savedTab)) {
            savedTab = sessionStorage.getItem('admin_settings_active_tab');
        }
    } catch(e) {}

    const aliasMap = {
        'section-profile': 'tab-pane-profile',
        'section-payment': 'tab-pane-payment',
        'section-printer': 'tab-pane-printer',
        'section-lokasi': 'tab-pane-lokasi',
        'section-absensi': 'tab-pane-absensi',
        'section-backup': 'tab-pane-backup',
        'profile': 'tab-pane-profile',
        'payment': 'tab-pane-payment',
        'printer': 'tab-pane-printer',
        'lokasi': 'tab-pane-lokasi',
        'absensi': 'tab-pane-absensi',
        'backup': 'tab-pane-backup'
    };

    if (savedTab && aliasMap[savedTab]) {
        savedTab = aliasMap[savedTab];
    }

    if (!savedTab || !document.getElementById(savedTab)) {
        savedTab = 'tab-pane-profile';
    }

    window.switchSettingsTab(savedTab);

    // Initial Geofence mode check
    setTimeout(function() {
        const checkedRadio = document.querySelector('input[name="geofence_active"]:checked');
        if (checkedRadio && typeof window.setGeofenceMode === 'function') {
            window.setGeofenceMode(checkedRadio.value);
        }
    }, 50);
})();

window.setGeofenceMode = function(val) {
    const isValOn = (val === '1' || val === 1 || val === true);
    const cardOff = document.getElementById('cardGeofenceOff');
    const cardOn = document.getElementById('cardGeofenceOn');
    const radioOff = document.getElementById('radioGeofenceOff');
    const radioOn = document.getElementById('radioGeofenceOn');
    const banner = document.getElementById('geofenceStatusBanner');
    const badge = document.getElementById('geofenceStatusBadge');

    if (isValOn) {
        if (radioOn) radioOn.checked = true;
        if (radioOff) radioOff.checked = false;
        if (cardOn) {
            cardOn.style.setProperty('border-color', 'rgba(52, 211, 153, 0.4)', 'important');
            cardOn.style.setProperty('background-color', 'rgba(52, 211, 153, 0.08)', 'important');
        }
        if (cardOff) {
            cardOff.style.setProperty('border-color', '#21262d', 'important');
            cardOff.style.setProperty('background-color', '#0e1217', 'important');
        }
        if (badge) {
            badge.style.backgroundColor = 'rgba(52, 211, 153, 0.12)';
            badge.style.color = '#34d399';
            badge.style.border = '1px solid rgba(52, 211, 153, 0.25)';
            badge.textContent = 'Proteksi Aktif';
        }
        if (banner) {
            banner.style.setProperty('background-color', 'rgba(52, 211, 153, 0.06)', 'important');
            banner.style.setProperty('border-color', 'rgba(52, 211, 153, 0.2)', 'important');
            banner.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check text-success fs-4 me-3"></i>
                    <div>
                        <div class="fw-semibold text-white small">Status: AKTIF</div>
                        <div class="text-secondary small" style="font-size: 0.75rem;">Proteksi GPS aktif. Konsumen wajib terdeteksi berada di kafe untuk memesan via QR meja.</div>
                    </div>
                </div>
            `;
        }
    } else {
        if (radioOff) radioOff.checked = true;
        if (radioOn) radioOn.checked = false;
        if (cardOff) {
            cardOff.style.setProperty('border-color', 'rgba(239, 68, 68, 0.4)', 'important');
            cardOff.style.setProperty('background-color', 'rgba(239, 68, 68, 0.08)', 'important');
        }
        if (cardOn) {
            cardOn.style.setProperty('border-color', '#21262d', 'important');
            cardOn.style.setProperty('background-color', '#0e1217', 'important');
        }
        if (badge) {
            badge.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            badge.style.color = '#8b949e';
            badge.style.border = '1px solid rgba(255, 255, 255, 0.1)';
            badge.textContent = 'Nonaktif';
        }
        if (banner) {
            banner.style.setProperty('background-color', 'rgba(255, 255, 255, 0.02)', 'important');
            banner.style.setProperty('border-color', '#21262d', 'important');
            banner.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-slash text-secondary fs-4 me-3"></i>
                    <div>
                        <div class="fw-semibold text-white small">Status: NONAKTIF</div>
                        <div class="text-secondary small" style="font-size: 0.75rem;">Proteksi lokasi dimatikan. Meja dapat dipesan tanpa verifikasi koordinat GPS.</div>
                    </div>
                </div>
            `;
        }
    }
};

window.getCurrentLocation = function() {
    const status = document.getElementById('location-status');
    if (!navigator.geolocation) {
        status.innerHTML = '<span class="text-danger">Browser tidak mendukung Geolocation.</span>';
        return;
    }
    status.innerHTML = '<span class="text-warning"><i class="bi bi-hourglass-split"></i> Mendeteksi lokasi...</span>';
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('warung_latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('warung_longitude').value = position.coords.longitude.toFixed(6);
            status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Lokasi berhasil didapatkan!</span>';
        },
        function(error) {
            status.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Gagal: ' + error.message + '</span>';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

window.getCafeLocation = function() {
    const status = document.getElementById('cafe-location-status');
    if (!navigator.geolocation) {
        status.innerHTML = '<span class="text-danger">Browser tidak mendukung Geolocation.</span>';
        return;
    }
    status.innerHTML = '<span class="text-warning"><i class="bi bi-hourglass-split"></i> Mendeteksi lokasi...</span>';
    navigator.geolocation.getCurrentPosition(
        function(position) {
            document.getElementById('cafe_latitude').value = position.coords.latitude.toFixed(6);
            document.getElementById('cafe_longitude').value = position.coords.longitude.toFixed(6);
            status.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Koordinat berhasil didapatkan!</span>';
        },
        function(error) {
            status.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle"></i> Gagal: ' + error.message + '</span>';
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

window.addSosmedRow = function() {
    const container = document.getElementById('sosmed-container');
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-3 align-items-end sosmed-row';
    newRow.innerHTML = `
        <div class="col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Platform</label>
            <select class="form-select text-white sosmed-platform" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[platform][]" onchange="updateSosmedIcon(this)">
                <option value="Instagram" data-icon="bi-instagram">Instagram</option>
                <option value="TikTok" data-icon="bi-tiktok">TikTok</option>
                <option value="Facebook" data-icon="bi-facebook">Facebook</option>
                <option value="X/Twitter" data-icon="bi-twitter-x">X/Twitter</option>
                <option value="YouTube" data-icon="bi-youtube">YouTube</option>
                <option value="Lainnya" data-icon="bi-link-45deg">Lainnya</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-secondary small fw-medium mb-1">Label / Username</label>
            <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[label][]" placeholder="@namaakun" required>
        </div>
        <div class="col-md-5">
            <label class="form-label text-secondary small fw-medium mb-1">URL Profil</label>
            <input type="url" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="sosmed[url][]" placeholder="https://..." required>
        </div>
        <div class="col-md-1 text-end">
            <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="bi-instagram">
            <button type="button" class="btn btn-sm btn-icon d-inline-flex align-items-center justify-content-center p-0 rounded-2" style="width: 36px; height: 36px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171;" onclick="removeSosmedRow(this)" title="Hapus">
                <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
            </button>
        </div>
    `;
    container.appendChild(newRow);
};

window.removeSosmedRow = function(btn) {
    btn.closest('.sosmed-row').remove();
};

window.updateSosmedIcon = function(selectEl) {
    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    const iconClass = selectedOpt.getAttribute('data-icon') || 'bi-link-45deg';
    const row = selectEl.closest('.sosmed-row');
    const hiddenIcon = row.querySelector('.sosmed-icon-input');
    if (hiddenIcon) hiddenIcon.value = iconClass;
};
</script>
@endsection
