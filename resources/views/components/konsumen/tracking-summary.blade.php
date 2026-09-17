<!-- Ringkasan Menu Pesanan -->
<div class="card tracking-card shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-2">
        <h6 class="text-white fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">
            <i class="bi bi-receipt me-1" style="color: #c08e5c;"></i> 
            {{ (isset($activeTableOrders) && $activeTableOrders->count() > 1) ? 'Semua Rincian Menu di Meja Ini' : 'Rincian Menu Pesanan' }}
        </h6>
        <span class="badge rounded-pill bg-dark border border-secondary text-secondary">
            @if(isset($activeTableOrders) && $activeTableOrders->count() > 1)
                {{ $activeTableOrders->sum(fn($o) => $o->detail_pesanan->sum('jumlah')) }} Item ({{ $activeTableOrders->count() }} Pesanan)
            @else
                {{ $pesanan->detail_pesanan->sum('jumlah') }} Item
            @endif
        </span>
    </div>

    @if(isset($activeTableOrders) && $activeTableOrders->count() > 1)
        <!-- Multi-Order Grouped View (Semua Pesanan Ditampilkan Bersama) -->
        @foreach($activeTableOrders as $subIdx => $subOrder)
            @php
                $isCurrent = ($subOrder->id === $pesanan->id);
                $subPaid = ($subOrder->pembayaran && $subOrder->pembayaran->status === 'paid');
            @endphp
            <div class="rounded-3 p-3 mb-3" style="background: {{ $isCurrent ? 'rgba(192, 142, 92, 0.08)' : 'rgba(255, 255, 255, 0.03)' }}; border: 1px solid {{ $isCurrent ? 'rgba(192, 142, 92, 0.35)' : 'rgba(255, 255, 255, 0.08)' }};">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-secondary border-opacity-25 flex-wrap gap-1">
                    <div class="d-flex align-items-center gap-1.5">
                        <span class="fw-bold {{ $subIdx === 0 ? 'text-white' : 'text-warning' }}" style="font-size: 0.95rem;">
                            <i class="bi {{ $subIdx === 0 ? 'bi-receipt text-secondary' : 'bi-plus-circle-fill text-warning' }} me-1"></i>
                            {{ $subIdx === 0 ? 'Pesanan Awal' : 'Pesanan Tambahan' }} (#{{ $subOrder->id }})
                            @if(!empty($subOrder->customer_name) && strtolower(trim($subOrder->customer_name)) !== strtolower(trim($pesanan->customer_name)))
                                <span class="badge bg-dark border border-secondary text-info ms-1 py-0.5 px-1.5" style="font-size: 0.65rem; font-weight: normal;">a/n {{ $subOrder->customer_name }}</span>
                            @endif
                        </span>
                        <span class="text-white-50 ms-1" style="font-size: 0.72rem;">{{ $subOrder->created_at->format('H:i') }} WIB</span>
                        @if($isCurrent)
                            <span class="badge bg-warning text-dark py-0.5 px-1.5 ms-1 fw-bold" style="font-size: 0.65rem;">Sedang Dilihat</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-1">
                        @if($subPaid)
                            <span class="badge bg-success bg-opacity-25 text-success border border-success" style="font-size: 0.7rem;">
                                <i class="bi bi-check-circle me-1"></i>Lunas ({{ strtoupper($subOrder->pembayaran->metode ?? 'QRIS') }})
                            </span>
                        @else
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1"></i>Belum Lunas
                            </span>
                        @endif
                        @if($subOrder->status === 'processing')
                            <span class="badge bg-primary" style="font-size: 0.7rem;"><i class="bi bi-fire me-1"></i>Sedang Dimasak</span>
                        @elseif($subOrder->status === 'completed')
                            <span class="badge bg-success" style="font-size: 0.7rem;"><i class="bi bi-check2-all me-1"></i>Siap Dihidangkan</span>
                        @else
                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;"><i class="bi bi-hourglass-split me-1"></i>Menunggu</span>
                        @endif
                    </div>
                </div>

                <div class="list-group list-group-flush mb-2">
                    @foreach($subOrder->detail_pesanan as $detail)
                        <div class="d-flex justify-content-between align-items-start py-1.5 border-bottom border-secondary border-opacity-10">
                            <div>
                                <span class="text-white small fw-medium">{{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Item' }}</span>
                                @if(!empty($detail->catatan))
                                    <small class="text-warning d-block" style="font-size: 0.74rem;">
                                        <i class="bi bi-pencil-square me-1"></i>{{ $detail->catatan }}
                                    </small>
                                @endif
                                @if(!empty($detail->selected_variants))
                                    <small class="text-secondary d-block" style="font-size: 0.72rem;">
                                        {{ is_array($detail->selected_variants) ? implode(', ', $detail->selected_variants) : $detail->selected_variants }}
                                    </small>
                                @endif
                            </div>
                            <span class="text-white-50 small">
                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex justify-content-between align-items-center pt-1 small text-white-50">
                    <span>Subtotal Pesanan #{{ $subOrder->id }}:</span>
                    <span class="text-white fw-bold">Rp {{ number_format($subOrder->total - ($subOrder->discount_amount ?? 0), 0, ',', '.') }}</span>
                </div>

                @if(!$subPaid)
                    <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-warning"><i class="bi bi-info-circle me-1"></i>Tagihan pesanan ini belum diselesaikan.</small>
                        <a href="{{ url('/konsumen/checkout/' . $subOrder->id . '?token=' . $subOrder->order_token) }}" class="btn btn-sm rounded-pill px-3 py-1 fw-bold" style="background: var(--gradient-bronze); color: white; border: none; font-size: 0.75rem;">
                            ⚡ Bayar Pesanan #{{ $subOrder->id }}
                        </a>
                    </div>
                @endif
            </div>
        @endforeach

        <!-- Total Akumulatif Meja Ini -->
        @php
            $grandTotal = $activeTableOrders->sum(fn($o) => $o->total - ($o->discount_amount ?? 0));
            $allPaid = $activeTableOrders->every(fn($o) => $o->pembayaran && $o->pembayaran->status === 'paid');
        @endphp
        <div class="p-3 rounded-3 mt-3" style="background: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.35);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="fw-bold text-white fs-6">Total Tagihan Seluruh Pesanan</span>
                    <small class="text-white-50 d-block" style="font-size: 0.75rem;">
                        Akumulasi dari {{ $activeTableOrders->count() }} pesanan aktif Anda
                    </small>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold mb-0 text-accent" style="color: #c08e5c;">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </h5>
                    <small class="badge {{ $allPaid ? 'bg-success' : 'bg-danger' }} rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                        {{ $allPaid ? 'Semua Lunas' : 'Ada Yang Belum Lunas' }}
                    </small>
                </div>
            </div>
        </div>

        @php
            $unpaidGroupOrders = $activeTableOrders->filter(fn($o) => !$o->pembayaran || $o->pembayaran->status !== 'paid');
            $hasCashUnpaidGroup = $unpaidGroupOrders->contains(fn($o) => $o->pembayaran && $o->pembayaran->metode === 'cash');
            $maxUangDiterimaGroup = (float) $unpaidGroupOrders->max(fn($o) => $o->pembayaran?->uang_diterima ?? 0);
            $unpaidAmountGroup = (float) $unpaidGroupOrders->sum(fn($o) => $o->pembayaran?->total_bayar ?? ($o->total - ($o->discount_amount ?? 0)));
            $kembalianMejaGroup = max(0, $maxUangDiterimaGroup - $unpaidAmountGroup);
        @endphp

        @if($hasCashUnpaidGroup && $maxUangDiterimaGroup > 0)
            <div class="mt-3 p-3 rounded-3" style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-success bg-opacity-25 text-success border border-success fw-bold">
                        <i class="bi bi-cash-stack me-1"></i> Bayar Tunai di Meja
                    </span>
                    <small class="text-white-50">Bayar saat makanan tiba</small>
                </div>
                @if($kembalianMejaGroup > 0)
                    <div class="small text-white mt-2">
                        Uang disiapkan: <strong class="text-white">Rp {{ number_format($maxUangDiterimaGroup, 0, ',', '.') }}</strong>
                    </div>
                    <div class="small text-warning fw-bold">
                        <i class="bi bi-wallet-fill me-1"></i> Kembalian Waitress: Rp {{ number_format($kembalianMejaGroup, 0, ',', '.') }}
                    </div>
                @else
                    <div class="small text-success fw-bold mt-2">
                        <i class="bi bi-check-circle me-1"></i> Uang Pas: Rp {{ number_format($maxUangDiterimaGroup, 0, ',', '.') }} (Tanpa Kembalian)
                    </div>
                @endif
            </div>
        @endif
    @else
        <!-- Single Order View (Bawaan saat cuma 1 pesanan) -->
        <div class="list-group list-group-flush">
            @foreach($pesanan->detail_pesanan as $detail)
                <div class="d-flex justify-content-between align-items-start py-2 border-bottom border-secondary border-opacity-10">
                    <div>
                        <h6 class="text-white mb-0 fs-6">{{ $detail->jumlah }}x {{ $detail->menu->nama_menu ?? 'Item' }}</h6>
                        @if(!empty($detail->catatan))
                            <small class="text-warning d-block" style="font-size: 0.76rem;">
                                <i class="bi bi-pencil-square me-1"></i>{{ $detail->catatan }}
                            </small>
                        @endif
                        @if(!empty($detail->selected_variants))
                            <small class="text-secondary d-block" style="font-size: 0.74rem;">
                                {{ is_array($detail->selected_variants) ? implode(', ', $detail->selected_variants) : $detail->selected_variants }}
                            </small>
                        @endif
                    </div>
                    <span class="text-white fw-semibold small">
                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                    </span>
                </div>
            @endforeach
        </div>

        <div class="mt-3 pt-2">
            <div class="d-flex justify-content-between small text-secondary mb-1">
                <span>Subtotal</span>
                <span>Rp {{ number_format($pesanan->total, 0, ',', '.') }}</span>
            </div>
            @if($pesanan->discount_amount > 0)
                <div class="d-flex justify-content-between small text-danger mb-1">
                    <span>Diskon Promo</span>
                    <span>- Rp {{ number_format($pesanan->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="d-flex justify-content-between fw-bold fs-5 text-white mt-2 pt-2 border-top border-secondary border-opacity-25">
                <span style="color: #c08e5c;">Total Tagihan</span>
                <span style="color: #c08e5c;">
                    Rp {{ number_format($pembayaran->total_bayar ?? ($pesanan->total - $pesanan->discount_amount), 0, ',', '.') }}
                </span>
            </div>

            @if(($pesanan->tipe_pesanan ?? '') === 'dine_in' && $pembayaran && $pembayaran->metode === 'cash')
                <div class="mt-3 p-3 rounded-3" style="background: rgba(34, 197, 94, 0.08); border: 1px solid rgba(34, 197, 94, 0.25);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-success bg-opacity-25 text-success border border-success fw-bold">
                            <i class="bi bi-cash-stack me-1"></i> Bayar Tunai (Cash) di Meja
                        </span>
                        <small class="text-white-50">Bayar saat makanan tiba</small>
                    </div>
                    @if($pembayaran->uang_kembalian > 0)
                        <div class="small text-white mt-2">
                            Uang disiapkan: <strong class="text-white">Rp {{ number_format($pembayaran->uang_diterima, 0, ',', '.') }}</strong>
                        </div>
                        <div class="small text-warning fw-bold">
                            <i class="bi bi-wallet-fill me-1"></i> Kembalian Waitress: Rp {{ number_format($pembayaran->uang_kembalian, 0, ',', '.') }}
                        </div>
                    @elseif($pembayaran->uang_diterima)
                        <div class="small text-success fw-bold mt-2">
                            <i class="bi bi-check-circle me-1"></i> Uang Pas (Tanpa Kembalian)
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif
</div>
