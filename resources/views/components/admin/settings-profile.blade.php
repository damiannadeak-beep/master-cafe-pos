<!-- Kolom Profil Warung -->
        <div class="col-lg-6">
            <div class="card admin-card border-0 shadow-sm h-100">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 48px; width: 48px; object-fit: cover; margin-bottom: 8px;">Profil Warung & Struk</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.profile') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Warung</label>
                            <input type="text" class="form-control" name="store_name" value="{{ $settings['store_name'] ?? 'Master Cafe' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat Lengkap</label>
                            <textarea class="form-control" name="store_address" rows="2" required>{{ $settings['store_address'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nomor Telepon / WhatsApp</label>
                            <input type="text" class="form-control" name="store_phone" value="{{ $settings['store_phone'] ?? '' }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Pesan Bawah Struk (Footer)</label>
                            <textarea class="form-control" name="receipt_footer" rows="2" placeholder="Terima kasih atas kunjungan Anda!">{{ str_replace('\n', "\n", $settings['receipt_footer'] ?? '') }}</textarea>
                            <div class="form-text">Bisa menggunakan beberapa baris. Teks ini akan dicetak di bagian paling bawah struk kasir.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Profil Warung</button>
                    </form>
                </div>
            </div>
        </div>