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
    @endphp
    <div class="col-md-6 col-lg-4 order-card-item" id="order-card-{{ $primaryOrder->id }}" data-order-ids="{{ $orderIdsStr }}" data-total="{{ $cardTotalPay }}" data-uang-diterima="{{ $cardUangDiterima }}" data-uang-kembalian="{{ $cardUangKembalian }}" data-total-items="{{ $totalItemCount }}" data-served-items="{{ $servedItemCount }}">
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
                    <h6 class="fw-bold mb-0.5 text-white d-flex align-items-center flex-wrap gap-1.5 fs-6">
                        @if($cardData->is_grouped)
                            <span>Pesanan #{{ $cardData->order_ids_string }}</span>
                            <span class="badge rounded-2 px-2 py-0.5" style="background: rgba(56, 189, 248, 0.12); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25); font-size: 0.68rem;">
                                <i class="bi bi-layers-fill me-1"></i>Gabungan ({{ count($cardData->orders) }}x Order)
                            </span>
                            <button type="button" 
                                    class="btn btn-sm py-0 px-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1 text-decoration-none" 
                                    style="background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8; font-size: 0.65rem; height: 20px; line-height: 1;" 
                                    onclick="window.toggleAllSubOrders('order-card-{{ $primaryOrder->id }}', {{ json_encode($cardData->order_ids) }})" 
                                    title="Buka / Lipat Semua Rincian Pesanan Meja Ini">
                                <i class="bi bi-arrows-collapse" style="font-size: 0.7rem !important; line-height: 1;"></i>
                                <span>Lipat / Buka</span>
                            </button>
                        @else
                            <span>Pesanan #{{ $primaryOrder->id }}</span>
                        @endif
                    </h6>
                    <small class="text-secondary" style="font-size: 0.75rem;"><i class="bi bi-clock me-1.5 opacity-75"></i> {{ $cardData->latest_created_at->format('H:i') }} WIB</small>
                </div>
                <div class="text-end">
                    @if($cardData->overall_status === 'pending')
                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.72rem;">
                            <i class="bi bi-hourglass-split me-1"></i>PENDING
                        </span>
                        @if($cardData->is_grouped)
                            <small class="d-block fw-semibold mt-0.5" style="color: #fbbf24; font-size: 0.68rem;">+ Menu Baru Masuk</small>
                        @endif
                    @elseif($cardData->overall_status === 'processing')
                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); font-size: 0.72rem;">
                            <i class="bi bi-fire me-1"></i>DIMASAK
                        </span>
                    @elseif($cardData->overall_status === 'completed')
                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); font-size: 0.72rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>SIAP SAJI
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="card-body p-3.5 text-white">
                <div class="p-3 rounded-3 mb-3" style="background-color: #0e1217; border: 1px solid #21262d;">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="rounded-3 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.12); width: 34px; height: 34px; color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.25);">
                                <i class="{{ $cardData->tipe_pesanan == 'takeaway' ? 'bi bi-bag-check-fill' : 'bi bi-shop' }}"></i>
                            </div>
                            <div>
                                <div class="fw-semibold text-white small" style="font-size: 0.85rem;">
                                    {{ $cardData->tipe_pesanan == 'takeaway' ? 'Takeaway (Bungkus)' : 'Dine-In (Makan di Tempat)' }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($cardData->tipe_pesanan == 'takeaway')
                                <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); font-size: 0.72rem;">
                                    <i class="bi bi-bag me-1"></i>Bawa Pulang
                                </span>
                            @elseif($cardData->tipe_pesanan == 'dine_in' && !$cardData->id_meja)
                                <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(248, 113, 113, 0.12); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.25); font-size: 0.72rem;">
                                    <i class="bi bi-geo-alt me-1"></i>Belum Pilih Meja
                                </span>
                            @else
                                @php
                                    $rawMejaName = trim((string)($cardData->meja->nama_meja_atau_nomor ?? '?'));
                                    $labelMeja = preg_match('/^meja\b/i', $rawMejaName) ? $rawMejaName : 'Meja ' . $rawMejaName;
                                @endphp
                                <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3); font-size: 0.75rem;">
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
                    <table class="table table-dark text-white border-secondary table-sm table-borderless mb-0" style="table-layout: fixed; width: 100%; font-size: 0.8125rem;">
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
                                    <tr class="table-active" style="{{ $subIdx > 0 ? 'border-top: 8px solid transparent;' : '' }}">
                                        <td colspan="4" class="p-2 rounded-3" 
                                            style="background: rgba(255, 255, 255, 0.08); font-size: 0.75rem; cursor: pointer; user-select: none; transition: background 0.15s ease;"
                                            onclick="window.toggleSubOrderCollapse({{ $subOrder->id }})"
                                            title="Klik untuk melipat / membuka rincian pesanan #{{ $subOrder->id }}"
                                            onmouseover="this.style.background='rgba(255, 255, 255, 0.13)'"
                                            onmouseout="this.style.background='rgba(255, 255, 255, 0.08)'">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i id="suborder-chevron-{{ $subOrder->id }}" class="bi bi-chevron-down text-white-50" style="font-size: 0.7rem !important; transition: transform 0.2s ease;"></i>
                                                    <span class="fw-bold {{ $subIdx === 0 ? 'text-white' : 'text-warning' }}" style="font-size: 0.75rem;">
                                                        <i class="bi {{ $subIdx === 0 ? 'bi-receipt text-secondary' : 'bi-plus-circle-fill text-warning' }} me-1"></i>
                                                        {{ $subIdx === 0 ? 'Pesanan Awal' : 'Tambahan' }} <span class="text-white-50 fw-normal">(#{{ $subOrder->id }})</span>
                                                    </span>
                                                    <span class="badge bg-secondary bg-opacity-50 text-white-50 py-0.5 px-1.5 rounded" style="font-size: 0.65rem;">
                                                        {{ count($subOrder->detail_pesanan) }} item
                                                    </span>
                                                    @if(!empty($subOrder->customer_name) && strtolower(trim($subOrder->customer_name)) !== strtolower(trim($cardData->customer_name)))
                                                        <span class="badge bg-dark border border-secondary text-info py-0.5 px-1.5 rounded" style="font-size: 0.65rem;">{{ $subOrder->customer_name }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-1.5 ms-auto flex-wrap">
                                                    @if($subOrderPaid)
                                                        <span class="badge rounded-2 px-2 py-1 fw-semibold" style="background: rgba(46, 160, 67, 0.2); color: #3fb950; border: 1px solid rgba(46, 160, 67, 0.4); font-size: 0.65rem;">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Lunas{{ $subOrderMetode ? " ($subOrderMetode)" : '' }}
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-2 px-2 py-1 fw-semibold" style="background: rgba(220, 53, 69, 0.2); color: #ff7b72; border: 1px solid rgba(220, 53, 69, 0.35); font-size: 0.65rem;">
                                                            <i class="bi bi-exclamation-circle-fill me-1"></i>Belum Bayar
                                                        </span>
                                                    @endif

                                                    @if($subOrder->status === 'pending')
                                                        <button type="button" 
                                                                class="badge bg-warning text-dark py-1 px-2 rounded-pill border-0 d-inline-flex align-items-center gap-1 text-decoration-none shadow-none fw-bold" 
                                                                style="font-size: 0.65rem; cursor: pointer;"
                                                                onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'processing', this)"
                                                                title="Mulai masak pesanan #{{ $subOrder->id }}">
                                                            <i class="bi bi-fire"></i> Masak
                                                        </button>
                                                    @elseif($subOrder->status === 'processing')
                                                        <button type="button" 
                                                                class="badge bg-primary text-white py-1 px-2 rounded-pill border-0 d-inline-flex align-items-center gap-1 text-decoration-none shadow-none fw-bold" 
                                                                style="font-size: 0.65rem; cursor: pointer;"
                                                                onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'completed', this)"
                                                                title="Tandai pesanan #{{ $subOrder->id }} selesai dimasak">
                                                            <i class="bi bi-check2"></i> Selesai
                                                        </button>
                                                    @elseif($subOrder->status === 'completed')
                                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-1 px-2 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.65rem;">
                                                            <i class="bi bi-check2-circle"></i> Selesai
                                                        </span>
                                                    @endif

                                                    @php
                                                        $canVoidSubOrder = !$subOrderPaid && $subOrder->status !== 'completed' && !in_array($subOrder->status, ['cancelled', 'void']);
                                                    @endphp
                                                    @if($canVoidSubOrder)
                                                        <button type="button" 
                                                                class="btn btn-sm py-0.5 px-1.5 rounded border border-danger border-opacity-25 d-inline-flex align-items-center gap-1 text-danger" 
                                                                style="background: rgba(239, 68, 68, 0.15); font-size: 0.65rem; height: 21px;" 
                                                                onclick="event.stopPropagation(); window.voidOrder({{ $subOrder->id }})" 
                                                                title="Batalkan Pesanan #{{ $subOrder->id }}">
                                                            <i class="bi bi-trash3"></i>
                                                            <span class="d-none d-sm-inline">Batal</span>
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
                                                <i class="bi {{ $itemServed ? 'bi-check-lg text-success' : 'bi-circle text-white-50' }}" style="font-size: {{ $itemServed ? '1rem' : '0.6rem' }};"></i>
                                            </button>
                                        </td>
                                        <!-- Kolom Qty -->
                                        <td class="ps-1 pe-2 text-center" style="vertical-align: middle; font-size: 0.8rem;">
                                            <span class="badge rounded px-1.5 py-0.5 item-qty-badge {{ $itemServed ? 'text-success fw-bold' : 'text-white' }}" style="background-color: rgba(255, 255, 255, 0.05); font-size: 0.75rem;">{{ $item->jumlah }}x</span>
                                        </td>
                                        <!-- Kolom Nama Menu & Detail -->
                                        <td class="fw-medium py-2 pe-2" style="vertical-align: middle; word-wrap: break-word; overflow-wrap: break-word;">
                                            <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                <span class="item-name {{ $itemServed ? 'text-secondary text-decoration-line-through' : 'text-white' }}" style="font-size: 0.8125rem; line-height: 1.35;">
                                                    {{ $item->menu->nama_menu ?? 'Menu tidak ditemukan' }}
                                                </span>
                                                @if($itemServed)
                                                    <span class="badge bg-success bg-opacity-25 text-success py-0 px-1 rounded-pill item-status-badge" style="font-size: 0.62rem;">
                                                        <i class="bi bi-check2"></i> Siap
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

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-secondary small">Status Bayar</span>
                    @if($cardData->all_paid)
                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(52, 211, 153, 0.12); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.25); font-size: 0.72rem;">
                            <i class="bi bi-check-circle-fill me-1"></i> Lunas Semua
                        </span>
                    @else
                        <span class="badge rounded-2 px-2.5 py-1 fw-medium" style="background-color: rgba(248, 113, 113, 0.12); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.25); font-size: 0.72rem;">
                            <i class="bi bi-x-circle me-1"></i> Belum Lunas (Rp {{ number_format($cardData->unpaid_amount, 0, ',', '.') }})
                        </span>
                    @endif
                </div>

                @if($cardData->is_grouped && !$cardData->all_paid)
                    @php
                        $unpaidOrderList = $cardData->orders->filter(fn($o) => !($o->pembayaran && $o->pembayaran->status === 'paid'));
                    @endphp
                    @if($unpaidOrderList->isNotEmpty())
                        <div class="px-2.5 py-2 mb-2.5 rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-1.5" style="background: rgba(248, 113, 113, 0.08); border: 1px dashed rgba(248, 113, 113, 0.35); font-size: 0.75rem;">
                            <span class="text-white-50">
                                <i class="bi bi-info-circle text-danger me-1"></i>Tagihan pending:
                            </span>
                            <span class="badge bg-danger bg-opacity-75 text-white fw-semibold px-2 py-1" style="font-size: 0.72rem;">
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
                    <div class="p-2 mb-3 rounded-3" style="background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.35);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-cash-stack me-1"></i>BAYAR TUNAI</span>
                            @if($cashMode === 'kasir')
                                <span class="badge bg-info bg-opacity-25 text-info border border-info" style="font-size: 0.7rem;"><i class="bi bi-shop me-1"></i>Datang ke Kasir</span>
                            @else
                                <span class="badge bg-success bg-opacity-25 text-success border border-success" style="font-size: 0.7rem;"><i class="bi bi-cash-coin me-1"></i>Bayar di Meja</span>
                            @endif
                        </div>
                        @if(!empty($cashNote))
                            <div class="small text-white-50 mt-1" style="font-size: 0.78rem;">
                                <i class="bi bi-chat-left-text me-1"></i> {{ $cashNote }}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Action Buttons: Clean 2-row layout with clear visual hierarchy -->
                <div class="d-flex flex-column gap-2 pt-2 border-top" style="border-color: #21262d !important;">
                    <div class="d-flex align-items-center gap-2">
                        <!-- Tiket Dapur / Koki -->
                        <a href="{{ route('kasir.order.kitchen', $orderIdsStr) }}" target="_blank" class="btn btn-sm px-2.5 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 text-decoration-none shadow-sm flex-fill" style="background-color: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8; font-size: 0.78rem;" title="Cetak rincian pesanan untuk Koki / Barista">
                            <i class="bi bi-journal-text"></i> Tiket Dapur
                        </a>

                        @if($cardData->overall_status === 'pending')
                            @php
                                $pendingOrders = $cardData->orders->filter(fn($o) => $o->status === 'pending');
                                $pendingIdsStr = implode(',', $pendingOrders->pluck('id')->toArray());
                            @endphp
                            <button type="button" class="btn btn-primary btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-fill" style="font-size: 0.78rem;" onclick="window.updateOrderStatus('{{ $pendingIdsStr ?: $orderIdsStr }}', 'processing', this)">
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
                                    style="background-color: rgba(52, 211, 153, 0.15); border: 1px solid rgba(52, 211, 153, 0.3); color: #34d399; font-size: 0.78rem;"
                                    data-total-items="{{ $totalItemCount }}"
                                    data-served-items="{{ $servedItemCount }}"
                                    data-all-served="{{ $allCardItemsServed ? '1' : '0' }}"
                                    onclick="window.confirmCompleteOrder('{{ $processingIdsStr ?: $orderIdsStr }}', this)">
                                <i class="bi bi-check2-all"></i>
                                <span class="btn-text">
                                    {{ $cardData->tipe_pesanan === 'takeaway' ? 'Selesai & Serahkan' : ($cardData->is_grouped ? 'Selesai Dimasak (Semua)' : 'Selesai Dimasak') }}
                                </span>
                                <span class="badge rounded-2 py-0.5 px-1.5 ms-1 counter-badge" style="background: rgba(52, 211, 153, 0.25); color: #34d399; font-size: 0.65rem;">
                                    {{ $servedItemCount }}/{{ $totalItemCount }}
                                </span>
                            </button>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
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
                            @endphp
                            <button type="button" class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-grow-1" style="background-color: rgba(52, 211, 153, 0.12); border: 1px solid rgba(52, 211, 153, 0.25); color: #34d399; font-size: 0.78rem;" onclick='window.payGroupOrders("{{ $orderIdsStr }}", {{ $cardData->unpaid_amount }}, {{ $cardData->total_uang_diterima }}, {{ $cardData->total_uang_kembalian }}, @json($unpaidSubOrdersData))'>
                                <i class="bi bi-cash-stack"></i> Terima Bayar
                            </button>
                            @if($cardData->can_void)
                                <button type="button" class="btn btn-sm px-2.5 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm" style="background-color: rgba(248, 113, 113, 0.1); border: 1px solid rgba(248, 113, 113, 0.25); color: #f87171; font-size: 0.78rem; min-width: 72px;" onclick='window.voidOrder({{ $cardData->primary_void_id }}, @json($cardData->void_order_options))' title="Void / Batalkan Pesanan Kasir">
                                    <i class="bi bi-trash3"></i> Void
                                </button>
                            @endif
                        @else
                            @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                            @if($printerActive)
                                <button type="button" class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-fill" style="background-color: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); color: #38bdf8; font-size: 0.78rem;" onclick="window.printThermal({{ $primaryOrder->id }})">
                                    <i class="bi bi-printer"></i> Cetak Thermal
                                </button>
                            @endif
                            <a href="{{ route('kasir.order.receipt', $orderIdsStr) }}" target="_blank" class="btn btn-sm px-3 py-2 rounded-3 fw-medium d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm flex-fill" style="background-color: rgba(192, 142, 92, 0.12); border: 1px solid rgba(192, 142, 92, 0.25); color: #c08e5c; font-size: 0.78rem;">
                                <i class="bi bi-file-earmark-text"></i> Struk Kasir
                            </a>
                        @endif
                    </div>
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