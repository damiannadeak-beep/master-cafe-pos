<style>
    .void-card-luxury {
        background-color: #161b22 !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-top: 3px solid #f87171 !important;
        border-radius: 14px !important;
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.45);
        padding: 1.25rem !important;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .void-card-luxury:hover {
        transform: translateY(-2px);
        border-color: rgba(248, 113, 113, 0.35) !important;
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
                
                <!-- 1. Header: Nomor Pesanan, Status, dan Meja -->
                <div class="d-flex justify-content-between align-items-center pb-2.5 mb-2.5 border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                            #{{ $order->id }}
                        </span>
                        <span class="text-danger fw-semibold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-x-circle-fill"></i> Dibatalkan
                        </span>
                    </div>

                    <div>
                        @if($order->meja)
                            <span class="text-secondary small fw-medium d-inline-flex align-items-center gap-1">
                                <i class="bi bi-geo-alt text-warning"></i> {{ $labelMeja }}
                            </span>
                        @else
                            <span class="text-secondary small fw-medium d-inline-flex align-items-center gap-1">
                                <i class="bi bi-bag"></i> Takeaway
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 2. Customer & Waktu -->
                <div class="mb-2">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="text-white fw-bold" style="font-size: 0.95rem;">
                                {{ $custName }}
                            </div>
                            @if(!empty($order->guest_phone))
                                <div class="text-secondary small mt-0.5" style="font-size: 0.75rem;">
                                    <i class="bi bi-telephone me-1"></i>{{ $order->guest_phone }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end text-secondary small" style="font-size: 0.74rem;">
                            <div>
                                <i class="bi bi-clock me-1"></i>{{ $voidTime ? $voidTime->format('H:i') : '-' }} WIB
                            </div>
                            <div class="opacity-75" style="font-size: 0.68rem;">
                                {{ $voidTime ? $voidTime->diffForHumans() : '' }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Informasi Audit Pembatalan -->
                <div class="my-2 py-0.5">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                        <span class="text-secondary small" style="font-size: 0.74rem;">Alasan</span>
                        <span class="text-danger fw-semibold small text-truncate" style="font-size: 0.76rem; max-width: 65%;">
                            {{ $alasan }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 text-secondary small" style="font-size: 0.72rem;">
                        <span>Petugas</span>
                        <span class="text-white-50">{{ $pelaku }}</span>
                    </div>
                </div>

                <!-- 4. Rincian Item (Typography Nyaman & Mudah Dibaca) -->
                <div class="flex-grow-1 d-flex flex-column mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-1.5 mb-1.5 border-bottom border-secondary border-opacity-10">
                        <span class="text-uppercase fw-semibold text-secondary" style="font-size: 0.68rem; letter-spacing: 0.05em;">
                            Item Dibatalkan ({{ $order->detail_pesanan->sum('jumlah') }})
                        </span>
                        <span class="text-secondary small d-inline-flex align-items-center gap-1" style="font-size: 0.7rem;">
                            <i class="bi bi-arrow-return-left"></i> Stok Kembali
                        </span>
                    </div>

                    <div class="void-order-items py-1 flex-grow-1" style="max-height: 160px; overflow-y: auto;">
                        <ul class="list-unstyled mb-0">
                            @forelse($order->detail_pesanan as $detail)
                                <li class="d-flex justify-content-between align-items-start py-1.5 border-bottom border-secondary border-opacity-10">
                                    <div class="pe-2 text-truncate" style="min-width: 0;">
                                        <div class="d-flex align-items-baseline">
                                            <span class="fw-semibold text-secondary me-2" style="font-size: 0.82rem; flex-shrink: 0;">{{ $detail->jumlah }}×</span>
                                            <span class="text-white-50 text-truncate" style="font-size: 0.82rem;">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                        </div>
                                        @if($detail->catatan)
                                            <div class="fst-italic ps-3 mt-0.5 text-secondary" style="font-size: 0.7rem;">
                                                &bull; {{ $detail->catatan }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-secondary small text-nowrap ms-2" style="font-size: 0.8rem; font-variant-numeric: tabular-nums;">
                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                    </span>
                                </li>
                            @empty
                                <li class="text-secondary small py-2 fst-italic">Tidak ada rincian item</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- 5. Footer: Total Dibatalkan & Status Audit -->
                <div class="pt-2.5 mt-auto border-top border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="text-secondary small">Total Dibatalkan</span>
                        <span class="fw-bold text-danger fs-6" style="letter-spacing: -0.01em;">
                            Rp {{ number_format($order->total, 0, ',', '.') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center justify-content-center gap-1.5 text-secondary pt-1" style="font-size: 0.72rem;">
                        <i class="bi bi-arrow-counterclockwise text-secondary"></i>
                        <span>Stok dikembalikan &bull; Transaksi ditutup</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
