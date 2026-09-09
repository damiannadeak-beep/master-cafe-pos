@forelse($pesananAktif as $pesanan)
    <div class="card border-0 shadow-sm mb-4 rounded-4" style="background-color: #0e1217; border: 1px solid #21262d !important; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center py-3 border-bottom rounded-top-4" style="background-color: #13171d; border-color: #21262d !important;">
            <div>
                <h6 class="fw-bold mb-0 text-white" style="font-family: 'Outfit', sans-serif !important;">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                <small class="text-secondary" style="font-family: 'Outfit', sans-serif !important;">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
            </div>
            <div>
                @if($pesanan->status === 'pending')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-medium" style="font-family: 'Outfit', sans-serif !important;"><i class="bi bi-hourglass-split me-1"></i> Menunggu Diproses</span>
                @elseif($pesanan->status === 'processing')
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-medium" style="font-family: 'Outfit', sans-serif !important;"><i class="bi bi-fire me-1"></i> Sedang Dimasak</span>
                @elseif($pesanan->status === 'completed')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-medium" style="font-family: 'Outfit', sans-serif !important;"><i class="bi bi-check2-all me-1"></i> Selesai Dimasak</span>
                @endif
            </div>
        </div>
        <div class="card-body" style="background-color: #0e1217; font-family: 'Outfit', sans-serif !important;">
            <div class="d-flex align-items-center mb-3">
                <div class="me-2" style="color: #c08e5c;"><i class="bi bi-geo-alt-fill fs-5"></i></div>
                <div>
                    <h6 class="mb-0 fw-bold text-white" style="font-family: 'Outfit', sans-serif !important;">{{ $pesanan->tipe_pesanan == 'takeaway' ? 'Bungkus / Takeaway' : 'Makan di Tempat' }}</h6>
                    <small class="text-secondary">Meja: {{ $pesanan->meja->nama_meja_atau_nomor ?? '-' }}</small>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        @foreach($pesanan->detail_pesanan as $detail)
                            <tr>
                                <td class="text-secondary" style="width: 35px;">{{ $detail->jumlah }}x</td>
                                <td class="fw-medium text-white-50">{{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                                <td class="text-end text-white">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @php
            $hasAction = $pesanan->pembayaran->status == 'unpaid' || ($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja);
        @endphp
        <div class="card-footer py-3 rounded-bottom-4" style="background-color: #13171d; border-top: 1px solid #21262d !important; font-family: 'Outfit', sans-serif !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    @if($pesanan->pembayaran->status == 'unpaid')
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1.5 rounded-pill"><i class="bi bi-x-circle me-1"></i> Belum Lunas</span>
                    @else
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5 rounded-pill"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                    @endif
                </div>
                <div class="text-end">
                    <p class="small text-secondary mb-0">Total Tagihan ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item)</p>
                    @if($pesanan->discount_amount > 0)
                        <p class="small text-danger mb-0 text-decoration-line-through">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                        <h5 class="fw-bold mb-0" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;">Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</h5>
                    @else
                        <h5 class="fw-bold mb-0" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h5>
                    @endif
                </div>
            </div>
            @if($hasAction)
                <div class="d-flex justify-content-end gap-2 flex-wrap border-top pt-3 mt-3" style="border-color: #21262d !important;">
                    @if($pesanan->pembayaran->status == 'unpaid')
                        <a href="/konsumen/checkout/{{ $pesanan->id }}" class="btn fw-semibold px-4 rounded-pill btn-touch" style="background: var(--gradient-bronze); color: white; border: none; font-family: 'Outfit', sans-serif !important; font-size: 0.875rem;">
                            Pilih Pembayaran <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    @endif
                    @if($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja)
                        <button onclick="callBell({{ $pesanan->id_meja }})" class="btn btn-outline-danger fw-semibold px-4 rounded-pill btn-touch" style="font-family: 'Outfit', sans-serif !important; font-size: 0.875rem;">
                            <i class="bi bi-bell-fill me-1"></i> Panggil Pelayan
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
@empty
    <div class="text-center py-5 px-3">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 76px; height: 76px; background: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.25); box-shadow: 0 0 24px rgba(192, 142, 92, 0.1);">
            <i class="bi bi-basket" style="font-size: 2.2rem; color: #c08e5c;"></i>
        </div>
        <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif !important; font-size: 1.2rem; letter-spacing: -0.01em;">Belum Ada Pesanan Aktif</h5>
        <p class="text-secondary small mx-auto mb-4" style="max-width: 320px; font-family: 'Outfit', sans-serif !important; line-height: 1.55;">
            Anda belum memiliki pesanan yang sedang diproses. Yuk pilih menu favorit Anda!
        </p>
        <a href="{{ url('/katalog') }}" class="btn btn-touch px-4 py-2 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-2" style="background: var(--gradient-bronze); color: #ffffff; border: none; font-family: 'Outfit', sans-serif !important; font-size: 0.9rem;">
            <i class="bi bi-book-half"></i> Pesan Sekarang
        </a>
    </div>
@endforelse