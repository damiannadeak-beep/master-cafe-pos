@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            @if(session('info'))
                <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4 p-3 d-flex align-items-center" style="background-color: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3) !important;">
                    <i class="bi bi-info-circle fs-4 me-3"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            <div class="card shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-body p-4 p-md-5">
                    <h2 class="fw-bold text-center mb-2 text-white" style="font-family: 'Outfit', sans-serif !important;">Pilih Jenis Pesanan</h2>
                    <p class="text-center text-secondary mb-5 small">Apakah Anda ingin memesan untuk dinikmati di tempat atau dibawa pulang?</p>
                    
                    <div class="row g-4">
                        <!-- Dine In Option: Membuka Modal Informasi Scan QR -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 text-center p-4 cursor-pointer hover-shadow rounded-4"
                                 data-bs-toggle="modal" data-bs-target="#modalDineInInfo"
                                 style="background-color: #0e1217; border: 2px solid #21262d !important; transition: all 0.3s ease; cursor: pointer;">
                                <div class="mb-3 d-flex justify-content-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                         style="width: 84px; height: 84px; background-color: rgba(178, 122, 77, 0.15); border: 1px solid rgba(178, 122, 77, 0.3); line-height: 0;">
                                        <i class="bi bi-qr-code-scan" style="font-size: 38px; color: #c08e5c !important;"></i>
                                    </div>
                                </div>
                                <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif !important;">Makan di Tempat</h5>
                                <p class="text-secondary small mb-0">Khusus scan stiker QR di meja kafe</p>
                            </div>
                        </div>

                        <!-- Takeaway Option -->
                        <div class="col-md-6">
                            <a href="{{ url('/konsumen/menu-takeaway') }}" class="text-decoration-none">
                                <div class="card h-100 border-0 text-center p-4 cursor-pointer hover-shadow rounded-4"
                                     style="background-color: #0e1217; border: 2px solid #21262d !important; transition: all 0.3s ease; cursor: pointer;">
                                    <div class="mb-3 d-flex justify-content-center">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                                             style="width: 84px; height: 84px; background-color: rgba(72, 187, 120, 0.15); border: 1px solid rgba(72, 187, 120, 0.3); line-height: 0;">
                                            <i class="bi bi-bag-check text-white" style="font-size: 38px; color: #48bb78 !important; line-height: 1; margin: 0;"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif !important;">Dibawa Pulang</h5>
                                    <p class="text-secondary small mb-0">Pesan dari mana saja untuk diambil</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Panduan Scan QR Dine-In -->
<div class="modal fade" id="modalDineInInfo" tabindex="-1" aria-labelledby="modalDineInInfoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 text-center p-4" style="background-color: #161b22; border: 1px solid #21262d !important; color: white;">
            <div class="modal-body p-3">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center rounded-circle" 
                     style="width: 80px; height: 80px; background-color: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3);">
                    <i class="bi bi-qr-code-scan fs-1 text-accent"></i>
                </div>
                <h4 class="fw-bold mb-2 text-white" id="modalDineInInfoLabel" style="font-family: 'Outfit', sans-serif;">Makan di Tempat (Dine-In)</h4>
                <p class="text-white-50 small mb-3">
                    Silakan <strong>pindai (scan) stiker QR di atas meja</strong> atau klik nomor meja Anda di bawah ini untuk memulai pesanan:
                </p>

                <div class="row g-2 justify-content-center mb-4" style="max-height: 200px; overflow-y: auto;">
                    @forelse($mejas ?? [] as $m)
                        <div class="col-4 col-sm-3">
                            <a href="{{ url('/konsumen/menu/' . $m->id) }}" class="btn btn-outline-warning w-100 rounded-3 py-2 fw-bold d-flex flex-column align-items-center justify-content-center shadow-sm" style="border-color: rgba(192, 142, 92, 0.4);">
                                <i class="bi bi-shop fs-5 mb-1" style="color: #c08e5c;"></i>
                                <span style="font-size: 0.8rem;">Meja {{ $m->nomor_meja }}</span>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-secondary small">
                            Scan stiker QR di atas meja kafe untuk membuka menu meja Anda.
                        </div>
                    @endforelse
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="{{ url('/konsumen/menu-takeaway') }}" class="btn fw-bold py-2 rounded-pill btn-touch" style="background: var(--gradient-bronze); color: white; border: none;">
                        <i class="bi bi-bag-check me-1"></i> Atau Pesan Bawa Pulang (Takeaway)
                    </a>
                </div>
                
                <button type="button" class="btn btn-link text-secondary text-decoration-none small" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease !important;
    }
    .hover-shadow:hover {
        box-shadow: 0 15px 30px rgba(0,0,0,0.4) !important;
        transform: translateY(-8px) !important;
        border-color: #c08e5c !important;
    }
</style>
@endsection
