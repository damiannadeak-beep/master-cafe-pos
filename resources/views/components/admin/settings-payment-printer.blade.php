<!-- Kolom Pengaturan Pembayaran -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-warning"></i>Pengaturan Payment Gateway (Midtrans & QRIS)</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Kolom Midtrans API Credentials -->
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold mb-3 text-warning"><i class="bi bi-shield-lock me-2"></i>Midtrans Payment Gateway Credentials</h6>
                                <p class="text-white-50 small">Kredensial resmi dari Midtrans Dashboard untuk memproses pembayaran QRIS Instant & Virtual Account secara otomatis.</p>
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Midtrans Server Key</label>
                                    <input type="text" class="form-control text-white" name="midtrans_server_key" value="{{ $settings['midtrans_server_key'] ?? '' }}" placeholder="SB-Mid-server-xxxxxxxxxxxx" style="background-color: #0e1217; border-color: #30363d;">
                                    <div class="form-text text-muted">Dapatkan dari Dashboard Midtrans > Settings > Access Keys.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Midtrans Client Key</label>
                                    <input type="text" class="form-control text-white" name="midtrans_client_key" value="{{ $settings['midtrans_client_key'] ?? '' }}" placeholder="SB-Mid-client-xxxxxxxxxxxx" style="background-color: #0e1217; border-color: #30363d;">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Environment Mode</label>
                                    <select class="form-select text-white" name="midtrans_is_production" style="background-color: #0e1217; border-color: #30363d;">
                                        <option value="0" {{ ($settings['midtrans_is_production'] ?? '0') == '0' ? 'selected' : '' }}>🧪 Sandbox / Testing Mode</option>
                                        <option value="1" {{ ($settings['midtrans_is_production'] ?? '0') == '1' ? 'selected' : '' }}>🟢 Production / Live Mode</option>
                                    </select>
                                    <div class="form-text text-muted">Gunakan Sandbox saat uji coba dan Production saat cafe resmi beroperasi.</div>
                                </div>
                            </div>