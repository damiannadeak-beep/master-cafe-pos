<style>
    .void-card-luxury {
        background-color: #141820 !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.45);
        padding: 1.25rem !important;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .void-card-luxury:hover {
        transform: translateY(-2px);
        border-color: rgba(239, 68, 68, 0.35) !important;
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.55);
    }
    .void-order-items::-webkit-scrollbar {
        width: 4px;
    }
    .void-order-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .void-order-items::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 4px;
    }
    .void-order-items::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.25);
    }
</style>

@if($voidedOrders->isEmpty())
    <div class="col-12 text-center py-5">
        <div class="card border-0 rounded-4 p-5 shadow-sm" style="background-color: rgba(20, 24, 32, 0.6); border: 1px dashed rgba(239, 68, 68, 0.25) !important;">
            <i class="bi bi-shield-x text-danger display-4 mb-3 opacity-40"></i>
            <h5 class="text-white fw-semibold mb-1">Belum Ada Riwayat Pesanan Dibatalkan</h5>
            <p class="text-white-50 small mb-0">Pesanan yang dibatalkan oleh kasir (Void) atau pelanggan akan otomatis tercatat di sini secara transparan.</p>
        </div>
    </div>
@else
    @foreach($voidedOrders as $order)
        @php
            $rawMejaName = trim((string)($order->meja->nama_meja_atau_nomor ?? ''));
            $labelMeja = $order->meja ? (preg_match('/^meja\b/i', $rawMejaName) ? $rawMejaName : 'Meja ' . $rawMejaName) : 'Takeaway';
            $custName = $order->customer_name;
            $alasan = $order->voidLog->alasan ?? 'Dibatalkan oleh Pelanggan / Sistem';
            $pelaku = $order->voidLog?->kasir?->name ?? ($order->kasir?->name ?? 'Pelanggan / Kasir');
            $voidTime = $order->deleted_at ?? $order->updated_at;
            $searchKey = strtolower($order->id . ' ' . $labelMeja . ' ' . $custName . ' ' . ($order->guest_phone ?? '') . ' ' . $alasan . ' ' . $pelaku);
        @endphp
        <div class="col-12 col-md-6 col-xl-4 voided-order-item" data-search="{{ $searchKey }}">
            <div class="card h-100 border-0 position-relative void-card-luxury d-flex flex-column">
                
                <!-- 1. Card Header: Bersih & Terpadu -->
                <div class="d-flex justify-content-between align-items-center pb-2.5 mb-2.5" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                            <span class="text-danger fw-normal">#</span>{{ $order->id }}
                        </span>
                        <span class="badge rounded-pill px-2.5 py-1 fw-semibold" 
                              style="background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); font-size: 0.7rem; letter-spacing: 0.03em;">
                            DIBATALKAN / VOID
                        </span>
                    </div>

                    <div>
                        @if($order->meja)
                            <span class="badge rounded-pill px-2.5 py-1 fw-medium" style="background: rgba(192, 142, 92, 0.12); color: #d4a373; border: 1px solid rgba(192, 142, 92, 0.25); font-size: 0.72rem;">
                                <i class="bi bi-geo-alt me-1"></i> {{ $labelMeja }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-2.5 py-1 fw-medium" style="background: rgba(255, 255, 255, 0.05); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.08); font-size: 0.72rem;">
                                <i class="bi bi-bag me-1"></i> Takeaway
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 2. Customer & Metadata Info (Aliran Alami, Tanpa Kolom Kaku) -->
                <div class="mb-2.5">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1.5">
                        <div>
                            <div class="text-white fw-semibold" style="font-size: 0.95rem; line-height: 1.3;">
                                {{ $custName }}
                            </div>
                            @if(!empty($order->guest_phone))
                                <div class="text-white-50 mt-1" style="font-size: 0.74rem;">
                                    <i class="bi bi-telephone me-1.5 opacity-60"></i>{{ $order->guest_phone }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge rounded-pill px-2.5 py-0.5 fw-medium d-inline-flex align-items-center gap-1" 
                                  style="background: rgba(239, 68, 68, 0.12); color: #f87171; font-size: 0.7rem; border: 1px solid rgba(239, 68, 68, 0.25);">
                                <i class="bi bi-x-circle"></i> Void
                            </span>
                            <div class="text-white-50 mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1.5 opacity-75"></i> {{ $voidTime ? $voidTime->format('H:i') : '-' }} WIB &bull; {{ $voidTime ? $voidTime->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>

                    <!-- Alasan & Pemohon (Elegan tanpa kotak tebal kaku) -->
                    <div class="pt-2 mt-2" style="border-top: 1px solid rgba(255, 255, 255, 0.06);">
                        <div class="d-flex align-items-baseline gap-1.5">
                            <span class="text-white-50 small" style="font-size: 0.74rem; flex-shrink: 0;">Alasan:</span>
                            <span class="fw-medium text-truncate" style="font-size: 0.78rem; color: #fca5a5;">{{ $alasan }}</span>
                        </div>
                        <div class="text-white-50 mt-0.5" style="font-size: 0.72rem;">
                            <i class="bi bi-person-badge me-1 opacity-60"></i>Petugas: <span class="text-light">{{ $pelaku }}</span>
                        </div>
                    </div>
                </div>

                <!-- 3. Daftar Item Pesanan yang Dibatalkan (Menyatu Mulus di Atas Kartu) -->
                <div class="flex-grow-1 d-flex flex-column mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-1.5 mb-1.5" style="border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                        <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.06em; color: #8b949e;">
                            Item Dibatalkan ({{ $order->detail_pesanan->sum('jumlah') }})
                        </span>
                        <span class="badge rounded-pill px-2 py-0.5 fw-medium" style="background: rgba(255, 255, 255, 0.05); color: #94a3b8; font-size: 0.65rem; border: 1px solid rgba(255, 255, 255, 0.06);">
                            Stok Dikembalikan
                        </span>
                    </div>

                    <div class="void-order-items py-1 flex-grow-1" style="max-height: 180px; overflow-y: auto;">
                        <ul class="list-unstyled mb-0">
                            @forelse($order->detail_pesanan as $detail)
                                <li class="d-flex justify-content-between align-items-start py-1.5" style="border-bottom: 1px dashed rgba(255, 255, 255, 0.05);">
                                    <div class="pe-2 text-truncate" style="min-width: 0;">
                                        <div class="d-flex align-items-baseline">
                                            <span class="fw-semibold text-secondary me-2" style="font-size: 0.82rem; flex-shrink: 0;">{{ $detail->jumlah }}×</span>
                                            <span class="text-white-50 text-decoration-line-through text-truncate" style="font-size: 0.84rem;">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                        </div>
                                        @if($detail->catatan)
                                            <div class="fst-italic ps-3 mt-0.5 text-white-50 opacity-50" style="font-size: 0.7rem;">
                                                &bull; {{ $detail->catatan }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-white-50 text-decoration-line-through text-nowrap ms-2" style="font-size: 0.8rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </span>
                                </li>
                            @empty
                                <li class="text-white-50 small py-2 fst-italic">Tidak ada rincian item</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- 4. Footer: Total Nilai Void & Info Bersih -->
                <div class="pt-2.5 mt-auto" style="border-top: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                        <span class="text-white-50" style="font-size: 0.82rem;">Total Nilai Void:</span>
                        <span class="fw-bold" style="font-size: 1.1rem; color: #f87171; letter-spacing: -0.01em;">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-1.5 py-1 text-white-50" style="font-size: 0.73rem;">
                        <i class="bi bi-shield-check text-success opacity-80"></i>
                        <span>Transaksi resmi dibatalkan & ditutup</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
