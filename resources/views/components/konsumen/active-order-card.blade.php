@forelse($pesananAktif as $pesanan)
                                <div class="card border-0 shadow-sm mb-4 rounded-4">
                                    <div class="card-header  d-flex justify-content-between align-items-center py-3 border-bottom-0 rounded-top-4">
                                        <div>
                                            <h6 class="fw-bold mb-0 text-white">Order #{{ $pesanan->created_at->format('YmdHi') }}</h6>
                                            <small class="text-secondary">{{ $pesanan->created_at->translatedFormat('l, d F Y - H:i') }} WIB</small>
                                        </div>
                                        <div>
                                            @if($pesanan->status === 'pending')
                                                <span class="badge bg-warning text-white px-3 py-2 rounded-pill"><i class="bi bi-hourglass-split me-1"></i> Menunggu Diproses</span>
                                            @elseif($pesanan->status === 'processing')
                                                <span class="badge bg-primary px-3 py-2 rounded-pill"><i class="bi bi-fire me-1"></i> Sedang Dimasak</span>
                                            @elseif($pesanan->status === 'completed')
                                                <span class="badge bg-success px-3 py-2 rounded-pill"><i class="bi bi-check2-all me-1"></i> Selesai Dimasak</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-body  bg-opacity-50">
                                        <div class="d-flex mb-3">
                                            <div class="me-3 text-primary"><i class="bi bi-geo-alt-fill"></i></div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $pesanan->tipe_pesanan == 'takeaway' ? 'Bungkus / Takeaway' : 'Makan di Tempat' }}</h6>
                                                <small class="text-secondary">{{ $pesanan->meja->nama_meja_atau_nomor ?? '-' }}</small>
                                            </div>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-borderless table-sm mb-0">
                                                <tbody>
                                                    @foreach($pesanan->detail_pesanan as $detail)
                                                        <tr>
                                                            <td class="text-secondary" style="width: 30px;">{{ $detail->jumlah }}x</td>
                                                            <td class="fw-medium">{{ $detail->menu->nama_menu ?? 'Menu tidak ditemukan' }}</td>
                                                            <td class="text-end text-secondary">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    @php
                                        $hasAction = $pesanan->pembayaran->status == 'unpaid' || ($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja);
                                    @endphp
                                    <div class="card-footer  py-3 rounded-bottom-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                @if($pesanan->pembayaran->status == 'unpaid')
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i> Belum Lunas</span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <p class="small text-secondary mb-0">Total Tagihan ({{ $pesanan->detail_pesanan->sum('jumlah') }} Item)</p>
                                                @if($pesanan->discount_amount > 0)
                                                    <p class="small text-danger mb-0 text-decoration-line-through">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</p>
                                                    <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($pesanan->total - $pesanan->discount_amount, 0, ',', '.') }}</h5>
                                                @else
                                                    <h5 class="fw-bold text-primary mb-0">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</h5>
                                                @endif
                                            </div>
                                        </div>
                                        @if($hasAction)
                                            <div class="d-flex justify-content-end gap-2 flex-wrap border-top pt-3 mt-3">
                                                @if($pesanan->pembayaran->status == 'unpaid')
                                                    <a href="/konsumen/checkout/{{ $pesanan->id }}" class="btn btn-success fw-bold px-4 rounded-pill btn-touch">
                                                        Pilih Pembayaran <i class="bi bi-arrow-right ms-1"></i>
                                                    </a>
                                                @endif
                                                @if($pesanan->tipe_pesanan === 'dine_in' && $pesanan->id_meja)
                                                    <button onclick="callBell({{ $pesanan->id_meja }})" class="btn btn-outline-danger fw-bold px-4 rounded-pill btn-touch">
                                                        <i class="bi bi-bell-fill me-1"></i> Panggil Pelayan
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center  p-5 rounded-4 shadow-sm">
                                    <div class="d-inline-block  text-secondary p-4 rounded-circle mb-3">
                                        <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 64px; width: 64px; object-fit: cover; margin-bottom: 1rem;">
                                    </div>
                                    <h5 class="fw-bold">Belum Ada Pesanan</h5>
                                    <p class="text-secondary mb-4">Anda belum memesan makanan apa pun. Yuk lihat menu kami!</p>
                                    <a href="/katalog" class="btn btn-auth-primary btn-touch" style="background: var(--gradient-bronze); color: white; border: none;" rounded-pill px-4 py-2 fw-bold">Lihat Katalog Menu</a>
                                </div>
                            @endforelse