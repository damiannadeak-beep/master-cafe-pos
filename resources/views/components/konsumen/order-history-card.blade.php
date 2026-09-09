@forelse($riwayat as $pesanan)
    <div class="card border-0 shadow-sm mb-4 rounded-4" style="background-color: #0e1217; border: 1px solid #21262d !important; overflow: hidden;">
        <div class="card-header d-flex justify-content-between align-items-center py-3 border-bottom rounded-top-4" style="background-color: #13171d; border-color: #21262d !important;">
            <div>
                <h6 class="fw-bold mb-0 text-white" style="font-family: 'Outfit', sans-serif !important;">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                <small class="text-secondary" style="font-family: 'Outfit', sans-serif !important;">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
            </div>
            <div>
                @if($pesanan->status === 'completed')
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-medium" style="font-family: 'Outfit', sans-serif !important;"><i class="bi bi-check2-all me-1"></i> Selesai</span>
                @else
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill fw-medium" style="font-family: 'Outfit', sans-serif !important;"><i class="bi bi-x-circle me-1"></i> Dibatalkan</span>
                @endif
            </div>
        </div>
        <div class="card-body" style="background-color: #0e1217; font-family: 'Outfit', sans-serif !important;">
            <p class="mb-1 text-secondary small">Ringkasan Pesanan:</p>
            <p class="mb-0 fw-medium text-white-50">
                @foreach($pesanan->detail_pesanan as $detail)
                    {{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Menu' }}@if(!$loop->last), @endif
                @endforeach
            </p>
            @if($pesanan->discount_amount > 0)
                <h6 class="fw-bold text-white mt-2 mb-0" style="font-family: 'Outfit', sans-serif !important;">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): <span class="text-secondary text-decoration-line-through fw-normal small">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span> <span style="color: #c08e5c;">Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</span></h6>
            @else
                <h6 class="fw-bold text-white mt-2 mb-0" style="font-family: 'Outfit', sans-serif !important;">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): <span style="color: #c08e5c;">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span></h6>
            @endif
        </div>
        
        @if($pesanan->status === 'completed')
            <div class="card-footer py-3 rounded-bottom-4" style="background-color: #13171d; border-top: 1px solid #21262d !important;">
                @if(!$pesanan->rating)
                    <form action="/konsumen/rating/store" method="POST" class="p-3 rounded-3" style="background-color: rgba(192, 142, 92, 0.06); border: 1px solid rgba(192, 142, 92, 0.2);">
                        @csrf
                        <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                        <label class="small fw-bold mb-2 d-block" style="color: #c08e5c; font-family: 'Outfit', sans-serif !important;"><i class="bi bi-star-fill text-warning me-1"></i> Berikan Penilaian untuk Pesanan Ini</label>
                        <div class="row g-2">
                            <div class="col-md-4">
                                <select name="rating" class="form-select text-white" style="background-color: #0e1217; border-color: #21262d; font-family: 'Outfit', sans-serif !important;" required>
                                    <option value="" class="text-secondary">Pilih Bintang...</option>
                                    <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
                                    <option value="4">⭐⭐⭐⭐ Bagus</option>
                                    <option value="3">⭐⭐⭐ Cukup</option>
                                    <option value="2">⭐⭐ Kurang</option>
                                    <option value="1">⭐ Kecewa</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="text" name="komentar" class="form-control text-white" style="background-color: #0e1217; border-color: #21262d; font-family: 'Outfit', sans-serif !important;" placeholder="Ulasan Anda (Opsional)">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-touch w-100 fw-bold" style="background: var(--gradient-bronze); color: white; border: none; font-family: 'Outfit', sans-serif !important;">Kirim</button>
                            </div>
                        </div>
                    </form>
                @else
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-3" style="background-color: #0e1217; border: 1px solid #21262d; font-family: 'Outfit', sans-serif !important;">
                        <div>
                            <small class="d-block fw-bold mb-1 text-secondary">Penilaian Anda:</small>
                            <div class="text-warning mb-1">
                                @for($i=1; $i<=5; $i++)
                                    <i class="bi bi-star{{ $i <= $pesanan->rating->rating ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                            <p class="mb-0 text-white small fst-italic">"{{ $pesanan->rating->komentar ?? 'Tidak ada ulasan tertulis' }}"</p>
                        </div>
                        @if($pesanan->rating->balasan_admin)
                            <div class="p-2 rounded small w-50" style="background-color: #161b22; border: 1px solid #21262d;">
                                <strong class="d-block mb-1" style="color: #c08e5c;"><i class="bi bi-reply-fill"></i> Balasan Admin:</strong>
                                <span class="text-white-50">{{ $pesanan->rating->balasan_admin }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>
@empty
    <div class="text-center py-5 px-3">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 76px; height: 76px; background: rgba(192, 142, 92, 0.08); border: 1px solid rgba(192, 142, 92, 0.25); box-shadow: 0 0 24px rgba(192, 142, 92, 0.1);">
            <i class="bi bi-clock-history" style="font-size: 2.2rem; color: #c08e5c;"></i>
        </div>
        <h5 class="fw-bold text-white mb-2" style="font-family: 'Outfit', sans-serif !important; font-size: 1.2rem; letter-spacing: -0.01em;">Belum Ada Riwayat</h5>
        <p class="text-secondary small mx-auto mb-4" style="max-width: 320px; font-family: 'Outfit', sans-serif !important; line-height: 1.55;">
            Riwayat pesanan Anda yang sudah selesai akan tampil di sini.
        </p>
        <a href="{{ url('/katalog') }}" class="btn btn-touch px-4 py-2 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-2" style="background: var(--gradient-bronze); color: #ffffff; border: none; font-family: 'Outfit', sans-serif !important; font-size: 0.9rem;">
            <i class="bi bi-book-half"></i> Jelajahi Menu
        </a>
    </div>
@endforelse