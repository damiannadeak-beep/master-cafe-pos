<!-- Kolom Pengaturan Pembayaran & WA Gateway -->
<div class="col-12 mt-4" id="section-payment">
    <div class="card border-0 shadow-sm" style="background-color: #161b22; border: 1px solid #21262d !important; border-radius: 12px; overflow: hidden;">
        <div class="card-header py-3 px-4 d-flex justify-content-between align-items-center" style="background-color: rgba(255, 255, 255, 0.02); border-bottom: 1px solid #21262d;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-credit-card" style="color: #c08e5c;"></i>
                <h5 class="mb-0 fw-bold text-white fs-6">Payment Gateway (Midtrans & QRIS) & Integrasi</h5>
            </div>
            <span class="text-secondary small fw-medium">Transaksi Otomatis</span>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('admin.settings.payment') }}" method="POST" enctype="multipart/form-data" onsubmit="sessionStorage.setItem('admin_settings_active_tab', 'tab-pane-payment')">
                @csrf
                <div class="row g-4">
                    <!-- Kolom Midtrans API Credentials -->
                    <div class="col-lg-6">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-shield-lock" style="color: #c08e5c;"></i>
                            <h6 class="fw-bold mb-0 text-white fs-6">Midtrans API Credentials</h6>
                        </div>
                        <p class="text-secondary small mb-3">Kredensial resmi dari Midtrans Dashboard untuk memproses pembayaran QRIS & Virtual Account otomatis.</p>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Midtrans Server Key</label>
                            <input type="text" class="form-control text-white" name="midtrans_server_key" value="{{ $settings['midtrans_server_key'] ?? '' }}" placeholder="SB-Mid-server-xxxxxxxxxxxx" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Dashboard Midtrans &gt; Settings &gt; Access Keys.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Midtrans Client Key</label>
                            <input type="text" class="form-control text-white" name="midtrans_client_key" value="{{ $settings['midtrans_client_key'] ?? '' }}" placeholder="SB-Mid-client-xxxxxxxxxxxx" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">Environment Mode</label>
                            <select class="form-select text-white" name="midtrans_is_production" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                                <option value="0" {{ ($settings['midtrans_is_production'] ?? '0') == '0' ? 'selected' : '' }}>🧪 Sandbox / Testing Mode</option>
                                <option value="1" {{ ($settings['midtrans_is_production'] ?? '0') == '1' ? 'selected' : '' }}>🟢 Production / Live Mode</option>
                            </select>
                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Gunakan Sandbox untuk testing, dan Production untuk operasional resmi.</div>
                        </div>
                    </div>

                    <!-- Kolom WA Gateway & AI -->
                    <div class="col-lg-6 border-start-lg ps-lg-4" style="border-color: #21262d !important;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-whatsapp" style="color: #34d399;"></i>
                            <h6 class="fw-bold mb-0 text-white fs-6">WhatsApp Gateway (Notifikasi Takeaway)</h6>
                        </div>
                        <p class="text-secondary small mb-3">API Token untuk mengirim notifikasi WA otomatis saat pesanan Takeaway siap diambil.</p>
                        
                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">WA Gateway API Token / Key</label>
                            <input type="text" class="form-control text-white" name="wa_gateway_api_key" value="{{ $settings['wa_gateway_api_key'] ?? '' }}" placeholder="Contoh: u9#xK8mPzL2..." style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                            <div class="text-secondary small mt-1" style="font-size: 0.75rem;">Token dari provider gateway (misal Fonnte.com / Wablas).</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-secondary small fw-medium mb-1">WA Gateway API URL</label>
                            <input type="text" class="form-control text-white" name="wa_gateway_url" value="{{ $settings['wa_gateway_url'] ?? 'https://api.fonnte.com/send' }}" placeholder="https://api.fonnte.com/send" style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                        </div>

                        <div class="pt-3 border-top" style="border-color: #21262d !important;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="fw-semibold text-white small">Verifikasi Otomatis Bukti Transfer</span>
                                <span class="text-secondary opacity-75 small" style="font-size: 0.75rem;">(Opsional)</span>
                            </div>
                            <div class="mb-2">
                                <label class="form-label text-secondary small fw-medium mb-1">API Key (Gemini AI)</label>
                                <input type="text" class="form-control text-white" name="gemini_api_key" value="{{ $settings['gemini_api_key'] ?? '' }}" placeholder="AIzaSyB..." style="background-color: #0e1217; border-color: #21262d; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top" style="border-color: #21262d !important;">
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2 px-4 py-2 fw-medium rounded-3">
                        <i class="bi bi-check2"></i> Simpan Pengaturan Gateway
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>