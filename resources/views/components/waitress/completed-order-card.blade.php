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
    .btn-completed-print {
        background-color: #c08e5c !important;
        color: #ffffff !important;
        border: 1px solid rgba(192, 142, 92, 0.5) !important;
        border-radius: 10px !important;
        height: 40px !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.01em;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .btn-completed-print:hover {
        background-color: #aa7949 !important;
        color: #ffffff !important;
        border-color: #c08e5c !important;
        box-shadow: 0 4px 14px rgba(192, 142, 92, 0.3);
        transform: translateY(-1px);
    }
    .btn-completed-print:active {
        transform: translateY(0);
    }
    .print-receipt-link {
        color: #8b949e !important;
        text-decoration: none !important;
        font-size: 0.75rem !important;
        transition: color 0.15s ease;
    }
    .print-receipt-link:hover {
        color: #ffffff !important;
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
                            <span class="badge rounded-pill px-2 py-0.5 fw-medium" style="background: rgba(255, 255, 255, 0.06); color: #94a3b8; font-size: 0.68rem; border: 1px solid rgba(255, 255, 255, 0.08);">
                                <i class="bi bi-layers me-1"></i>{{ count($cardData->orders) }}x Order
                            </span>
                        @else
                            <span class="fw-bold text-white fs-5" style="letter-spacing: -0.02em;">
                                <span style="color: #c08e5c; font-weight: 500;">#</span>{{ $primaryOrder->id }}
                            </span>
                        @endif

                        <span class="badge rounded-pill px-2.5 py-1 fw-semibold" 
                              style="background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); font-size: 0.7rem; letter-spacing: 0.03em;">
                            SELESAI
                        </span>
                    </div>

                    <div>
                        @if($cardData->meja)
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
                                <span class="badge rounded-pill px-2.5 py-0.5 fw-medium d-inline-flex align-items-center gap-1" 
                                      style="background: rgba(16, 185, 129, 0.12); color: #34d399; font-size: 0.7rem; border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <i class="bi bi-check2"></i> Lunas
                                </span>
                            @else
                                <span class="badge rounded-pill px-2.5 py-0.5 fw-medium d-inline-flex align-items-center gap-1" 
                                      style="background: rgba(239, 68, 68, 0.12); color: #f87171; font-size: 0.7rem; border: 1px solid rgba(239, 68, 68, 0.25);">
                                    <i class="bi bi-exclamation-circle"></i> Belum Bayar
                                </span>
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
                                        <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 0.65rem; background: rgba(255, 255, 255, 0.06); color: #cbd5e1;">
                                            {{ $subIdx === 0 ? 'Pesanan Utama' : 'Tambahan' }} (#{{ $subOrd->id }})
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

                <!-- 4. Footer: Total & Tombol Cetak Struk Solid Elegan -->
                <div class="pt-2.5 mt-auto" style="border-top: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex justify-content-between align-items-baseline mb-3">
                        <span class="text-white-50" style="font-size: 0.82rem;">Total Pesanan:</span>
                        <span class="fw-bold" style="font-size: 1.1rem; color: #e2b17a; letter-spacing: -0.01em;">
                            Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-completed-print w-100 d-inline-flex align-items-center justify-content-center btn-touch shadow-sm" 
                                onclick="window.printThermal({{ $primaryOrder->id }})">
                            <i class="bi bi-printer me-2"></i> {{ $cardData->is_grouped ? 'Cetak Struk Thermal' : 'Cetak Struk' }}
                        </button>
                        <div class="text-center pt-0.5">
                            <a href="{{ route('kasir.order.receipt', ['id' => $printIds]) }}" target="_blank" 
                               class="d-inline-flex align-items-center gap-1.5 py-0.5 print-receipt-link" 
                               title="Buka Pratinjau Struk Digital di Tab Baru">
                                <i class="bi bi-arrow-up-right me-1 opacity-70"></i> {{ $cardData->is_grouped ? 'Pratinjau Struk Gabungan' : 'Pratinjau Struk Digital' }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
