@php
    $cards = isset($groupedCompletedOrders) ? $groupedCompletedOrders : (isset($completedOrders) ? app(\App\Http\Controllers\PosController::class)->groupCompletedOrders($completedOrders) : collect([]));
@endphp

@if($cards->isEmpty())
    <div class="col-12 text-center py-5">
        <div class="card border-0 rounded-4 p-5 shadow-sm" style="background-color: rgba(22, 27, 34, 0.6); border: 1px dashed rgba(255, 255, 255, 0.15) !important;">
            <i class="bi bi-clock-history text-secondary display-3 mb-3 opacity-50"></i>
            <h5 class="text-white fw-bold">Belum Ada Riwayat Pesanan Selesai</h5>
            <p class="text-white-50 small mb-0">Pesanan yang diselesaikan oleh kasir / waitress pada hari ini akan otomatis tercatat di sini.</p>
        </div>
    </div>
@else
    @foreach($cards as $cardData)
        @php
            $primaryOrder = $cardData->primary_order;
            $isPaid = $cardData->all_paid;
            $mejaLabel = $cardData->meja ? 'Meja ' . $cardData->meja->nama_meja_atau_nomor : 'Takeaway';
            $custName = $cardData->customer_name;
            $searchKey = strtolower($cardData->order_ids_string . ' ' . $mejaLabel . ' ' . $custName . ' ' . ($cardData->guest_phone ?? ''));
        @endphp
        <div class="col-12 col-md-6 col-xl-4 completed-order-item" data-search="{{ $searchKey }}">
            <div class="card h-100 rounded-4 shadow-sm border-0 position-relative overflow-hidden" 
                 style="background-color: #161b22; border: 1px solid rgba(255, 255, 255, 0.08) !important; transition: transform 0.2s ease, box-shadow 0.2s ease;">
                
                <!-- Top Indicator Line: Hijau Selesai -->
                <div class="position-absolute top-0 start-0 end-0" style="height: 3px; background: linear-gradient(90deg, #10b981, #059669);"></div>

                <!-- Card Header -->
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center flex-wrap gap-1.5">
                        @if($cardData->is_grouped)
                            <span class="fw-bold text-white fs-6" style="font-family: 'Outfit', sans-serif;">
                                <span class="text-accent" style="color: #c08e5c;">#</span>{{ $cardData->order_ids_display }}
                            </span>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                <i class="bi bi-layers-fill me-1"></i>Gabungan ({{ count($cardData->orders) }}x Order)
                            </span>
                        @else
                            <span class="fw-bold text-white fs-6" style="font-family: 'Outfit', sans-serif;">
                                <span class="text-accent" style="color: #c08e5c;">#</span>{{ $primaryOrder->id }}
                            </span>
                        @endif
                        <span class="badge rounded-pill px-2 py-1 small fw-bold" style="background-color: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-check-circle-fill me-1"></i> SELESAI
                        </span>
                    </div>
                    <div>
                        @if($cardData->meja)
                            <span class="badge rounded-pill bg-dark border border-secondary text-warning px-2.5 py-1">
                                <i class="bi bi-geo-alt-fill me-1"></i> Meja {{ $cardData->meja->nama_meja_atau_nomor }}
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
                        <!-- Info Pemesan & Waktu Selesai -->
                        <div class="d-flex justify-content-between align-items-start mb-2 pb-2 border-bottom border-secondary border-opacity-15 flex-wrap gap-1 text-white-50 small">
                            <div>
                                <div class="text-white fw-semibold">
                                    <i class="bi bi-person me-1 text-secondary"></i> {{ $custName }}
                                </div>
                                @if(!empty($cardData->guest_phone))
                                    <div style="font-size: 0.72rem;" class="text-white-50">
                                        <i class="bi bi-telephone me-1"></i> {{ $cardData->guest_phone }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="text-white-50" style="font-size: 0.75rem;">
                                    <i class="bi bi-clock-history me-1 text-success"></i> {{ $cardData->latest_created_at->format('H:i') }} WIB
                                </div>
                                <div style="font-size: 0.7rem;" class="text-secondary">
                                    {{ $cardData->latest_created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <!-- Status Pembayaran & Kasir -->
                        <div class="d-flex justify-content-between align-items-center mb-3 text-white-50 small">
                            <span style="font-size: 0.75rem;">
                                <i class="bi bi-person-badge me-1"></i> {{ $primaryOrder->kasir->name ?? 'Kasir' }}
                            </span>
                            @if($isPaid)
                                <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Lunas
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 py-1 px-2" style="font-size: 0.72rem;">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Bayar
                                </span>
                            @endif
                        </div>

                        <!-- Daftar Item Pesanan -->
                        <div class="rounded-3 p-3 mb-3" style="background-color: rgba(22, 27, 34, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); max-height: 180px; overflow-y: auto;">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-1.5 border-bottom border-secondary border-opacity-25" style="padding-top: 2px;">
                                <span class="text-white-50 fw-bold text-uppercase d-inline-block" style="font-size: 0.75rem; letter-spacing: 0.5px; line-height: 1.5;">
                                    RINCIAN MENU ({{ $cardData->total_items }} ITEM{{ $cardData->is_grouped ? ' DARI ' . count($cardData->orders) . ' PESANAN' : '' }})
                                </span>
                            </div>

                            @if($cardData->is_grouped)
                                @foreach($cardData->orders as $subIdx => $subOrd)
                                    <div class="mb-2 {{ !$loop->last ? 'pb-2 border-bottom border-secondary border-opacity-15' : '' }}">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="badge {{ $subIdx === 0 ? 'bg-secondary' : 'bg-warning text-dark' }} py-0.5 px-1.5 fw-bold" style="font-size: 0.65rem;">
                                                {{ $subIdx === 0 ? 'Pesanan Awal' : 'Tambahan' }} (#{{ $subOrd->id }})
                                            </span>
                                            <small class="text-white-50" style="font-size: 0.68rem;">{{ $subOrd->created_at->format('H:i') }} WIB</small>
                                        </div>
                                        <ul class="list-unstyled mb-0 ps-1" style="font-size: 0.8rem;">
                                            @foreach($subOrd->detail_pesanan as $detail)
                                                <li class="d-flex justify-content-between align-items-center py-0.5 text-white-50">
                                                    <div class="text-truncate pe-2">
                                                        <span class="text-white fw-medium">{{ $detail->jumlah }}x</span> 
                                                        <span class="text-light">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                                        @if($detail->catatan)
                                                            <small class="d-block text-warning opacity-75 fst-italic" style="font-size: 0.68rem;">&bull; {{ $detail->catatan }}</small>
                                                        @endif
                                                    </div>
                                                    <span class="text-white-50 fw-semibold text-nowrap" style="font-size: 0.76rem;">
                                                        Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            @else
                                <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                    @foreach($primaryOrder->detail_pesanan as $detail)
                                        <li class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary border-opacity-10 text-white-50">
                                            <div class="text-truncate pe-2">
                                                <span class="text-white fw-medium">{{ $detail->jumlah }}x</span> 
                                                <span class="text-light">{{ $detail->menu->nama_menu ?? 'Item Menu' }}</span>
                                                @if($detail->catatan)
                                                    <small class="d-block text-warning opacity-75 fst-italic" style="font-size: 0.7rem;">&bull; {{ $detail->catatan }}</small>
                                                @endif
                                            </div>
                                            <span class="text-white-50 fw-semibold text-nowrap" style="font-size: 0.78rem;">
                                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    <!-- Footer: Total & Tombol Aksi -->
                    <div class="pt-2 border-top border-secondary border-opacity-25">
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <span class="text-white-50 small">Total Pesanan:</span>
                            <span class="text-accent fw-bold fs-6" style="color: #c08e5c; font-family: 'Outfit', sans-serif;">
                                Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-1.5">
                            @php $printIds = $cardData->order_ids_string; @endphp
                            <button type="button" class="btn btn-sm text-white w-100 fw-bold rounded-pill d-inline-flex align-items-center justify-content-center btn-touch shadow-sm" 
                                    style="font-size: 0.8rem; height: 36px; background: linear-gradient(135deg, #0284c7, #0369a1); border: none;"
                                    onclick="window.printThermal({{ $primaryOrder->id }})">
                                <i class="bi bi-printer-fill me-1.5"></i> {{ $cardData->is_grouped ? 'Cetak Struk Thermal' : 'Cetak Struk' }}
                            </button>
                            <div class="text-center">
                                <a href="{{ route('kasir.order.receipt', ['id' => $printIds]) }}" target="_blank" 
                                   class="text-white-50 text-decoration-none d-inline-flex align-items-center py-0.5" 
                                   style="font-size: 0.72rem;" title="Buka Pratinjau Struk Digital di Tab Baru">
                                    <i class="bi bi-eye me-1"></i> {{ $cardData->is_grouped ? 'Pratinjau Struk Gabungan' : 'Pratinjau Struk Digital' }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endif
