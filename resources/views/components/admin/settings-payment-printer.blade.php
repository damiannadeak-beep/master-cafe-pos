<!-- Kolom Pengaturan Pembayaran -->
        <div class="col-12 mt-4">
            <div class="card admin-card border-0 shadow-sm">
                <div class="card-header text-white" style="background-color: #161b22; border: 1px solid #21262d !important;" py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-credit-card me-2 text-success"></i>Pengaturan Pembayaran & QRIS</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.payment') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <!-- Kolom Upload QRIS -->
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold mb-3">QRIS Statis Warung</h6>
                                <p class="text-white-50 small">Unggah gambar QRIS Statis agar kasir bisa menampilkannya di layar saat pelanggan ingin membayar menggunakan QRIS (E-Wallet/M-Banking).</p>
                                
                                <div class="mb-3">
                                    @if(isset($settings['qris_image']) && $settings['qris_image'])
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/'.$settings['qris_image']) }}" alt="QRIS Warung" class="img-thumbnail" style="max-height: 200px;">
                                        </div>
                                    @endif
                                    <label class="form-label fw-bold">Unggah Barcode QRIS</label>
                                    <input class="form-control" type="file" name="qris_image" accept="image/jpeg, image/png, image/jpg">
                                    <div class="form-text">Maksimal 2MB. Format: JPG, PNG.</div>
                                </div>
                            </div>