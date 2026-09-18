@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="bi bi-gear me-2"></i>Pengaturan</h2>
            <p class="text-white-50 mb-0">Kelola informasi warung dan keamanan akun Anda.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @include("components.admin.settings-profile")

        @include("components.admin.settings-security")

        @include("components.admin.settings-payment-printer")

                            <!-- Kolom AI API Key & WA Gateway -->
                            <div class="col-md-6 border-start ps-md-4">
                                <h6 class="fw-bold mb-3 text-success"><i class="bi bi-whatsapp me-2"></i>WhatsApp Gateway (Notifikasi Takeaway)</h6>
                                <p class="text-white-50 small">Masukkan API Token untuk mengirim notifikasi WA otomatis saat pesanan Takeaway siap diambil.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">WA Gateway API Token / Key</label>
                                    <input type="text" class="form-control text-white" name="wa_gateway_api_key" value="{{ $settings['wa_gateway_api_key'] ?? '' }}" placeholder="Contoh: u9#xK8mPzL2..." style="background-color: #0e1217; border-color: #30363d;">
                                    <div class="form-text text-white-50">Token resmi dari provider (misal Fonnte.com / Wablas).</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">WA Gateway API URL</label>
                                    <input type="text" class="form-control text-white" name="wa_gateway_url" value="{{ $settings['wa_gateway_url'] ?? 'https://api.fonnte.com/send' }}" placeholder="https://api.fonnte.com/send" style="background-color: #0e1217; border-color: #30363d;">
                                    <div class="form-text text-white-50">Default Fonnte: <code>https://api.fonnte.com/send</code></div>
                                </div>

                                <hr class="border-secondary my-4">

                                <h6 class="fw-bold mb-2">Sistem Verifikasi Otomatis Struk <span class="badge bg-secondary">Opsional</span></h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">API Key (Gemini AI)</label>
                                    <input type="text" class="form-control text-white" name="gemini_api_key" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIzaSyB..." style="background-color: #0e1217; border-color: #30363d;">
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success w-100 fw-bold">Simpan Pengaturan Pembayaran & WA Gateway</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Kolom Pengaturan Absensi -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2 text-warning"></i>Pengaturan Absensi & Shift</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.absensi') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Kerja Shift Pagi</label>
                                <div class="input-group">
                                    <span class="input-group-text">Mulai</span>
                                    <input type="time" class="form-control" name="shift_pagi_start" value="{{ $settings['shift_pagi_start'] ?? '08:00' }}" required>
                                    <span class="input-group-text">Selesai</span>
                                    <input type="time" class="form-control" name="shift_pagi_end" value="{{ $settings['shift_pagi_end'] ?? '17:00' }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Jam Kerja Shift Malam</label>
                                <div class="input-group">
                                    <span class="input-group-text">Mulai</span>
                                    <input type="time" class="form-control" name="shift_malam_start" value="{{ $settings['shift_malam_start'] ?? '17:00' }}" required>
                                    <span class="input-group-text">Selesai</span>
                                    <input type="time" class="form-control" name="shift_malam_end" value="{{ $settings['shift_malam_end'] ?? '00:00' }}" required>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label fw-bold">Toleransi Terlambat (Menit)</label>
                                <input type="number" class="form-control text-white border-secondary  w-50" name="toleransi_terlambat" value="{{ $settings['toleransi_terlambat'] ?? '15' }}" min="0" required>
                                <div class="form-text">Batas maksimal dari jam mulai shift. Jika melebihi ini maka statusnya "Terlambat".</div>
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-warning fw-bold px-4">Simpan Pengaturan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Printer Thermal -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-printer me-2 text-info"></i>Pengaturan Printer Thermal (ESC/POS)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.printer') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Koneksi Jaringan (Network)</h6>
                                <p class="text-white-50 small">Masukkan IP Address printer thermal Anda yang terhubung dalam satu jaringan Wi-Fi/LAN dengan server ini.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">IP Address Printer</label>
                                    <input type="text" class="form-control" name="printer_ip" value="{{ $settings['printer_ip'] ?? '' }}" placeholder="Contoh: 192.168.1.100">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Port</label>
                                    <input type="text" class="form-control" name="printer_port" value="{{ $settings['printer_port'] ?? '9100' }}" placeholder="Default: 9100">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Status Fungsionalitas</h6>
                                <div class="form-check form-switch mb-3 mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" id="printerActiveSwitch" name="printer_active" value="1" {{ isset($settings['printer_active']) && $settings['printer_active'] == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="printerActiveSwitch">Aktifkan Fitur Cetak Thermal ESC/POS</label>
                                    <div class="form-text">Jika dimatikan, fitur cetak thermal akan disembunyikan dari layar kasir.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-info text-white w-100 fw-bold">Simpan Pengaturan Printer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Kolom Pengaturan Lokasi -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-2 text-primary"></i>Pengaturan Halaman Lokasi</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.lokasi') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Judul Halaman Lokasi</label>
                                    <input type="text" class="form-control" name="lokasi_judul" value="{{ $settings['lokasi_judul'] ?? 'Lokasi Master Cafe' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Deskripsi Halaman</label>
                                    <textarea class="form-control" name="lokasi_deskripsi" rows="3">{{ $settings['lokasi_deskripsi'] ?? 'Kami berlokasi di pusat kota, mudah dijangkau, dan menawarkan suasana santai untuk menikmati hidangan tradisional.' }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Tempat/Jalan</label>
                                    <input type="text" class="form-control" name="lokasi_utama_nama" value="{{ $settings['lokasi_utama_nama'] ?? 'Master Cafe' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Panduan Menuju Lokasi</label>
                                    <textarea class="form-control" name="lokasi_panduan" rows="3">{{ $settings['lokasi_panduan'] ?? 'Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711' }}</textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Alamat Lengkap</label>
                                    <textarea class="form-control" name="lokasi_utama_alamat" rows="2">{{ $settings['lokasi_utama_alamat'] ?? "Jl. Bantan, Senggoro, Bengkalis, Riau, Indonesia 28711" }}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jam Operasional (Teks)</label>
                                    <input type="text" class="form-control" name="lokasi_jam_operasional" value="{{ $settings['lokasi_jam_operasional'] ?? 'Buka Setiap Hari: 10:00 AM - 23:00 PM' }}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Link Google Maps (Iframe SRC) atau URL biasa</label>
                                    <textarea class="form-control" name="lokasi_gmaps_url" rows="2" placeholder="https://maps.google.com/...">{{ $settings['lokasi_gmaps_url'] ?? 'https://maps.google.com/maps?q=Senggoro,%20Bengkalis&t=&z=16&ie=UTF8&iwloc=&output=embed' }}</textarea>
                                    <div class="form-text">Bisa menggunakan URL biasa atau src dari embed peta (Google Maps).</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Pengaturan Lokasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Absensi Geolocation -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-geo-fill me-2 text-danger"></i>Pengaturan Absensi Geolocation (Lokasi Warung)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.absensi') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Koordinat Pusat Warung</h6>
                                <p class="text-white-50 small mb-3">Tentukan titik kordinat (Latitude & Longitude) warung. Staf Waitress hanya bisa melakukan absensi jika berada dalam radius tertentu dari titik ini.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Latitude</label>
                                    <input type="text" class="form-control" id="warung_latitude" name="warung_latitude" value="{{ $settings['warung_latitude'] ?? '' }}" placeholder="Contoh: -6.200000">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Longitude</label>
                                    <input type="text" class="form-control" id="warung_longitude" name="warung_longitude" value="{{ $settings['warung_longitude'] ?? '' }}" placeholder="Contoh: 106.816666">
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-sm fw-bold mb-2" onclick="getCurrentLocation()">
                                    <i class="bi bi-crosshair me-1"></i> Ambil Lokasi Saya Saat Ini
                                </button>
                                <div class="form-text text-white-50 small" id="location-status">Klik tombol di atas jika Anda sedang berada tepat di warung sekarang.</div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Radius Absensi</h6>
                                <p class="text-white-50 small">Tentukan batas maksimal jarak (dalam meter) agar kasir bisa absen. Standarnya adalah 5 meter sesuai instruksi.</p>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Radius Maksimal (Meter)</label>
                                    <input type="number" class="form-control" name="absensi_radius_meter" value="{{ $settings['absensi_radius_meter'] ?? '5' }}" min="1" max="1000">
                                    <div class="form-text">Semakin kecil angkanya, semakin ketat sistem absensinya. Disarankan minimal 5-15 meter karena akurasi GPS HP bisa sedikit meleset.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger w-100 fw-bold">Simpan Pengaturan Absensi</button>
                        </div>
                    </form>
                </div>
            </div>
        <!-- Kolom Pengaturan Geofencing GPS Meja -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center py-3 border-0" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock-fill me-2 text-danger"></i>Proteksi Lokasi Pemesanan Meja (Geofencing GPS)</h5>
                    <span class="badge {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'bg-success' : 'bg-secondary' }}" id="geofenceStatusBadge">
                        {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'Proteksi Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.geofence') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-2 text-white">Status Fitur</h6>
                                <p class="text-white-50 small mb-3">
                                    Cegah pesanan fiktif/iseng dari orang yang memfoto QR meja lalu memesan dari rumah. 
                                    Jika diaktifkan, pemesanan meja (Dine-In) hanya dapat diproses jika HP pelanggan terdeteksi berada di dalam radius kafe.
                                </p>

                                <!-- Pilihan Kartu Tombol ON / OFF yang Jelas -->
                                <label class="form-label fw-bold text-white mb-2">Pilih Status Proteksi GPS:</label>
                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="card p-3 text-center border h-100 geofence-card-select" id="cardGeofenceOff" onclick="window.setGeofenceMode('0')" style="cursor: pointer; transition: all 0.2s ease; background-color: #0e1217; user-select: none;">
                                            <input type="radio" name="geofence_active" value="0" class="d-none" id="radioGeofenceOff" onchange="window.setGeofenceMode('0')" {{ !isset($settings['geofence_active']) || $settings['geofence_active'] != '1' ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                <i class="bi bi-x-circle-fill fs-4 text-danger me-2"></i>
                                                <span class="fw-bold fs-6 text-white">OFF (NONAKTIF)</span>
                                            </div>
                                            <small class="text-white-50" style="font-size: 0.75rem;">Bebas pesan dari mana saja (Untuk Testing)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card p-3 text-center border h-100 geofence-card-select" id="cardGeofenceOn" onclick="window.setGeofenceMode('1')" style="cursor: pointer; transition: all 0.2s ease; background-color: #0e1217; user-select: none;">
                                            <input type="radio" name="geofence_active" value="1" class="d-none" id="radioGeofenceOn" onchange="window.setGeofenceMode('1')" {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'checked' : '' }}>
                                            <div class="d-flex align-items-center justify-content-center mb-1">
                                                <i class="bi bi-check-circle-fill fs-4 text-success me-2"></i>
                                                <span class="fw-bold fs-6 text-white">ON (AKTIF)</span>
                                            </div>
                                            <small class="text-white-50" style="font-size: 0.75rem;">Wajib berada di kafe untuk pesan meja</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Banner Keterangan Status Dinamis -->
                                <div id="geofenceStatusBanner" class="p-3 rounded-3 mb-3 border"></div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-white">Batas Radius Toleransi (Meter)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control text-white" name="geofence_radius" value="{{ $settings['geofence_radius'] ?? '100' }}" min="10" max="5000" style="background-color: #0e1217; border-color: #30363d;">
                                        <span class="input-group-text bg-dark text-white-50 border-secondary">Meter</span>
                                    </div>
                                    <div class="form-text text-white-50 small">Rekomendasi: <strong>50 - 100 meter</strong> (memberikan toleransi sedikit akurasi GPS HP).</div>
                                </div>
                            </div>

                            <div class="col-md-6 border-start ps-md-4">
                                <h6 class="fw-bold mb-2 text-white">Titik Koordinat Lokasi Kafe</h6>
                                <p class="text-white-50 small mb-3">Tentukan titik pusat GPS kafe Anda. Pelanggan harus berada di sekitar titik ini saat memesan di meja.</p>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-white">Latitude Kafe</label>
                                    <input type="text" class="form-control text-white" id="cafe_latitude" name="cafe_latitude" value="{{ $settings['cafe_latitude'] ?? ($settings['warung_latitude'] ?? '') }}" placeholder="Contoh: -6.2088" style="background-color: #0e1217; border-color: #30363d;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-white">Longitude Kafe</label>
                                    <input type="text" class="form-control text-white" id="cafe_longitude" name="cafe_longitude" value="{{ $settings['cafe_longitude'] ?? ($settings['warung_longitude'] ?? '') }}" placeholder="Contoh: 106.8456" style="background-color: #0e1217; border-color: #30363d;">
                                </div>

                                <div class="d-flex flex-wrap gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm fw-bold" onclick="getCafeLocation()">
                                        <i class="bi bi-crosshair me-1"></i> Ambil Lokasi Kafe Saya Saat Ini
                                    </button>
                                    @php
                                        $cLat = $settings['cafe_latitude'] ?? ($settings['warung_latitude'] ?? '');
                                        $cLng = $settings['cafe_longitude'] ?? ($settings['warung_longitude'] ?? '');
                                    @endphp
                                    @if(!empty($cLat) && !empty($cLng))
                                        <a href="https://www.google.com/maps?q={{ $cLat }},{{ $cLng }}" target="_blank" class="btn btn-outline-info btn-sm fw-bold">
                                            <i class="bi bi-geo-alt me-1"></i> Buka di Google Maps
                                        </a>
                                    @endif
                                </div>
                                <div class="form-text small" id="cafe-location-status"></div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-danger w-100 fw-bold">Simpan Pengaturan Proteksi Lokasi (Geofencing)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Pengaturan Kontak -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white py-3 border-0" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-person-lines-fill me-2 text-warning"></i>Pengaturan Halaman Kontak</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.kontak') }}" method="POST">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nomor WhatsApp</label>
                                <input type="text" class="form-control" name="kontak_wa" value="{{ $settings['kontak_wa'] ?? '+62 812-3456-7890' }}">
                                <div class="form-text">Format yang disarankan: +62 8xx-xxxx-xxxx</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Alamat Email</label>
                                <input type="email" class="form-control" name="kontak_email" value="{{ $settings['kontak_email'] ?? 'halo@mastercafe.com' }}">
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Daftar Sosial Media Tambahan</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSosmedRow()">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Sosial Media
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
                                    <label class="form-label small fw-bold">Platform</label>
                                    <select class="form-select text-white border-secondary  sosmed-platform" name="sosmed[platform][]" onchange="updateSosmedIcon(this)">
                                        <option value="Instagram" data-icon="bi-instagram" {{ $sosmed['platform'] == 'Instagram' ? 'selected' : '' }}>Instagram</option>
                                        <option value="TikTok" data-icon="bi-tiktok" {{ $sosmed['platform'] == 'TikTok' ? 'selected' : '' }}>TikTok</option>
                                        <option value="Facebook" data-icon="bi-facebook" {{ $sosmed['platform'] == 'Facebook' ? 'selected' : '' }}>Facebook</option>
                                        <option value="X/Twitter" data-icon="bi-twitter-x" {{ $sosmed['platform'] == 'X/Twitter' ? 'selected' : '' }}>X/Twitter</option>
                                        <option value="YouTube" data-icon="bi-youtube" {{ $sosmed['platform'] == 'YouTube' ? 'selected' : '' }}>YouTube</option>
                                        <option value="Lainnya" data-icon="bi-link-45deg" {{ $sosmed['platform'] == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Label / Username</label>
                                    <input type="text" class="form-control" name="sosmed[label][]" value="{{ $sosmed['label'] }}" required>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">URL Target</label>
                                    <input type="url" class="form-control" name="sosmed[url][]" value="{{ $sosmed['url'] }}" required>
                                </div>
                                <div class="col-md-1 text-end">
                                    <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="{{ $sosmed['icon'] ?? 'bi-link-45deg' }}">
                                    <button type="button" class="btn btn-outline-danger btn-icon" onclick="removeSosmedRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-warning w-100 fw-bold">Simpan Pengaturan Kontak</button>
                        </div>
                    </form>
                </div>
            </div>
        <!-- Kolom Backup & Pemeliharaan Database -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background-color: #161b22; border: 1px solid #21262d !important;">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shield-check me-2 text-info"></i>Cadangan & Pemeliharaan Database</h5>
                    <a href="{{ route('admin.backups.index') }}" class="btn btn-sm btn-info text-white fw-bold">
                        <i class="bi bi-folder2-open me-1"></i> Buka Manajemen File Backup
                    </a>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <p class="text-white-50 mb-1">
                                Backup database otomatis dijalankan oleh server setiap hari pukul <strong>03:00 pagi</strong>. Anda juga dapat membuat file cadangan secara instan kapan saja untuk mengamankan data transaksi dan master cafe.
                            </p>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> File cadangan tersimpan rapi dan dapat langsung diunduh ke laptop/HP Anda.</small>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <form action="{{ route('admin.backups.run') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-info fw-bold shadow-sm" onclick="this.disabled=true; this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Memproses...'; this.form.submit();">
                                    <i class="bi bi-play-circle me-1"></i> Backup Sekarang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
            cardOn.style.setProperty('border-color', '#198754', 'important');
            cardOn.style.setProperty('background-color', 'rgba(25, 135, 84, 0.18)', 'important');
            cardOn.style.setProperty('border-width', '2px', 'important');
            cardOn.style.setProperty('box-shadow', '0 0 15px rgba(25, 135, 84, 0.25)', 'important');
        }
        if (cardOff) {
            cardOff.style.setProperty('border-color', '#30363d', 'important');
            cardOff.style.setProperty('background-color', '#0e1217', 'important');
            cardOff.style.setProperty('border-width', '1px', 'important');
            cardOff.style.setProperty('box-shadow', 'none', 'important');
        }
        if (badge) {
            badge.className = 'badge bg-success';
            badge.textContent = 'Proteksi Aktif';
        }
        if (banner) {
            banner.style.setProperty('background-color', 'rgba(25, 135, 84, 0.12)', 'important');
            banner.style.setProperty('border-color', '#198754', 'important');
            banner.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-shield-check text-success fs-3 me-3"></i>
                    <div>
                        <div class="fw-bold text-success fs-6">Status Terpilih: ON (AKTIF)</div>
                        <div class="small text-white-50">Proteksi GPS aktif. Pelanggan yang memesan di meja (Dine-In) wajib terdeteksi berada di dalam radius kafe.</div>
                    </div>
                </div>
            `;
        }
    } else {
        if (radioOff) radioOff.checked = true;
        if (radioOn) radioOn.checked = false;
        if (cardOff) {
            cardOff.style.setProperty('border-color', '#dc3545', 'important');
            cardOff.style.setProperty('background-color', 'rgba(220, 53, 69, 0.18)', 'important');
            cardOff.style.setProperty('border-width', '2px', 'important');
            cardOff.style.setProperty('box-shadow', '0 0 15px rgba(220, 53, 69, 0.25)', 'important');
        }
        if (cardOn) {
            cardOn.style.setProperty('border-color', '#30363d', 'important');
            cardOn.style.setProperty('background-color', '#0e1217', 'important');
            cardOn.style.setProperty('border-width', '1px', 'important');
            cardOn.style.setProperty('box-shadow', 'none', 'important');
        }
        if (badge) {
            badge.className = 'badge bg-secondary';
            badge.textContent = 'Nonaktif';
        }
        if (banner) {
            banner.style.setProperty('background-color', 'rgba(220, 53, 69, 0.1)', 'important');
            banner.style.setProperty('border-color', '#dc3545', 'important');
            banner.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="bi bi-x-circle-fill text-danger fs-3 me-3"></i>
                    <div>
                        <div class="fw-bold text-danger fs-6">Status Terpilih: OFF (NONAKTIF / BEBAS)</div>
                        <div class="small text-white-50">Proteksi GPS saat ini dimatikan. Siapapun bebas memesan meja dari mana saja (Aman untuk fase pengetesan dari rumah).</div>
                    </div>
                </div>
            `;
        }
    }
};

window.initGeofenceAdmin = function() {
    const radioOn = document.getElementById('radioGeofenceOn');
    let isChecked = false;
    if (radioOn) {
        isChecked = radioOn.checked;
    } else {
        isChecked = {{ isset($settings['geofence_active']) && $settings['geofence_active'] == '1' ? 'true' : 'false' }};
    }
    if (typeof window.setGeofenceMode === 'function') {
        window.setGeofenceMode(isChecked ? '1' : '0');
    }
};

// Eksekusi inisialisasi langsung & pasang listener
window.initGeofenceAdmin();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initGeofenceAdmin);
}
window.addEventListener('admin:page-loaded', window.initGeofenceAdmin);

window.getCafeLocation = function() {
    const status = document.getElementById('cafe-location-status');
    const latInput = document.getElementById('cafe_latitude');
    const lngInput = document.getElementById('cafe_longitude');

    if (!navigator.geolocation) {
        if (status) status.innerHTML = "<span class='text-danger'>Geolocation tidak didukung oleh browser Anda.</span>";
        return;
    }

    if (status) status.innerHTML = "<span class='text-primary'><span class='spinner-border spinner-border-sm me-1'></span> Mendeteksi lokasi GPS Anda...</span>";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            if (latInput) latInput.value = position.coords.latitude;
            if (lngInput) lngInput.value = position.coords.longitude;
            if (status) status.innerHTML = "<span class='text-success'>Lokasi kafe berhasil didapatkan! (Akurasi: " + Math.round(position.coords.accuracy) + " meter)</span>";
        },
        (error) => {
            let msg = "";
            switch(error.code) {
                case error.PERMISSION_DENIED: msg = "Akses lokasi ditolak."; break;
                case error.POSITION_UNAVAILABLE: msg = "Informasi lokasi tidak tersedia."; break;
                case error.TIMEOUT: msg = "Waktu pencarian lokasi habis."; break;
                default: msg = "Terjadi kesalahan."; break;
            }
            if (status) status.innerHTML = "<span class='text-danger'>" + msg + "</span>";
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

window.getCurrentLocation = function() {
    const status = document.getElementById('location-status');
    const latInput = document.getElementById('warung_latitude');
    const lngInput = document.getElementById('warung_longitude');

    if (!navigator.geolocation) {
        if (status) status.innerHTML = "<span class='text-danger'>Geolocation tidak didukung oleh browser Anda.</span>";
        return;
    }

    if (status) status.innerHTML = "<span class='text-primary'>Mencari lokasi Anda...</span>";

    navigator.geolocation.getCurrentPosition(
        (position) => {
            if (latInput) latInput.value = position.coords.latitude;
            if (lngInput) lngInput.value = position.coords.longitude;
            if (status) status.innerHTML = "<span class='text-success'>Lokasi berhasil didapatkan! (Akurasi: " + Math.round(position.coords.accuracy) + " meter)</span>";
        },
        (error) => {
            let msg = "";
            switch(error.code) {
                case error.PERMISSION_DENIED: msg = "Anda menolak permintaan akses lokasi."; break;
                case error.POSITION_UNAVAILABLE: msg = "Informasi lokasi tidak tersedia."; break;
                case error.TIMEOUT: msg = "Waktu pencarian lokasi habis."; break;
                default: msg = "Terjadi kesalahan yang tidak diketahui."; break;
            }
            if (status) status.innerHTML = "<span class='text-danger'>" + msg + "</span>";
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

window.addSosmedRow = function() {
    const container = document.getElementById('sosmed-container');
    if (!container) return;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-3 align-items-end sosmed-row';
    row.innerHTML = `
        <div class="col-md-3">
            <label class="form-label small fw-bold">Platform</label>
            <select class="form-select text-white border-secondary sosmed-platform" name="sosmed[platform][]" onchange="window.updateSosmedIcon(this)">
                <option value="Instagram" data-icon="bi-instagram">Instagram</option>
                <option value="TikTok" data-icon="bi-tiktok">TikTok</option>
                <option value="Facebook" data-icon="bi-facebook">Facebook</option>
                <option value="X/Twitter" data-icon="bi-twitter-x">X/Twitter</option>
                <option value="YouTube" data-icon="bi-youtube">YouTube</option>
                <option value="Lainnya" data-icon="bi-link-45deg" selected>Lainnya</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold">Label / Username</label>
            <input type="text" class="form-control" name="sosmed[label][]" placeholder="@username" required>
        </div>
        <div class="col-md-5">
            <label class="form-label small fw-bold">URL Target</label>
            <input type="url" class="form-control" name="sosmed[url][]" placeholder="https://" required>
        </div>
        <div class="col-md-1 text-end">
            <input type="hidden" name="sosmed[icon][]" class="sosmed-icon-input" value="bi-link-45deg">
            <button type="button" class="btn btn-outline-danger btn-icon" onclick="window.removeSosmedRow(this)" title="Hapus"><i class="bi bi-trash"></i></button>
        </div>
    `;
    container.appendChild(row);
};

window.removeSosmedRow = function(button) {
    if (button && button.closest('.sosmed-row')) {
        button.closest('.sosmed-row').remove();
    }
};

window.updateSosmedIcon = function(select) {
    if (!select) return;
    const iconInput = select.closest('.sosmed-row')?.querySelector('.sosmed-icon-input');
    const selectedOption = select.options[select.selectedIndex];
    if (iconInput && selectedOption) {
        iconInput.value = selectedOption.getAttribute('data-icon');
    }
};
</script>
@endsection
