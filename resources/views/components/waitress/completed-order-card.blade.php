@php
    $cards = isset($groupedCompletedOrders) ? $groupedCompletedOrders : (isset($completedOrders) ? app(\App\Http\Controllers\PosController::class)->groupCompletedOrders($completedOrders) : collect([]));
@endphp

<style>
    .completed-card-luxury {
        background-color: #141820 !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.45);
        padding: 1.25rem !important; /* Pastikan padding konsisten 20px di semua sisi */
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .completed-card-luxury:hover {
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, 0.16) !important;
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.55);
    }
    .btn-print-thermal {
        background-color: rgba(56, 189, 248, 0.1) !important;
        border: 1px solid rgba(56, 189, 248, 0.25) !important;
        color: #38bdf8 !important;
        border-radius: 8px !important;
        font-size: 0.78rem !important;
        font-weight: 500 !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-print-thermal:hover {
        background-color: rgba(56, 189, 248, 0.2) !important;
        border-color: rgba(56, 189, 248, 0.45) !important;
        color: #7dd3fc !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15);
    }
    .btn-print-thermal:active {
        transform: translateY(0);
    }
    .btn-print-receipt {
        background-color: rgba(192, 142, 92, 0.12) !important;
        border: 1px solid rgba(192, 142, 92, 0.28) !important;
        color: #e2b17a !important;
        border-radius: 8px !important;
        font-size: 0.78rem !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-print-receipt:hover {
        background-color: rgba(192, 142, 92, 0.22) !important;
        border-color: rgba(192, 142, 92, 0.5) !important;
        color: #f6d3a8 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(192, 142, 92, 0.2);
    }
    .btn-print-receipt:active {
        transform: translateY(0);
    }
    .completed-order-items::-webkit-scrollbar {
        width: 4px;
    }
    .completed-order-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .completed-order-items::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 4px;
    }
    .completed-order-items::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.25);
    }
</style>

@if($cards->isEmpty())
    <div class="col-12 text-center py-5">
        <div class="card border-0 rounded-4 p-5 shadow-sm" style="background-color: rgba(20, 24, 32, 0.6); border: 1px dashed rgba(255, 255, 255, 0.12) !important;">
            <i class="bi bi-clock-history text-secondary display-4 mb-3 opacity-40"></i>
            <h5 class="text-white fw-semibold mb-1">Belum Ada Riwayat Pesanan Selesai</h5>
            <p class="text-white-50 small mb-0">Pesanan yang diselesaikan oleh kasir / waitress pada hari ini akan otomatis tercatat di sini.</p>
        </div>
    </div>
