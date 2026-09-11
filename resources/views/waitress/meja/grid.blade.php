<!-- Ringkasan Statistik Singkat -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: #14171c; border: 1px solid #21262d !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 me-3 text-primary" style="background-color: rgba(59, 130, 246, 0.12); font-size: 1.5rem;">
                    <i class="bi bi-grid"></i>
                </div>
                <div>
                    <div class="text-white-50 small">Total Meja Terdaftar</div>
                    <h4 class="fw-bold text-white mb-0">{{ $totalMeja }} Meja</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: #14171c; border: 1px solid #21262d !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 me-3" style="background-color: rgba(245, 158, 11, 0.12); color: #f59e0b; font-size: 1.5rem;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="text-white-50 small">Meja Sedang Ada Pesanan</div>
                    <h4 class="fw-bold text-white mb-0">{{ $mejaAdaPesanan }} Meja</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 rounded-4 p-3 shadow-sm" style="background-color: #14171c; border: 1px solid #21262d !important;">
            <div class="d-flex align-items-center">
                <div class="rounded-3 p-3 me-3" style="background-color: rgba(16, 185, 129, 0.12); color: #10b981; font-size: 1.5rem;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="text-white-50 small">Meja Tersedia / Bersih</div>
                    <h4 class="fw-bold text-white mb-0">{{ $mejaKosong }} Meja</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Grid Kartu Meja -->
<div class="row g-4">
    @forelse($mejas as $meja)
        @php
            $activeOrder = $meja->pesanan->first();
            $callNotif = isset($activeCallsMap) && isset($activeCallsMap[$meja->id]) ? $activeCallsMap[$meja->id] : null;
        @endphp
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift" 
                 style="background-color: #14171c; border: 1px solid {{ $callNotif ? '#dc3545' : ($activeOrder ? 'rgba(245, 158, 11, 0.4)' : '#21262d') }} !important;">
                
                @if($callNotif)
                    <div class="bg-danger text-white text-center py-1 fw-bold small pulse-animation">
                        <i class="bi bi-bell-fill me-1"></i> MEMANGGIL PELAYAN
                    </div>
                @elseif($activeOrder)
                    <div class="position-absolute top-0 start-0 end-0" style="height: 4px; background: linear-gradient(90deg, #f59e0b, #d97706);"></div>
                @endif

                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Header Meja -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif;">
                                    <i class="bi bi-geo-alt-fill text-accent me-1"></i> {{ $meja->nama_meja_atau_nomor }}
                                </h5>
                                <small class="text-white-50">{{ $meja->keterangan ?? 'Meja Pelanggan' }}</small>
                            </div>
                            @if($callNotif)
                                <span class="badge bg-danger text-white rounded-pill px-2 py-1 small pulse-animation">
                                    <i class="bi bi-bell-fill"></i> Panggilan
                                </span>
                            @elseif($activeOrder)
                                <span class="badge rounded-pill px-2 py-1 small" style="background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <i class="bi bi-dot"></i> Ada Pesanan
                                </span>
                            @else
                                <span class="badge rounded-pill px-2 py-1 small" style="background-color: rgba(16, 185, 129, 0.12); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <i class="bi bi-check2"></i> Tersedia
                                </span>
                            @endif
                        </div>

                        <hr style="border-color: #21262d;" class="my-3">

                        <!-- Status Konten -->
                        @if($activeOrder)
                            <div class="rounded-3 p-3 mb-3" style="background-color: rgba(22, 27, 34, 0.8); border: 1px solid #21262d;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-white small">Order #{{ $activeOrder->id }}</span>
                                    @if($activeOrder->status === 'pending')
                                        <span class="badge bg-warning text-dark px-2 py-1" style="font-size: 0.7rem;">Menunggu</span>
                                    @elseif($activeOrder->status === 'processing')
                                        <span class="badge bg-info text-dark px-2 py-1" style="font-size: 0.7rem;">Dimasak</span>
                                    @elseif($activeOrder->status === 'completed' || $activeOrder->status === 'ready')
                                        <span class="badge bg-success px-2 py-1" style="font-size: 0.7rem;">Siap Antar</span>
                                    @endif
                                </div>
                                <div class="text-white-50 small mb-1">
                                    <i class="bi bi-person me-1"></i> {{ $activeOrder->konsumen->name ?? 'Tamu Langsung' }}
                                </div>
                                <div class="text-white-50 small mb-2">
                                    <i class="bi bi-bag me-1"></i> {{ $activeOrder->detail_pesanan->count() }} Item &bull; <strong class="text-accent">Rp {{ number_format($activeOrder->total, 0, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top border-secondary border-opacity-25" style="font-size: 0.75rem;">
                                    <span class="text-white-50">Pembayaran:</span>
                                    @if(optional($activeOrder->pembayaran)->status === 'paid')
                                        <span class="text-success fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Lunas</span>
                                    @else
                                        <span class="text-warning fw-bold"><i class="bi bi-clock me-1"></i>Belum Bayar</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="text-center py-4 text-white-50">
                                <i class="bi bi-cup-hot fs-2 mb-2 d-block opacity-25"></i>
                                <p class="small mb-0">Tidak ada pesanan aktif saat ini</p>
                                <span class="text-secondary" style="font-size: 0.75rem;">Siap menerima tamu baru</span>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Kartu -->
                    <div class="pt-2 d-flex flex-column gap-2">
                        @if($callNotif)
                            <button onclick="dismissCallBellNotif({{ $callNotif->id }})" class="btn btn-warning w-100 rounded-3 fw-bold d-inline-flex align-items-center justify-content-center btn-touch shadow-sm" style="height: 40px; min-height: 40px; font-size: 0.875rem;">
                                <i class="bi bi-check2-circle me-2" style="font-size: 1.1rem;"></i> Tanggapi Panggilan
                            </button>
                        @endif

                        @if($activeOrder)
                            <a href="{{ route('kasir.pesanan_aktif') }}" class="btn w-100 rounded-3 fw-bold d-inline-flex align-items-center justify-content-center btn-touch shadow-sm" style="height: 40px; min-height: 40px; font-size: 0.875rem; border: 1px solid #c08e5c; color: #c08e5c; background: rgba(192, 142, 92, 0.1); transition: all 0.2s ease;">
                                <i class="bi bi-eye me-2" style="font-size: 1.1rem;"></i> Buka di Pesanan Aktif
                            </a>
                        @elseif(!$callNotif)
                            <div class="text-center">
                                <span class="text-secondary small" style="font-size: 0.75rem;"><i class="bi bi-shield-check me-1"></i>Otomatis terisi saat ada pesanan</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning border-0 shadow-sm rounded-4 text-center py-5">
                <i class="bi bi-info-circle fs-1"></i>
                <h5 class="mt-3 fw-bold">Belum Ada Meja</h5>
                <p class="mb-0">Data meja belum ditambahkan oleh Admin.</p>
            </div>
        </div>
    @endforelse
</div>
