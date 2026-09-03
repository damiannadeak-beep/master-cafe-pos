@forelse($riwayat as $pesanan)
                                <div class="card border-0 shadow-sm mb-4 rounded-4">
                                    <div class="card-header  d-flex justify-content-between align-items-center py-3 border-bottom-0 rounded-top-4">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-white">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                                            <small class="text-secondary">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
                                        </div>
                                        <div>
                                            @if($pesanan->status === 'completed')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill"><i class="bi bi-check2-all me-1"></i> Selesai</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill"><i class="bi bi-x-circle me-1"></i> Dibatalkan</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body  bg-opacity-50 border-top border-bottom">
                                        <p class="mb-1 text-secondary small">Ringkasan Pesanan:</p>
                                        <p class="mb-0 fw-medium">
                                            @foreach($pesanan->detail_pesanan as $detail)
                                                {{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Menu' }}@if(!$loop->last), @endif
                                            @endforeach
                                        </p>
                                        @if($pesanan->discount_amount > 0)
                                            <h6 class="fw-bold text-white mt-2 mb-0">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): <span class="text-secondary text-decoration-line-through fw-normal small">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span> Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</h6>
                                        @else
                                            <h6 class="fw-bold text-white mt-2 mb-0">Total ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item): Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h6>
                                        @endif
                                    </div>
                                    
                                    @if($pesanan->status === 'completed')
                                        <div class="card-footer  py-3 rounded-bottom-4">
                                            @if(!$pesanan->rating)
                                                <form action="/konsumen/rating/store" method="POST" class="bg-primary bg-opacity-10 p-3 rounded-3">
                                                    @csrf
                                                    <input type="hidden" name="id_pesanan" value="{{ $pesanan->id }}">
                                                    <label class="small fw-bold text-primary mb-2 d-block"><i class="bi bi-star-fill text-warning me-1"></i> Berikan Penilaian untuk Pesanan Ini</label>
                                                    <div class="row g-2">
                                                        <div class="col-md-4">
                                                            <select name="rating" class="form-select border-primary  text-white" required>
                                                                <option value="" class="text-secondary">Pilih Bintang...</option>
                                                                <option value="5">⭐⭐⭐⭐⭐ Sangat Bagus</option>
                                                                <option value="4">⭐⭐⭐⭐ Bagus</option>
                                                                <option value="3">⭐⭐⭐ Cukup</option>
                                                                <option value="2">⭐⭐ Kurang</option>
                                                                <option value="1">⭐ Kecewa</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="text" name="komentar" class="form-control border-primary " placeholder="Ulasan Anda (Opsional)">
                                                        </div>
                                                        <div class="col-md-2">
                                                            <button type="submit" class="btn btn-auth-primary btn-touch" style="background: var(--gradient-bronze); color: white; border: none;" w-100 fw-bold">Kirim</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            @else
                                                <div class="d-flex justify-content-between align-items-center  p-3 rounded-3 border">
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
                                                        <div class="border p-2 rounded small w-50">
                                                            <strong class="text-primary d-block mb-1"><i class="bi bi-reply-fill"></i> Balasan Admin:</strong>
                                                            {{ $pesanan->rating->balasan_admin }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-center  p-5 rounded-4 shadow-sm">
                                    <div class="d-inline-block  text-secondary p-4 rounded-circle mb-3">
                                        <i class="bi bi-clock-history fs-1"></i>
                                    </div>
                                    <h5 class="fw-bold">Belum Ada Riwayat</h5>
                                    <p class="text-secondary mb-0">Riwayat pesanan Anda yang sudah selesai akan tampil di sini.</p>
                                </div>
                            @endforelse