@else
    @foreach($cards as $cardData)
        @php
            $primaryOrder = $cardData->primary_order;
            $isPaid = $cardData->all_paid;
            $rawMejaName = trim((string)($cardData->meja->nama_meja_atau_nomor ?? ''));
            $labelMeja = $cardData->meja ? (preg_match('/^meja\b/i', $rawMejaName) ? $rawMejaName : 'Meja ' . $rawMejaName) : 'Takeaway';
            $custName = $cardData->customer_name;
            $searchKey = strtolower($cardData->order_ids_string . ' ' . $labelMeja . ' ' . $custName . ' ' . ($cardData->guest_phone ?? ''));
            $printIds = $cardData->order_ids_string;
        @endphp
        <div class="col-12 col-md-6 col-xl-4 completed-order-item" data-search="{{ $searchKey }}">
            <div class="card h-100 border-0 position-relative completed-card-luxury d-flex flex-column">
                
                <!-- 1. Card Header: Bersih & Terpadu -->
                <div class="d-flex justify-content-between align-items-center pb-2.5 mb-2.5" style="border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex align-items-center gap-2">
                        @if($cardData->is_grouped)
                            <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                                <span style="color: #c08e5c; font-weight: 500;">#</span>{{ $cardData->order_ids_display }}
                            </span>
                            <span class="text-white-50 fw-medium d-inline-flex align-items-center" style="font-size: 0.75rem; gap: 4px;">
                                <i class="bi bi-layers"></i> {{ count($cardData->orders) }}x Order
                            </span>
                        @else
                            <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                                <span style="color: #c08e5c; font-weight: 500;">#</span>{{ $primaryOrder->id }}
                            </span>
                        @endif

                        <span class="text-success fw-semibold d-inline-flex align-items-center" style="font-size: 0.75rem; gap: 4px;">
                            <i class="bi bi-check-circle-fill"></i> SELESAI
                        </span>
                    </div>

                    <div>
                        @if($cardData->meja)
                            <span class="fw-medium text-warning d-inline-flex align-items-center" style="font-size: 0.78rem; gap: 4px;">
                                <i class="bi bi-geo-alt"></i> {{ $labelMeja }}
                            </span>
                        @else
                            <span class="fw-medium text-secondary d-inline-flex align-items-center" style="font-size: 0.78rem; gap: 4px;">
                                <i class="bi bi-bag"></i> Takeaway
                            </span>
                        @endif
                    </div>
                </div>

                <!-- 2. Customer & Metadata Info (Aliran Alami, Bebas Kolom) -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1.5">
                        <div>
                            <div class="text-white fw-semibold" style="font-size: 0.95rem; line-height: 1.3;">
                                {{ $custName }}
                            </div>
                            @if(!empty($cardData->guest_phone))
                                <div class="text-white-50 mt-1" style="font-size: 0.74rem;">
                                    <i class="bi bi-telephone me-1.5 opacity-60"></i>{{ $cardData->guest_phone }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($isPaid)
                                <div class="text-success fw-medium d-inline-flex align-items-center" style="font-size: 0.75rem; gap: 4px;">
                                    <i class="bi bi-check2-circle"></i> Lunas
                                </div>
                            @else
                                <div class="text-danger fw-medium d-inline-flex align-items-center" style="font-size: 0.75rem; gap: 4px;">
                                    <i class="bi bi-exclamation-circle"></i> Belum Bayar
                                </div>
                            @endif
                            <div class="text-white-50 mt-1" style="font-size: 0.7rem;">
                                <i class="bi bi-clock me-1.5 opacity-75"></i> {{ $cardData->latest_created_at->format('H:i') }} WIB &bull; {{ $cardData->latest_created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center text-white-50 mt-1" style="font-size: 0.72rem;">
                        <span class="opacity-75"><i class="bi bi-person-badge me-1.5"></i>Kasir: {{ $primaryOrder->kasir->name ?? 'Kasir' }}</span>
                    </div>
                </div>

                <!-- 3. Menu Items (Menyatu di atas kartu, tanpa kolom kaku) -->
                <div class="flex-grow-1 d-flex flex-column mb-3">
                    <div class="d-flex justify-content-between align-items-center pb-1.5 mb-1.5" style="border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                        <span class="text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 0.06em; color: #8b949e;">
                            Rincian Menu ({{ $cardData->total_items }})
                        </span>
                    </div>

                    <div class="completed-order-items py-1 flex-grow-1" style="max-height: 180px; overflow-y: auto;">
                        @if($cardData->is_grouped)
                            @foreach($cardData->orders as $subIdx => $subOrd)
                                <div class="mb-2 {{ !$loop->last ? 'pb-2 border-bottom border-white border-opacity-5' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-white-50 fw-semibold d-inline-flex align-items-center" style="font-size: 0.72rem; gap: 4px;">
                                            <i class="bi {{ $subIdx === 0 ? 'bi-star-fill text-warning' : 'bi-plus-circle' }}" style="font-size: 0.65rem;"></i>
                                            {{ $subIdx === 0 ? 'Pesanan Utama' : 'Tambahan' }} <span style="color: #c08e5c;">(#{{ $subOrd->id }})</span>
                                        </span>
                                        <span class="text-white-50" style="font-size: 0.68rem;"><i class="bi bi-clock me-1.5 opacity-75"></i> {{ $subOrd->created_at->format('H:i') }} WIB</span>
                                    </div>
                                    <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                        @foreach($subOrd->detail_pesanan as $detail)
                                            <li class="d-flex justify-content-between align-items-start py-1" style="border-bottom: 1px dashed rgba(255, 255, 255, 0.05);">
                                                <div class="pe-2 text-truncate" style="min-width: 0;">
                                                    <div class="d-flex align-items-baseline">
                                                        <span class="fw-bold me-2" style="color: #cbd5e1; font-size: 0.8rem; flex-shrink: 0;">{{ $detail->jumlah }}×</span>
                                                        <span class="text-white fw-medium text-truncate" style="font-size: 0.82rem;">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                                    </div>
                                                    @if($detail->catatan)
                                                        <div class="fst-italic ps-3 mt-0.5" style="font-size: 0.7rem; color: #d4a373; opacity: 0.9;">
                                                            &bull; {{ $detail->catatan }}
                                                        </div>
                                                    @endif
                                                </div>
                                                <span class="text-white-50 fw-medium text-nowrap ms-2" style="font-size: 0.78rem; font-variant-numeric: tabular-nums;">
                                                    Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @else
                            <ul class="list-unstyled mb-0">
                                @foreach($primaryOrder->detail_pesanan as $detail)
                                    <li class="d-flex justify-content-between align-items-start py-1.5" style="border-bottom: 1px dashed rgba(255, 255, 255, 0.05);">
                                        <div class="pe-2 text-truncate" style="min-width: 0;">
                                            <div class="d-flex align-items-baseline">
                                                <span class="fw-bold me-2" style="color: #cbd5e1; font-size: 0.82rem; flex-shrink: 0;">{{ $detail->jumlah }}×</span>
                                                <span class="text-white fw-medium text-truncate" style="font-size: 0.84rem;">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                            </div>
                                            @if($detail->catatan)
                                                <div class="fst-italic ps-3 mt-0.5" style="font-size: 0.7rem; color: #d4a373; opacity: 0.9;">
                                                    &bull; {{ $detail->catatan }}
                                                </div>
                                            @endif
                                        </div>
                                        <span class="text-white-50 fw-medium text-nowrap ms-2" style="font-size: 0.8rem; font-variant-numeric: tabular-nums;">
                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <!-- 4. Footer: Total & Tombol Aksi Cetak Struk & Cetak Thermal -->
                <div class="pt-2.5 mt-auto" style="border-top: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <span class="text-white-50" style="font-size: 0.82rem;">Total Pesanan:</span>
                        <span class="fw-bold" style="font-size: 1.1rem; color: #e2b17a; letter-spacing: -0.01em;">
                            Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm px-3 py-2 btn-print-thermal d-inline-flex align-items-center justify-content-center flex-fill shadow-sm" 
                                onclick="window.printThermal({{ $primaryOrder->id }})" 
                                title="Cetak struk ke printer thermal / terminal POS"
                                style="gap: 5px !important;">
                            <i class="bi bi-printer" style="margin: 0 !important; margin-right: 0 !important;"></i> Cetak Thermal
                        </button>
                        <a href="{{ route('kasir.order.receipt', ['id' => $printIds]) }}" target="_blank" 
                           class="btn btn-sm px-3 py-2 btn-print-receipt d-inline-flex align-items-center justify-content-center flex-fill shadow-sm" 
                           title="Buka atau cetak struk kasir digital"
                           style="gap: 5px !important;">
                            <i class="bi bi-file-earmark-text" style="margin: 0 !important; margin-right: 0 !important;"></i> Cetak Struk
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
