@if($voidedOrders->isEmpty())
    <div class="col-12 text-center py-5">
        <div class="card border-0 rounded-4 p-5 shadow-sm" style="background-color: rgba(22, 27, 34, 0.6); border: 1px dashed rgba(239, 68, 68, 0.25) !important;">
            <i class="bi bi-shield-x text-danger display-3 mb-3 opacity-50"></i>
            <h5 class="text-white fw-bold">Belum Ada Riwayat Pesanan Dibatalkan</h5>
            <p class="text-white-50 small mb-0">Pesanan yang dibatalkan oleh kasir (Void) atau pelanggan akan otomatis tercatat di sini secara transparan.</p>
        </div>
    </div>
@else
    @foreach($voidedOrders as $order)
        @php
            $mejaLabel = $order->meja ? 'Meja ' . $order->meja->nama_meja_atau_nomor : 'Takeaway';
            $custName = $order->customer_name;
            $alasan = $order->voidLog->alasan ?? 'Dibatalkan oleh Pelanggan / Sistem';
            $pelaku = $order->voidLog?->kasir?->name ?? ($order->kasir?->name ?? 'Pelanggan / Kasir');
            $voidTime = $order->deleted_at ?? $order->updated_at;
            $searchKey = strtolower($order->id . ' ' . $mejaLabel . ' ' . $custName . ' ' . ($order->guest_phone ?? '') . ' ' . $alasan . ' ' . $pelaku);
        @endphp
        <div class="col-12 col-md-6 col-xl-4 voided-order-item" data-search="{{ $searchKey }}">
            <div class="card h-100 rounded-4 shadow-sm border-0 position-relative overflow-hidden" 
                 style="background-color: #161b22; border: 1px solid rgba(239, 68, 68, 0.15) !important; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                
                <!-- Top Indicator Line: Merah Void -->
                <div class="position-absolute top-0 start-0 end-0" style="height: 3px; background: linear-gradient(90deg, #ef4444, #dc2626);"></div>

                <!-- Card Header -->
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-1.5">
                        <span class="fw-bold text-white fs-6">
                            <span class="text-danger">#</span>{{ $order->id }}
                        </span>
                        <span class="badge rounded-pill px-2 py-1 small fw-bold" style="background-color: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);">
                            <i class="bi bi-x-circle-fill me-1"></i> DIBATALKAN / VOID
                        </span>
                    </div>
                    <div>
                        @if($order->meja)
                            <span class="badge rounded-pill bg-dark border border-secondary text-warning px-2.5 py-1">
                                <i class="bi bi-geo-alt-fill me-1"></i> Meja {{ $order->meja->nama_meja_atau_nomor }}
                            </span>
                        @else
                            <span class="badge rounded-pill bg-dark border border-secondary text-info px-2.5 py-1">
                                <i class="bi bi-bag-check-fill me-1"></i> Takeaway
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Card Body -->
                <div class="card-body p-3 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Info Pemesan & Waktu Pembatalan -->
                        <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-15 flex-wrap gap-1 text-white-50 small">
                            <div>
                                <div class="text-white fw-semibold">
                                    <i class="bi bi-person me-1 text-secondary"></i> {{ $custName }}
                                </div>
                                @if(!empty($order->guest_phone))
                                    <div style="font-size: 0.72rem;" class="text-white-50">
                                        <i class="bi bi-telephone me-1"></i> {{ $order->guest_phone }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="text-white-50" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock-history me-1 text-danger"></i> {{ $voidTime ? $voidTime->format('H:i') : '-' }} WIB
                                </div>
                                <div style="font-size: 0.7rem;" class="text-secondary">
                                    {{ $voidTime ? $voidTime->diffForHumans() : '' }}
                                </div>
                            </div>
                        </div>

                        <!-- Box Alasan & Pelaku Void -->
                        <div class="rounded-3 p-3 mb-3" style="background-color: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.25);">
                            <div class="d-flex align-items-start gap-2 mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-danger flex-shrink-0" style="font-size: 0.95rem; margin-top: 2px;"></i>
                                <div class="w-100">
                                    <div class="text-white-50 small mb-0.5" style="font-size: 0.75rem;">Alasan Void / Batal:</div>
                                    <div class="fw-semibold" style="font-size: 0.84rem; color: #f87171; line-height: 1.4;">
                                        {{ $alasan }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 mt-2 border-top border-danger border-opacity-25" style="font-size: 0.75rem;">
                                <span class="text-white-50 d-inline-flex align-items-center">
                                    <i class="bi bi-person-badge me-1.5 text-danger opacity-75"></i> Petugas / Pemohon:
                                </span>
                                <span class="text-white fw-semibold">
                                    {{ $pelaku }}
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Item Pesanan yang Dibatalkan -->
                        <div class="rounded-3 p-3 mb-3" style="background-color: rgba(22, 27, 34, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); max-height: 180px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1.5 border-bottom border-secondary border-opacity-25" style="padding-top: 2px;">
                                <span class="text-white-50 fw-bold text-uppercase d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px; line-height: 1.5;">
                                    ITEM DIBATALKAN ({{ $order->detail_pesanan->sum('jumlah') }} ITEM)
                                </span>
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 py-0.5 px-1.5" style="font-size: 0.65rem;">
                                    Stok Dikembalikan
                                </span>
                            </div>

                            <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                @forelse($order->detail_pesanan as $detail)
                                    <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10 text-white-50">
                                        <div class="text-truncate pe-2">
                                            <span class="text-secondary fw-medium">{{ $detail->jumlah }}x</span> 
                                            <span class="text-white-50 text-decoration-line-through">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                            @if($detail->catatan)
                                                <small class="d-block text-white-50 opacity-50 fst-italic" style="font-size: 0.7rem;">&bull; {{ $detail->catatan }}</small>
                                            @endif
                                        </div>
                                        <span class="text-white-50 text-decoration-line-through text-nowrap" style="font-size: 0.78rem;">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="text-white-50 small py-1 fst-italic">Tidak ada rincian item</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Footer: Total Nilai Void & Info -->
                    <div class="pt-2 border-top border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white-50 small">Total Nilai Void:</span>
                            <span class="text-danger fw-bold fs-6">
                                Rp {{ number_format($order->total, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="p-2 rounded-3 text-center" style="background-color: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                            <span class="text-white-50 small" style="font-size: 0.72rem;">
                                <i class="bi bi-shield-check text-success me-1"></i> Pesanan resmi dibatalkan & transaksi dihentikan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
