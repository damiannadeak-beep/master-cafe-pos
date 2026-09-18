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
    @endphp
    <div class="col-md-6 col-lg-4 order-card-item" id="order-card-{{ $primaryOrder->id }}" data-order-ids="{{ $orderIdsStr }}" data-total="{{ $cardTotalPay }}" data-uang-diterima="{{ $cardUangDiterima }}" data-uang-kembalian="{{ $cardUangKembalian }}">
        <div class="card shadow-sm border-0 h-100 rounded-4">
            <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                <div>
                    <h6 class="fw-bold mb-1 d-flex align-items-center flex-wrap gap-1">
                        @if($cardData->is_grouped)
                            <span>Pesanan #{{ $cardData->order_ids_string }}</span>
                            <span class="badge bg-info bg-opacity-25 text-info border border-info px-2 py-0.5 rounded-pill" style="font-size: 0.7rem;">
                                <i class="bi bi-layers-fill me-1"></i>Gabungan ({{ count($cardData->orders) }}x Order)
                            </span>
                            <button type="button" 
                                    class="btn btn-sm py-0 px-2 rounded-pill fw-medium d-inline-flex align-items-center gap-1 text-decoration-none border border-info border-opacity-50 text-info" 
                                    style="background: rgba(13, 202, 240, 0.12); font-size: 0.65rem; height: 20px; line-height: 1;" 
                                    onclick="window.toggleAllSubOrders('order-card-{{ $primaryOrder->id }}', {{ json_encode($cardData->order_ids) }})" 
                                    title="Buka / Lipat Semua Rincian Pesanan Meja Ini">
                                <i class="bi bi-arrows-collapse" style="font-size: 0.7rem !important; line-height: 1;"></i>
                                <span>Lipat / Buka</span>
                            </button>
                        @else
                            <span>Pesanan #{{ $primaryOrder->id }}</span>
                        @endif
                    </h6>
                    <small class="text-white-50"><i class="bi bi-clock me-1"></i>{{ $cardData->latest_created_at->format('H:i') }} WIB</small>
                </div>
                <div class="text-end">
                    @if($cardData->overall_status === 'pending')
                        <span class="badge bg-warning text-white"><i class="bi bi-hourglass-split"></i> PENDING</span>
                        @if($cardData->is_grouped)
                            <small class="d-block text-warning fw-semibold mt-0.5" style="font-size: 0.68rem;">+ Menu Baru Masuk</small>
                        @endif
                    @elseif($cardData->overall_status === 'processing')
                        <span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>
                    @elseif($cardData->overall_status === 'completed')
                        <span class="badge bg-success"><i class="bi bi-check-circle"></i> SIAP DIHIDANGKAN</span>
                    @endif
                </div>
            </div>
            
            <div class="card-body text-white bg-opacity-50">
                <div class="p-3 rounded-3 mb-3" style="background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.15); width: 36px; height: 36px; color: #c08e5c;">
                                <i class="{{ $cardData->tipe_pesanan == 'takeaway' ? 'bi bi-bag-check-fill' : 'bi bi-shop' }}"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-white fs-6">
                                    {{ $cardData->tipe_pesanan == 'takeaway' ? 'Takeaway (Bungkus)' : 'Dine-In (Makan di Tempat)' }}
                                </h6>
                                <small class="text-secondary" style="font-size: 0.8rem;">
                                    @if($cardData->tipe_pesanan == 'takeaway')
                                        <span class="badge rounded-pill text-bg-warning px-2">Bawa Pulang</span>
                                    @elseif($cardData->tipe_pesanan == 'dine_in' && !$cardData->id_meja)
                                        <span class="text-danger fw-bold"><i class="bi bi-geo-alt"></i> Belum Pilih Meja</span>
                                    @else
                                        <span class="badge rounded-pill text-bg-secondary px-2">Meja {{ $cardData->meja->nama_meja_atau_nomor ?? '?' }}</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Detail Pemesan & No WA -->
                    <div class="pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-1">
                        <div>
                            <div class="small text-white fw-bold">
                                <i class="bi bi-person-fill text-warning me-1"></i> Pemesan: <span class="text-white">{{ $cardData->customer_name }}</span>
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
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                <i class="bi bi-whatsapp me-1"></i> Chat WA
                            </a>
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-dark text-white border-secondary table-sm table-borderless mb-0" style="font-size: 0.8125rem;">
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
                                    <tr class="table-active">
                                        <td colspan="3" class="py-1 px-2 rounded-2" 
                                            style="background: rgba(255, 255, 255, 0.07); font-size: 0.72rem; cursor: pointer; user-select: none; transition: background 0.15s ease;"
                                            onclick="window.toggleSubOrderCollapse({{ $subOrder->id }})"
                                            title="Klik untuk melipat / membuka rincian pesanan #{{ $subOrder->id }}"
                                            onmouseover="this.style.background='rgba(255, 255, 255, 0.13)'"
                                            onmouseout="this.style.background='rgba(255, 255, 255, 0.07)'">
                                            <div class="d-flex justify-content-between align-items-center flex-nowrap gap-1">
                                                <div class="d-flex align-items-center gap-1.5 text-truncate me-1">
                                                    <i id="suborder-chevron-{{ $subOrder->id }}" class="bi bi-chevron-down text-white-50 flex-shrink-0" style="font-size: 0.65rem !important; transition: transform 0.2s ease;"></i>
                                                    <span class="fw-bold {{ $subIdx === 0 ? 'text-white' : 'text-warning' }} text-truncate" style="font-size: 0.72rem;">
                                                        <i class="bi {{ $subIdx === 0 ? 'bi-receipt text-secondary' : 'bi-plus-circle-fill text-warning' }} me-1"></i>
                                                        {{ $subIdx === 0 ? 'Pesanan Awal (#' . $subOrder->id . ')' : 'Tambahan (#' . $subOrder->id . ')' }}
                                                    </span>
                                                    <span class="badge bg-secondary bg-opacity-25 text-white-50 py-0 px-1 rounded" style="font-size: 0.62rem;">
                                                        {{ count($subOrder->detail_pesanan) }} item
                                                    </span>
                                                    @if(!empty($subOrder->customer_name) && strtolower(trim($subOrder->customer_name)) !== strtolower(trim($cardData->customer_name)))
                                                        <span class="badge bg-dark border border-secondary text-info py-0 px-1.5" style="font-size: 0.62rem; font-weight: normal;">{{ $subOrder->customer_name }}</span>
                                                    @endif
                                                    @php
                                                        $canVoidSubOrder = !$subOrderPaid && $subOrder->status !== 'completed' && !in_array($subOrder->status, ['cancelled', 'void']);
                                                    @endphp
                                                    @if($canVoidSubOrder)
                                                        <button type="button" 
                                                                class="btn btn-sm py-0 px-1.5 rounded-pill border-0 d-inline-flex align-items-center gap-1 ms-1 text-decoration-none" 
                                                                style="background: rgba(239, 68, 68, 0.18); color: #ff8b8b; font-size: 0.62rem; height: 19px; line-height: 1;" 
                                                                onclick="event.stopPropagation(); window.voidOrder({{ $subOrder->id }})" 
                                                                title="Batalkan Pesanan Tambahan #{{ $subOrder->id }}">
                                                            <i class="bi bi-trash3" style="font-size: 0.68rem !important; line-height: 1;"></i>
                                                            <span>Batal</span>
                                                        </button>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                    @if($subOrderPaid)
                                                        <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background: rgba(46, 160, 67, 0.2); color: #3fb950; border: 1px solid rgba(46, 160, 67, 0.4); font-size: 0.64rem;">
                                                            <i class="bi bi-check-circle-fill me-1"></i>Lunas{{ $subOrderMetode ? " ($subOrderMetode)" : '' }}
                                                        </span>
                                                    @else
                                                        <span class="badge rounded-pill px-2 py-0.5 fw-semibold" style="background: rgba(220, 53, 69, 0.2); color: #ff7b72; border: 1px solid rgba(220, 53, 69, 0.35); font-size: 0.64rem;">
                                                            <i class="bi bi-exclamation-circle-fill me-1"></i>Belum Bayar
                                                        </span>
                                                    @endif

                                                    @if($subOrder->status === 'pending')
                                                        <button type="button" 
                                                                class="badge bg-warning text-dark py-0.5 px-2 rounded-pill border-0 d-inline-flex align-items-center gap-1 text-decoration-none shadow-none" 
                                                                style="font-size: 0.62rem; cursor: pointer;"
                                                                onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'processing', this)"
                                                                title="Mulai masak pesanan #{{ $subOrder->id }}">
                                                            <i class="bi bi-fire"></i> Masak
                                                        </button>
                                                    @elseif($subOrder->status === 'processing')
                                                        <button type="button" 
                                                                class="badge bg-primary text-white py-0.5 px-2 rounded-pill border-0 d-inline-flex align-items-center gap-1 text-decoration-none shadow-none" 
                                                                style="font-size: 0.62rem; cursor: pointer;"
                                                                onclick="event.stopPropagation(); window.updateSingleOrderStatus({{ $subOrder->id }}, 'completed', this)"
                                                                title="Tandai pesanan #{{ $subOrder->id }} selesai dimasak">
                                                            <i class="bi bi-check2"></i> Selesai
                                                        </button>
                                                    @elseif($subOrder->status === 'completed')
                                                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 py-0.5 px-1.5 rounded-pill d-inline-flex align-items-center gap-1" style="font-size: 0.62rem;">
                                                            <i class="bi bi-check2-circle"></i> Selesai
                                                        </span>
                                                    @endif
                                                    <span class="text-white-50 ms-0.5" style="font-size: 0.65rem;">{{ $subOrder->created_at->format('H:i') }}</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                                @foreach($subOrder->detail_pesanan as $item)
                                    <tr class="{{ $cardData->is_grouped ? 'suborder-items-' . $subOrder->id : '' }}" style="{{ !$subOrderPaid ? 'background: rgba(220, 53, 69, 0.04); border-left: 3px solid rgba(220, 53, 69, 0.6);' : '' }}">
                                        <td class="text-white-50 ps-2" style="width: 28px; vertical-align: top; font-size: 0.78rem;">{{ $item->jumlah }}x</td>
                                        <td class="fw-medium py-1">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="text-white" style="font-size: 0.8125rem;">{{ $item->menu->nama_menu ?? 'Menu tidak ditemukan' }}</span>
                                            </div>
                                            @if($item->selected_variants)
                                                @php 
                                                    $variants = json_decode($item->selected_variants, true); 
                                                @endphp
                                                @if(is_array($variants) && count($variants) > 0)
                                                    <div class="text-success" style="font-size: 0.7rem;"><i class="bi bi-tags me-1"></i>
                                                        @foreach($variants as $idx => $v)
                                                            {{ isset($v['qty']) && $v['qty'] > 1 ? $v['qty'].'x ' : '' }}{{ $v['name'] }}{{ $idx < count($variants) - 1 ? ', ' : '' }}
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @endif
                                            @if($item->catatan)
                                                <div class="text-danger fst-italic" style="font-size: 0.7rem;"><i class="bi bi-chat-text me-1"></i>Catatan: {{ $item->catatan }}</div>
                                            @endif
                                        </td>
                                        <td class="text-end text-white-50 pe-2 text-nowrap py-1" style="vertical-align: top; font-size: 0.78rem;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-transparent pt-2.5 pb-3 rounded-bottom-4">
                <div class="d-flex justify-content-between align-items-center mb-2.5">
                    <span class="text-white-50" style="font-size: 0.78rem;">Total Tagihan ({{ $cardData->total_items }} Item)</span>
                    <div class="text-end">
                        @if($cardData->total_discount > 0)
                            <span class="text-danger small text-decoration-line-through d-block" style="font-size: 0.75rem;">Rp {{ number_format($cardData->total_bill + $cardData->total_discount, 0, ',', '.') }}</span>
                            <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem;">Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}</h6>
                        @else
                            <h6 class="fw-bold text-primary mb-0" style="font-size: 0.95rem;">Rp {{ number_format($cardData->total_bill, 0, ',', '.') }}</h6>
                        @endif
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small">Status Bayar</span>
                    @if($cardData->all_paid)
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2.5 py-1 rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i> Lunas Semua
                        </span>
                    @else
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2.5 py-1 rounded-pill">
                            <i class="bi bi-x-circle-fill me-1"></i> Belum Lunas (Rp {{ number_format($cardData->unpaid_amount, 0, ',', '.') }})
                        </span>
                    @endif
                </div>

                @if($cardData->is_grouped && !$cardData->all_paid)
                    @php
                        $unpaidOrderList = $cardData->orders->filter(fn($o) => !($o->pembayaran && $o->pembayaran->status === 'paid'));
                    @endphp
                    @if($unpaidOrderList->isNotEmpty())
                        <div class="px-2 py-1.5 mb-2 rounded-2 d-flex align-items-center justify-content-between" style="background: rgba(220, 53, 69, 0.08); border: 1px dashed rgba(220, 53, 69, 0.35); font-size: 0.75rem;">
                            <span class="text-white-50">
                                <i class="bi bi-info-circle text-danger me-1"></i>Tagihan pending:
                            </span>
                            <span class="badge bg-danger bg-opacity-75 text-white fw-semibold" style="font-size: 0.7rem;">
                                @foreach($unpaidOrderList as $uOrd)
                                    Pesanan #{{ $uOrd->id }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        </div>
                    @endif
                @endif

                @if($cardData->tipe_pesanan === 'dine_in' && !$cardData->all_paid && $cardData->total_uang_diterima > 0)
                    <div class="p-2 mb-3 rounded-3" style="background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.35);">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-cash-stack me-1"></i>BAYAR TUNAI DI MEJA</span>
                            <small class="text-warning fw-bold">Siapkan Uang</small>
                        </div>
                        @if($cardData->total_uang_kembalian > 0)
                            <div class="small text-white fw-bold">
                                ⚠️ Siapkan Kembalian: <span class="text-warning fs-6">Rp {{ number_format($cardData->total_uang_kembalian, 0, ',', '.') }}</span>
                            </div>
                            <div class="small text-white-50">
                                (Tamu bayar uang: Rp {{ number_format($cardData->total_uang_diterima, 0, ',', '.') }})
                            </div>
                        @else
                            <div class="small text-success fw-bold">
                                ✅ Uang Pas: Rp {{ number_format($cardData->total_uang_diterima, 0, ',', '.') }} (Tanpa Kembalian)
                            </div>
                        @endif
                    </div>
                @endif

                <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                    <!-- Tombol Cetak Tiket Dapur / Koki -->
                    <a href="{{ route('kasir.order.kitchen', $orderIdsStr) }}" target="_blank" class="btn btn-sm btn-outline-info flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" title="Cetak rincian pesanan untuk Koki / Barista">
                        <i class="bi bi-journal-text me-1"></i> Tiket Dapur / Koki
                    </a>

                    @if($cardData->overall_status === 'pending')
                        @php
                            $pendingOrders = $cardData->orders->filter(fn($o) => $o->status === 'pending');
                            $pendingIdsStr = implode(',', $pendingOrders->pluck('id')->toArray());
                        @endphp
                        <button type="button" class="btn btn-sm btn-primary flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus('{{ $pendingIdsStr ?: $orderIdsStr }}', 'processing', this)">
                            <i class="bi bi-fire me-1"></i> {{ $cardData->is_grouped ? 'Konfirmasi & Dimasak (Semua)' : 'Konfirmasi & Dimasak' }}
                        </button>
                    @endif
                    
                    @if($cardData->overall_status === 'processing')
                        @php
                            $processingOrders = $cardData->orders->filter(fn($o) => $o->status === 'processing');
                            $processingIdsStr = implode(',', $processingOrders->pluck('id')->toArray());
                        @endphp
                        <button type="button" class="btn btn-sm btn-success flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus('{{ $processingIdsStr ?: $orderIdsStr }}', 'completed', this)">
                            <i class="bi bi-check2-all me-1"></i> {{ $cardData->tipe_pesanan === 'takeaway' ? 'Selesai & Serahkan Pesanan' : ($cardData->is_grouped ? 'Selesai Dimasak (Semua)' : 'Selesai Dimasak') }}
                        </button>
                    @endif

                    @if(!$cardData->all_paid)
                        <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.payGroupOrders('{{ $orderIdsStr }}', {{ $cardData->unpaid_amount }}, {{ $cardData->total_uang_diterima }}, {{ $cardData->total_uang_kembalian }})">
                            <i class="bi bi-cash-stack me-1"></i> Terima Bayar
                        </button>
                        @if($cardData->can_void)
                            <button type="button" class="btn btn-sm btn-danger flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick='window.voidOrder({{ $cardData->primary_void_id }}, @json($cardData->void_order_options))' title="Void / Batalkan Pesanan Kasir">
                                <i class="bi bi-trash me-1"></i> Void
                            </button>
                        @endif
                    @else
                        @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                        @if($printerActive)
                            <button type="button" class="btn btn-sm btn-info text-white flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.printThermal({{ $primaryOrder->id }})">
                                <i class="bi bi-printer me-1"></i> Cetak Thermal
                            </button>
                        @endif
                        <a href="{{ route('kasir.order.receipt', $orderIdsStr) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center">
                            <i class="bi bi-file-earmark-text me-1"></i> Struk Kasir
                        </a>
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