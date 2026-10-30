@php
    $cards = isset($groupedOrders) ? $groupedOrders : (isset($orders) ? app(\App\Http\Controllers\PosController::class)->groupActiveOrders($orders) : collect([]));
@endphp

@forelse($cards as $cardData)
    @php 
        $primaryOrder = $cardData->primary_order;
        $orderIdsStr = implode(',', $cardData->order_ids);
        $cardTotalPay = (int) ($cardData->all_paid ? $cardData->total_bill : $cardData->unpaid_amount);
        $cardUangDiterima = (int) $cardData->total_uang_diterima;
        $cardUangKembalian = (int) $cardData->total_uang_kembalian;

        $allCardDetails = $cardData->orders->flatMap(fn($o) => $o->detail_pesanan);
        $totalItemCount = $allCardDetails->count();
        $servedItemCount = $allCardDetails->where('is_served', true)->count();
        $allCardItemsServed = ($totalItemCount > 0 && $servedItemCount === $totalItemCount);

        $rawMejaName = trim((string)($cardData->meja->nama_meja_atau_nomor ?? ''));
        $labelMeja = preg_match('/^meja\b/i', $rawMejaName) ? $rawMejaName : (!empty($rawMejaName) ? 'Meja ' . $rawMejaName : '');
        $searchKeywords = strtolower(implode(' ', array_filter([
            $cardData->order_ids_string,
            $labelMeja,
            $rawMejaName,
            $cardData->customer_name,
            $cardData->tipe_pesanan,
            $cardData->tipe_pesanan === 'takeaway' ? 'take away bungkus' : 'dine in makan'
        ])));
    @endphp
    <div class="col-md-6 col-lg-4 order-card-item" 
         id="order-card-{{ $primaryOrder->id }}" 
         data-order-ids="{{ $orderIdsStr }}" 
         data-total="{{ $cardTotalPay }}" 
         data-uang-diterima="{{ $cardUangDiterima }}" 
         data-uang-kembalian="{{ $cardUangKembalian }}" 
         data-total-items="{{ $totalItemCount }}" 
         data-served-items="{{ $servedItemCount }}"
         data-search="{{ $searchKeywords }}"
         data-paid="{{ $cardData->all_paid ? 'paid' : 'unpaid' }}"
         data-cooking="{{ $allCardItemsServed ? 'served' : ($servedItemCount > 0 ? 'cooking' : 'waiting') }}"
         data-tipe="{{ $cardData->tipe_pesanan }}">
        <div class="card shadow-sm border-0 h-100 rounded-4 position-relative overflow-hidden" style="background-color: #161b22; border: 1px solid #21262d !important; transition: transform 0.2s ease, box-shadow 0.2s ease;">
            <!-- Top Status Indicator Strip -->
            @if($cardData->overall_status === 'pending')
                <div class="position-absolute top-0 start-0 end-0" style="height: 3px; background: linear-gradient(90deg, #f59e0b, #d97706);"></div>
            @elseif($cardData->overall_status === 'processing')
                <div class="position-absolute top-0 start-0 end-0" style="height: 3px; background: linear-gradient(90deg, #38bdf8, #0284c7);"></div>
            @elseif($cardData->overall_status === 'completed')
                <div class="position-absolute top-0 start-0 end-0" style="height: 3px; background: linear-gradient(90deg, #34d399, #059669);"></div>
            @endif

            <div class="card-header bg-transparent py-3 px-3.5 border-bottom d-flex justify-content-between align-items-center" style="border-color: #21262d !important;">
                <div>
                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1.5">
                        <span class="fw-bold text-white fs-6">Pesanan #{{ $cardData->is_grouped ? $cardData->order_ids_string : $primaryOrder->id }}</span>
                        @if($cardData->is_grouped)
                            <span class="text-info fw-medium d-inline-flex align-items-center ms-1.5" style="font-size: 0.72rem;">
                                <i class="bi bi-layers-fill me-1" style="font-size: 0.75rem;"></i>Gabungan ({{ count($cardData->orders) }}x)
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($cardData->is_grouped)
                            <button type="button" 
                                    class="btn btn-sm py-0.5 px-2 rounded-2 fw-medium text-info border border-info border-opacity-30 d-inline-flex align-items-center gap-1 shadow-none" 
                                    style="background: rgba(56, 189, 248, 0.08); font-size: 0.68rem; height: 23px;" 
                                    onclick="window.toggleAllSubOrders('order-card-{{ $primaryOrder->id }}', {{ json_encode($cardData->order_ids) }})" 
                                    title="Buka / Lipat Semua Rincian Pesanan Meja Ini">
                                <i class="bi bi-arrows-collapse" style="font-size: 0.65rem; line-height: 1;"></i>
                                <span>Lipat / Buka</span>
                            </button>
                        @endif
                        <small class="text-secondary" style="font-size: 0.75rem;"><i class="bi bi-clock me-1 opacity-75"></i> {{ $cardData->latest_created_at->format('H:i') }} WIB</small>
                    </div>
                </div>
                <div class="text-end">
                    @if($cardData->overall_status === 'completed' || $allCardItemsServed)
                        <div class="text-success fw-bold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-check-circle-fill"></i> SELESAI SAJI
                        </div>
                        <small class="d-block text-success opacity-75 mt-0.5" style="font-size: 0.68rem;">Semua Menu Siap</small>
                    @elseif($cardData->overall_status === 'processing' || $servedItemCount > 0)
                        <div class="text-info fw-bold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-fire"></i> SEDANG DIMASAK
                        </div>
                        <small class="d-block text-info opacity-75 mt-0.5" style="font-size: 0.68rem;">{{ $servedItemCount }}/{{ $totalItemCount }} Menu Siap</small>
                    @else
                        <div class="text-warning fw-bold small d-inline-flex align-items-center gap-1">
                            <i class="bi bi-hourglass-split"></i> ANTRIAN MASAK
                        </div>
                        <small class="d-block text-warning opacity-75 mt-0.5" style="font-size: 0.68rem;">{{ $cardData->is_grouped ? '+ Menu Baru Masuk' : $totalItemCount . ' Menu Menunggu' }}</small>
                    @endif
                </div>
            </div>
            
            <div class="card-body p-3.5 text-white">
                <div class="p-3 rounded-3 mb-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <i class="{{ $cardData->tipe_pesanan == 'takeaway' ? 'bi bi-bag-check-fill' : 'bi bi-shop' }} text-warning" style="font-size: 1.15rem;"></i>
                            <span class="fw-semibold text-white" style="font-size: 0.88rem;">
                                {{ $cardData->tipe_pesanan == 'takeaway' ? 'Takeaway (Bungkus)' : 'Dine-In (Makan di Tempat)' }}
                            </span>
                        </div>
                        <div>
                            @if($cardData->tipe_pesanan == 'takeaway')
                                <span class="text-warning fw-semibold small">
                                    <i class="bi bi-bag me-1"></i>Bawa Pulang
                                </span>
                            @elseif($cardData->tipe_pesanan == 'dine_in' && !$cardData->id_meja)
                                <span class="text-danger fw-semibold small">
                                    <i class="bi bi-geo-alt me-1"></i>Belum Pilih Meja
                                </span>
                            @else
                                @php
                                    $rawMejaName = trim((string)($cardData->meja->nama_meja_atau_nomor ?? '?'));
                                    $labelMeja = preg_match('/^meja\b/i', $rawMejaName) ? $rawMejaName : 'Meja ' . $rawMejaName;
                                @endphp
                                <span class="text-warning fw-bold fs-6">
                                    <i class="bi bi-geo-alt-fill me-1"></i>{{ $labelMeja }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Informasi Detail Pemesan & No WA -->
                    <div class="pt-2 border-top d-flex justify-content-between align-items-center flex-wrap gap-1" style="border-color: #21262d !important;">
                        <div>
                            <div class="small text-secondary">
                                <i class="bi bi-person me-1 text-secondary opacity-75"></i> Pemesan: <span class="text-white fw-semibold">{{ $cardData->customer_name }}</span>
                            </div>
                            @if(!empty($cardData->guest_phone))
                                <div class="small text-white-50 mt-1">
                                    <i class="bi bi-whatsapp text-success me-1"></i> WA: <span class="text-white">{{ $cardData->guest_phone }}</span>
                                </div>
                            @endif
                        </div>

                        @if(!empty($cardData->guest_phone))
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $cardData->guest_phone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $cleanPhone = '62' . substr($cleanPhone, 1);
                                }
                                $orderRefText = $cardData->is_grouped ? $cardData->order_ids_string : $primaryOrder->id;
                                $waMessage = rawurlencode("Halo Kak {$cardData->customer_name}, pesanan Master Cafe #{$orderRefText} Anda sudah siap! Silakan diambil di kasir/counter. Terima kasih!");
                            @endphp
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                <i class="bi bi-whatsapp me-1"></i> Chat WA
                            </a>
                        @endif
                    </div>
                </div>

                <div class="table-responsive" style="overflow-x: hidden;">
                    <table class="table table-dark text-white table-sm table-borderless mb-0" style="table-layout: fixed; width: 100%; font-size: 0.8125rem; border: none !important;">
                        <colgroup>
                            <col style="width: 34px;">
                            <col style="width: 36px;">
                            <col style="width: auto;">
                            <col style="width: 82px;">
                        </colgroup>
                        <tbody>
                            @foreach($cardData->orders as $subIdx => $subOrder)
                                @php
                                    $subOrderPaid = ($subOrder->pembayaran && $subOrder->pembayaran->status === 'paid');
                                    $subOrderMetode = $subOrder->pembayaran ? strtoupper($subOrder->pembayaran->metode ?? '') : '';
                                    $subOrderTotal = (float) (($subOrder->pembayaran && (float)$subOrder->pembayaran->total_bayar > 0)
                                        ? $subOrder->pembayaran->total_bayar
                                        : ($subOrder->total - ($subOrder->discount_amount ?? 0)));
                                @endphp
                                @if($cardData->is_grouped)
                                    <tr style="border: none !important; border-top: none !important;">
                                        <td colspan="4" class="p-2 {{ $loop->first ? 'rounded-top-3' : '' }} {{ $loop->last ? 'rounded-bottom-3' : '' }}" 
                                            style="background: rgba(255, 255, 255, 0.08); font-size: 0.75rem; cursor: pointer; user-select: none; border: none !important; border-top: none !important; box-shadow: none !important; transition: background 0.15s ease;"
                                            onclick="window.toggleSubOrderCollapse({{ $subOrder->id }})"
                                            title="Klik untuk melipat / membuka rincian pesanan #{{ $subOrder->id }}"
                                            onmouseover="this.style.background='rgba(255, 255, 255, 0.13)'"
                                            onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i id="suborder-chevron-{{ $subOrder->id }}" class="bi bi-chevron-down text-white-50" style="font-size: 0.7rem !important; transition: transform 0.2s ease;"></i>
                                                    <span class="fw-bold {{ $subIdx === 0 ? 'text-white' : 'text-warning' }}" style="font-size: 0.78rem;">
                                                        <i class="bi {{ $subIdx === 0 ? 'bi-receipt text-secondary' : 'bi-plus-circle-fill text-warning' }} me-1"></i>
                                                        {{ $subIdx === 0 ? 'Pesanan Awal' : 'Tambahan' }} <span class="text-white-50 fw-normal">(#{{ $subOrder->id }})</span>
                                                    </span>
                                                    <span class="text-white-50 small" style="font-size: 0.72rem;">
                                                        ({{ count($subOrder->detail_pesanan) }} item)
                                                    </span>
                                                    @if(!empty($subOrder->customer_name) && strtolower(trim($subOrder->customer_name)) !== strtolower(trim($cardData->customer_name)))
                                                        <span class="text-info small ms-1" style="font-size: 0.72rem;">({{ $subOrder->customer_name }})</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-2 ms-auto flex-wrap">
                                                    @if($subOrderPaid)
                                                        <span class="text-success small fw-medium d-inline-flex align-items-center" style="font-size: 0.72rem; gap: 4px;">
                                                            <i class="bi bi-check-circle-fill" style="font-size: 0.75rem; margin: 0 !important; margin-right: 0 !important;"></i><span>Lunas{{ $subOrderMetode ? " ($subOrderMetode)" : '' }}</span>
                                                        </span>
                                                    @else
                                                        <span class="text-danger small fw-semibold d-inline-flex align-items-center" style="font-size: 0.72rem; gap: 4px;">
                                                            <i class="bi bi-exclamation-circle-fill" style="font-size: 0.75rem; margin: 0 !important; margin-right: 0 !important;"></i><span>Belum Bayar</span>
                                                        </span>
                                                    @endif

                                                    <span id="suborder-status-badge-{{ $subOrder->id }}" class="d-inline-flex align-items-center">
                                                        @if($subOrder->status === 'pending')
                                                            <button type="button" 
                                                                    id="suborder-btn-{{ $subOrder->id }}"
                                                                    class="btn btn-sm btn-warning py-0 px-2 rounded-2 fw-semibold d-inline-flex align-items-center justify-content-center shadow-none suborder-btn" 
                                                                    style="font-size: 0.68rem; height: 24px; line-height: 1; gap: 4px !important;" 
                                                                    onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'processing', this)"
                                                                    title="Mulai masak pesanan #{{ $subOrder->id }}">
                                                                <i class="bi bi-fire" style="font-size: 0.68rem; line-height: 1; margin: 0 !important; margin-right: 0 !important;"></i>
                                                                <span>Masak</span>
                                                            </button>
                                                        @elseif($subOrder->status === 'processing')
                                                            <button type="button" 
                                                                    id="suborder-btn-{{ $subOrder->id }}"
                                                                    class="btn btn-sm btn-primary py-0 px-2 rounded-2 fw-semibold d-inline-flex align-items-center justify-content-center shadow-none suborder-btn" 
                                                                    style="font-size: 0.68rem; height: 24px; line-height: 1; gap: 4px !important;" 
                                                                    onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'completed', this)"
                                                                    title="Tandai pesanan #{{ $subOrder->id }} selesai dimasak">
                                                                <i class="bi bi-check2" style="font-size: 0.72rem; line-height: 1; margin: 0 !important; margin-right: 0 !important;"></i>
                                                                <span>Selesai</span>
                                                            </button>
                                                        @elseif($subOrder->status === 'completed')
                                                            <span class="text-success small fw-semibold d-inline-flex align-items-center" style="font-size: 0.68rem; gap: 4px !important;">
                                                                <i class="bi bi-check2-circle" style="font-size: 0.72rem; line-height: 1; margin: 0 !important; margin-right: 0 !important;"></i>
                                                                <span>Selesai</span>
                                                            </span>
                                                        @endif
                                                    </span>

                                                    @php
                                                        $totalQtyInSubOrder = $subOrder->detail_pesanan->sum('jumlah');
                                                        $canSplitSubOrder = !$subOrderPaid && !in_array($subOrder->status, ['cancelled', 'void']) && ($subOrder->detail_pesanan->count() > 1 || $totalQtyInSubOrder > 1);
                                                        $canVoidSubOrder = !$subOrderPaid && $subOrder->status !== 'completed' && !in_array($subOrder->status, ['cancelled', 'void']);
                                                    @endphp
                                                    @if($canSplitSubOrder)
                                                        <button type="button" 
                                                                class="btn btn-sm py-0 px-2 rounded-2 border border-warning border-opacity-40 d-inline-flex align-items-center justify-content-center text-warning shadow-none suborder-btn" 
                                                                style="background: rgba(245, 158, 11, 0.12); font-size: 0.68rem; height: 24px; line-height: 1; gap: 4px !important;" 
                                                                onclick="event.stopPropagation(); window.openSplitModal({{ $subOrder->id }})" 
                                                                title="Pisahkan item dari pesanan #{{ $subOrder->id }}">
                                                            <i class="bi bi-scissors" style="font-size: 0.68rem; line-height: 1; margin: 0 !important; margin-right: 0 !important;"></i>
                                                            <span>Pisah</span>
                                                        </button>
                                                    @endif
                                                    @if($canVoidSubOrder)
                                                        <button type="button" 
                                                                class="btn btn-sm py-0 px-2 rounded-2 border border-danger border-opacity-40 d-inline-flex align-items-center justify-content-center text-danger shadow-none suborder-btn" 
                                                                style="background: rgba(239, 68, 68, 0.12); font-size: 0.68rem; height: 24px; line-height: 1; gap: 4px !important;" 
                                                                onclick="event.stopPropagation(); window.voidOrder({{ $subOrder->id }})" 
                                                                title="Batalkan Pesanan #{{ $subOrder->id }}">
                                                            <i class="bi bi-trash3" style="font-size: 0.68rem; line-height: 1; margin: 0 !important; margin-right: 0 !important;"></i>
                                                            <span>Batal</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                @foreach($subOrder->detail_pesanan as $item)
                                    @php
                                        $itemServed = (bool)($item->is_served ?? false);
                                    @endphp
                                    <tr id="order-item-row-{{ $item->id }}" 
                                        class="{{ $cardData->is_grouped ? 'suborder-items-' . $subOrder->id : '' }} order-item-row {{ $itemServed ? 'item-served' : '' }}" 
                                        style="{{ !$subOrderPaid ? 'background: rgba(248, 113, 113, 0.04); border-left: 3px solid rgba(248, 113, 113, 0.6);' : '' }} {{ $itemServed ? 'opacity: 0.85;' : '' }}; border-bottom: 1px solid #1c2128; transition: all 0.2s ease;">
                                        <!-- Kolom Centang Menu -->
                                        <td class="ps-2 pe-1 py-2 text-center" style="vertical-align: middle;">
                                            <button type="button" 
                                                    class="btn btn-sm p-0 border-0 d-flex align-items-center justify-content-center item-check-btn" 
                                                    id="item-check-btn-{{ $item->id }}"
                                                    data-item-id="{{ $item->id }}"
                                                    data-order-id="{{ $subOrder->id }}"
                                                    data-card-id="{{ $primaryOrder->id }}"
                                                    onclick="event.stopPropagation(); window.toggleItemServed({{ $item->id }}, this)"
                                                    title="{{ $itemServed ? 'Klik untuk batal centang' : 'Klik untuk centang (Siap/Diantar)' }}"
                                                    style="width: 22px; height: 22px; border-radius: 6px; background: {{ $itemServed ? 'rgba(52, 211, 153, 0.2)' : 'rgba(255, 255, 255, 0.04)' }}; border: 1px solid {{ $itemServed ? '#34d399' : '#30363d' }} !important; cursor: pointer; transition: all 0.2s ease;">
                                                <i class="bi {{ $itemServed ? 'bi-check-lg text-success' : 'bi-circle text-white-50' }}" style="font-size: {{ $itemServed ? '0.82rem' : '0.55rem' }}; line-height: 1;"></i>
                                            </button>
                                        </td>
                                        <!-- Kolom Qty -->
                                        <td class="ps-1 pe-2 text-center" style="vertical-align: middle;">
                                            <span class="fw-bold {{ $itemServed ? 'text-success' : 'text-white' }} item-qty-badge" style="font-size: 0.82rem;">{{ $item->jumlah }}x</span>
                                        </td>
                                        <!-- Kolom Nama Menu & Detail -->
                                        <td class="fw-medium py-2 pe-2" style="vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word;">
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <span class="item-name {{ $itemServed ? 'text-secondary text-decoration-line-through' : 'text-white' }}" style="font-size: 0.8125rem; line-height: 1.35;">
                                                    {{ $item->menu->nama_menu ?? 'Menu tidak ditemukan' }}
                                                </span>
                                                @if($itemServed)
                                                    <span class="text-success small ms-1 item-status-badge d-inline-flex align-items-center" style="font-size: 0.7rem; gap: 3px;">
                                                        <i class="bi bi-check2" style="margin: 0 !important; margin-right: 0 !important;"></i><span>Siap</span>
                                                    </span>
                                                @endif
                                            </div>
                                            @if($item->selected_variants)
                                                @php 
                                                    $variants = json_decode($item->selected_variants, true); 
                                                @endphp
                                                @if(is_array($variants) && count($variants) > 0)
                                                    <div class="text-success mt-0.5" style="font-size: 0.7rem;"><i class="bi bi-tags me-1"></i>
                                                        @foreach($variants as $idx => $v)
                                                             {{ isset($v['qty']) && $v['qty'] > 1 ? $v['qty'].'x ' : '' }}{{ $v['name'] }}{{ $idx < count($variants) - 1 ? ', ' : '' }}
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endif
                                            @if($item->catatan)
                                                <div class="text-warning fst-italic mt-0.5" style="font-size: 0.7rem;"><i class="bi bi-chat-text me-1"></i>Catatan: {{ $item->catatan }}</div>
                                            @endif
                                        </td>
                                        <!-- Kolom Subtotal -->
                                        <td class="text-end text-secondary pe-2 text-nowrap py-2" style="vertical-align: middle; font-size: 0.8rem; font-weight: 500;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent pt-3 pb-3 px-3.5 rounded-bottom-4" style="border-top: 1px solid #21262d;">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">Total Tagihan ({{ $cardData->total_items }} Item)</span>
                    <div class="text-end">
                        @if($cardData->total_discount > 0)
                            <span class="text-secondary small text-decoration-line-through d-block" style="font-size: 0.75rem;">Rp {{ number_format($cardData->total_bill + $cardData->total_discount, 0, ',', '.') }}</span>
                            <span class="fw-bold text-white fs-6">Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}</span>
                        @else
                            <span class="fw-bold text-white fs-6">Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}</span>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-secondary small">Status Bayar</span>
                    @if($cardData->all_paid)
                        <span class="text-success fw-semibold small">
                            Lunas
                        </span>
                    @else
                        <span class="text-danger fw-semibold small">
                            {{ $cardData->unpaid_amount < $cardData->total_bill ? 'Belum Lunas (Sisa Rp ' . number_format($cardData->unpaid_amount, 0, ',', '.') . ')' : 'Belum Lunas' }}
                        </span>
                    @endif
                </div>

                @if($cardData->is_grouped && !$cardData->all_paid)
                    @php
                        $unpaidOrderList = $cardData->orders->filter(fn($o) => !($o->pembayaran && $o->pembayaran->status === 'paid'));
                    @endphp
                    @if($unpaidOrderList->isNotEmpty())
                        <div class="d-flex align-items-center justify-content-between text-secondary mb-2.5 small" style="font-size: 0.75rem;">
                            <span><i class="bi bi-info-circle text-warning me-1"></i>Tagihan belum bayar:</span>
                            <span class="text-danger fw-medium">
                                @foreach($unpaidOrderList as $uOrd)
                                    Pesanan #{{ $uOrd->id }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        </div>
                    @endif
                @endif

                @if($cardData->tipe_pesanan === 'dine_in' && !$cardData->all_paid && $cardData->total_uang_diterima > 0)
                    @php
                        // Ambil catatan dari pembayaran pertama yang unpaid
                        $cashNote = '';
                        $cashMode = 'bayar_pas';
                        foreach ($cardData->orders as $ord) {
                            if ($ord->pembayaran && $ord->pembayaran->status !== 'paid' && $ord->pembayaran->metode === 'cash') {
                                $cashNote = $ord->pembayaran->catatan_kembalian ?? '';
                                if (str_contains($cashNote, 'datang ke kasir')) {
                                    $cashMode = 'kasir';
                                }
                                break;
                            }
                        }
                    @endphp
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small">Metode</span>
                        <div class="text-end">
                            <span class="text-warning fw-semibold small">
                                Tunai &bull; {{ $cashMode === 'kasir' ? 'Datang ke Kasir' : 'Bayar di Meja' }}
                            </span>
                        </div>
                    </div>
                    @if(!empty($cashNote))
                        <div class="small text-secondary mb-2.5" style="font-size: 0.76rem;">
                            {{ $cashNote }}
                        </div>
                    @endif
                @endif

                <!-- Action Buttons: Clean 2-row layout with clear visual hierarchy -->
                <div class="d-flex flex-column gap-2 pt-2 border-top" style="border-color: #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Tiket Dapur / Koki -->
                        <a href="{{ route('kasir.order.kitchen', $orderIdsStr) }}" target="_blank" class="btn btn-sm px-2.5 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 text-decoration-none shadow-sm flex-fill" style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.78rem;" title="Cetak rincian pesanan untuk Koki / Barista">
                            <i class="bi bi-journal-text text-secondary"></i> Tiket Dapur
                        </a>

                        @if($cardData->overall_status === 'pending')
                            @php
                                $pendingOrders = $cardData->orders->filter(fn($o) => $o->status === 'pending');
                                $pendingIdsStr = implode(',', $pendingOrders->pluck('id')->toArray());
                            @endphp
                            <button type="button" class="btn btn-primary btn-sm px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-fill" style="font-size: 0.78rem;" onclick="window.updateOrderStatus('{{ $pendingIdsStr ?: $orderIdsStr }}', 'processing', this)">
                                <i class="bi bi-fire"></i> {{ $cardData->is_grouped ? 'Konfirmasi & Masak (Semua)' : 'Konfirmasi & Masak' }}
                            </button>
                        @endif

                        @if($cardData->overall_status === 'processing')
                            @php
                                $processingOrders = $cardData->orders->filter(fn($o) => $o->status === 'processing');
                                $processingIdsStr = implode(',', $processingOrders->pluck('id')->toArray());
                            @endphp
                            <button type="button" 
                                    id="btn-complete-order-{{ $primaryOrder->id }}"
                                    class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-fill" 
                                    style="background-color: rgba(46, 160, 67, 0.15); border: 1px solid rgba(46, 160, 67, 0.35); color: #3fb950; font-size: 0.78rem;"
                                    data-total-items="{{ $totalItemCount }}"
                                    data-served-items="{{ $servedItemCount }}"
                                    data-all-served="{{ $allCardItemsServed ? '1' : '0' }}"
                                    onclick="window.confirmCompleteOrder('{{ $processingIdsStr ?: $orderIdsStr }}', this)">
                                <i class="bi bi-check2-all"></i>
                                <span class="btn-text">
                                    {{ $cardData->tipe_pesanan === 'takeaway' ? 'Selesai & Serahkan' : ($cardData->is_grouped ? 'Selesai Dimasak (Semua)' : 'Selesai Dimasak') }}
                                </span>
                                <span class="badge rounded-2 py-0.5 px-1.5 ms-1 counter-badge" style="background: rgba(46, 160, 67, 0.25); color: #3fb950; font-size: 0.65rem;">
                                    {{ $servedItemCount }}/{{ $totalItemCount }}
                                </span>
                            </button>
                        @endif
                    </div>

                    @if(!$cardData->all_paid)
                        @php
                            $unpaidSubOrdersData = $cardData->orders
                                ->filter(fn($o) => !($o->pembayaran && $o->pembayaran->status === 'paid'))
                                ->values()
                                ->map(function($o, $idx) use ($cardData) {
                                    $oBill = (float) (($o->pembayaran && (float)$o->pembayaran->total_bayar > 0)
                                        ? $o->pembayaran->total_bayar
                                        : ($o->total - ($o->discount_amount ?? 0)));
                                    $itemsSummary = $o->detail_pesanan->map(fn($d) => $d->jumlah . 'x ' . ($d->menu?->nama_menu ?? 'Item'))->take(3)->implode(', ');
                                    if ($o->detail_pesanan->count() > 3) {
                                        $itemsSummary .= ' +' . ($o->detail_pesanan->count() - 3) . ' lainnya';
                                    }
                                    return [
                                        'id' => $o->id,
                                        'label' => ($idx === 0 ? 'Pesanan Awal' : 'Tambahan') . ' (#' . $o->id . ')',
                                        'customer' => $o->customer_name ?: ($cardData->customer_name ?: 'Tamu'),
                                        'total' => (int) $oBill,
                                        'summary' => $itemsSummary
                                    ];
                                    })->values()->toArray();

                            $primaryDetailsJson = $primaryOrder->detail_pesanan->map(fn($d) => [
                                'id' => $d->id,
                                'menu' => ['nama_menu' => $d->menu?->nama_menu ?? 'Item'],
                                'jumlah' => $d->jumlah,
                                'subtotal' => $d->subtotal
                            ])->toJson();

                            $rawMeja = trim((string)($cardData->meja?->nama_meja_atau_nomor ?? ''));
                            $targetMejaLabel = !empty($rawMeja)
                                ? (preg_match('/^meja\b/i', $rawMeja) ? $rawMeja : 'Meja ' . $rawMeja)
                                : ($cardData->tipe_pesanan === 'takeaway' ? 'Takeaway' : 'Dine-In');
                        @endphp

                        <!-- Fitur Pisah, Tambah & Gabung Bon (Split & Merge Order) -->
                        @php
                            $canSplitCard = ($cardData->is_grouped && count($cardData->orders) > 1) || ($totalItemCount > 1 || $allCardDetails->sum('jumlah') > 1);
                        @endphp
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('kasir.pos', array_filter(['tipe' => $cardData->tipe_pesanan, 'id_meja' => $cardData->id_meja])) }}" 
                               class="btn btn-sm px-2.5 py-1.5 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm flex-fill text-decoration-none" 
                               style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.76rem; gap: 6px !important;" 
                               title="Tambah pesanan / menu baru untuk meja ini">
                                <i class="bi bi-plus-circle text-secondary"></i> Tambah Menu
                            </a>
                            @if($canSplitCard)
                                <button type="button" 
                                        class="btn btn-sm px-2.5 py-1.5 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm flex-fill" 
                                        style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.76rem; gap: 6px !important;" 
                                        data-details="{{ $primaryDetailsJson }}"
                                        data-suborders='@json($unpaidSubOrdersData)'
                                        onclick="window.handleCardSplit({{ $primaryOrder->id }}, this)"
                                        title="Pecah atau pisahkan pesanan ke bon baru">
                                    <i class="bi bi-scissors text-secondary"></i> Pisah Bon
                                </button>
                            @endif
                            <button type="button" 
                                    class="btn btn-sm px-2.5 py-1.5 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm flex-fill" 
                                    style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.76rem; gap: 6px !important;" 
                                    onclick="window.openMergeModal({{ $primaryOrder->id }}, '{{ $targetMejaLabel }} ({{ $cardData->customer_name ?: 'Tamu' }})')"
                                    title="Gabungkan pesanan ini ke meja atau pesanan aktif lain">
                                <i class="bi bi-intersect text-secondary"></i> Gabung Meja
                            </button>
                        </div>

                        <!-- Baris Pembayaran / Void -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm px-3 py-2 rounded-3 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-grow-1" style="background-color: #238636; border: 1px solid #2ea043; color: #ffffff; font-size: 0.8rem; box-shadow: 0 2px 6px rgba(35, 134, 54, 0.25);" onclick='window.payGroupOrders("{{ $orderIdsStr }}", {{ $cardData->unpaid_amount }}, {{ $cardData->total_uang_diterima }}, {{ $cardData->total_uang_kembalian }}, @json($unpaidSubOrdersData))'>
                                <i class="bi bi-cash-stack"></i> Terima Bayar
                            </button>
                            @if($cardData->can_void)
                                <button type="button" class="btn btn-sm px-2.5 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="background-color: transparent; border: 1px solid rgba(248, 113, 113, 0.3); color: #f87171; font-size: 0.78rem; min-width: 72px;" onclick='window.voidOrder({{ $cardData->primary_void_id }}, @json($cardData->void_order_options))' title="Void / Batalkan Pesanan Kasir">
                                    <i class="bi bi-trash3"></i> Void
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <a href="{{ route('kasir.pos', array_filter(['tipe' => $cardData->tipe_pesanan, 'id_meja' => $cardData->id_meja])) }}" 
                               class="btn btn-sm px-2.5 py-1.5 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm w-100 text-decoration-none" 
                               style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.76rem; gap: 6px !important;" 
                               title="Tambah pesanan / menu tambahan untuk meja ini">
                                <i class="bi bi-plus-circle text-secondary"></i> Tambah Menu
                            </a>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm flex-fill" style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.78rem; gap: 6px !important;" onclick="window.printThermal({{ $primaryOrder->id }})" title="Cetak struk ke printer thermal / terminal POS">
                                <i class="bi bi-printer text-secondary"></i> Cetak Thermal
                            </button>
                            <a href="{{ route('kasir.order.receipt', $orderIdsStr) }}" target="_blank" class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center shadow-sm flex-fill text-decoration-none" style="background-color: #21262d; border: 1px solid #30363d; color: #e6edf3; font-size: 0.78rem; gap: 6px !important;" title="Buka atau cetak struk kasir digital">
                                <i class="bi bi-file-earmark-text text-secondary"></i> Cetak Struk
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12 text-center py-5">
        <div class="d-inline-block text-white p-4 rounded-circle mb-3">
            <i class="bi bi-receipt text-white-50" style="font-size: 3rem;"></i>
        </div>
        <h5 class="fw-bold text-white-50">Belum Ada Pesanan Masuk</h5>
        <p class="text-white-50">Pesanan dari konsumen akan muncul di sini.</p>
    </div>
@endforelse