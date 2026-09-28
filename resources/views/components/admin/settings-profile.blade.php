<!-- Kolom Profil Warung -->
<div class="col-lg-6">
    <div class="card border-0 shadow-sm h-100" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 32px; width: 32px; object-fit: cover;">
                <h5 class="mb-0 fw-bold text-white fs-6">Profil Warung & Struk</h5>
            </div>
            <span class="text-secondary small fw-medium">Identitas</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.settings.profile') }}" method="POST" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-profile')">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Nama Warung / Kafe</label>
                    <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="store_name" value="{{ $settings['store_name'] ?? 'Master Cafe' }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Alamat Lengkap</label>
                    <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="store_address" rows="2" required>{{ $settings['store_address'] ?? '' }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary small fw-medium mb-1">Nomor Telepon / WhatsApp</label>
                    <input type="text" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary small fw-medium mb-1">Pesan Bawah Struk (Footer)</label>
                    <textarea class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;" name="receipt_footer" rows="2" placeholder="Terima kasih atas kunjungan Anda!">{{ str_replace('\n', "\n", $settings['receipt_footer'] ?? '') }}</textarea>
                    <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Teks ini akan dicetak di bagian paling bawah struk kasir thermal.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-medium py-2 rounded-3">
                    <i class="bi bi-check2 me-1"></i> Simpan Profil Warung
                </button>
            </form>
        </div>
    </div>
</div>