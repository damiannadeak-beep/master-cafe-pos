@forelse($orders as $order)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm border-0 h-100 rounded-4">
                    <div class="card-header bg-transparent py-3 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                        <div>
                            <h6 class="fw-bold mb-1">Pesanan #{{ $order->id }}</h6>
                            <small class="text-white-50"><i class="bi bi-clock me-1"></i>{{ $order->created_at->format('H:i') }} WIB</small>
                        </div>
                        <div class="text-end">
                            @if($order->status === 'pending')
                                <span class="badge bg-warning text-white"><i class="bi bi-hourglass-split"></i> PENDING</span>
                            @elseif($order->status === 'processing')
                                <span class="badge bg-primary"><i class="bi bi-fire"></i> DIMASAK</span>
                            @elseif($order->status === 'completed')
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> SIAP DIHIDANGKAN</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body  text-white bg-opacity-50">
                        <div class="d-flex mb-3 align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3">
                                <i class="bi bi-geo-alt-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $order->tipe_pesanan == 'takeaway' ? 'Takeaway' : 'Dine-In' }}</h6>
                                <small class="text-white-50">
                                    @if($order->tipe_pesanan == 'takeaway')
                                        Bungkus
                                    @elseif($order->tipe_pesanan == 'dine_in' && !$order->id_meja)
                                        <span class="text-danger fw-bold"><i class="bi bi-geo-alt"></i> Belum Pilih Meja (Datang Nanti)</span>
                                    @else
                                        {{ $order->meja->nama_meja_atau_nomor ?? 'Meja Tidak Diketahui' }}
                                    @endif
                                </small>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-dark text-white border-secondary table-sm table-borderless mb-0">
                                <tbody>
                                    @foreach($order->detail_pesanan as $item)
                                        <tr>
                                            <td class="text-white-50" style="width: 30px; vertical-align: top;">{{ $item->jumlah }}x</td>
                                            <td class="fw-medium">
                                                {{ $item->menu->nama_menu ?? 'Menu tidak ditemukan' }}
                                                @if($item->selected_variants)
                                                    @php 
                                                        $variants = json_decode($item->selected_variants, true); 
                                                    @endphp
                                                    @if(is_array($variants) && count($variants) > 0)
                                                        <div class="small text-success"><i class="bi bi-tags me-1"></i>
                                                            @foreach($variants as $idx => $v)
                                                                {{ isset($v['qty']) && $v['qty'] > 1 ? $v['qty'].'x ' : '' }}{{ $v['name'] }}{{ $idx < count($variants) - 1 ? ', ' : '' }}
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                @endif
                                                @if($item->catatan)
                                                    <div class="small text-danger fst-italic"><i class="bi bi-chat-text me-1"></i>Catatan: {{ $item->catatan }}</div>
                                                @endif
                                            </td>
                                            <td class="text-end text-white-50" style="vertical-align: top;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer bg-transparent pt-3 pb-3 rounded-bottom-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-white-50 small">Total Tagihan ({{ $order->detail_pesanan->sum('jumlah') }} Item)</span>
                            <div class="text-end">
                                @if($order->discount_amount > 0)
                                    <span class="text-danger small text-decoration-line-through d-block" style="font-size: 0.8rem;">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
                                    <h6 class="fw-bold text-primary mb-0">Rp {{ number_format($order->total - $order->discount_amount, 0, ',', '.') }}</h6>
                                @else
                                    <h6 class="fw-bold text-primary mb-0">Rp {{ number_format($order->total, 0, ',', '.') }}</h6>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-white-50 small">Status Bayar</span>
                            @if($order->pembayaran && $order->pembayaran->status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle"></i> Lunas</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x-circle"></i> Belum Lunas</span>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                            @if($order->status === 'pending')
                                <button class="btn btn-sm btn-primary w-100 fw-bold btn-touch" onclick="updateOrderStatus({{ $order->id }}, 'processing')">
                                    <i class="bi bi-play-circle me-1"></i> Proses Masak
                                </button>
                            @endif
                            
                            @if($order->status === 'processing')
                                <button class="btn btn-sm btn-success w-100 fw-bold btn-touch" onclick="updateOrderStatus({{ $order->id }}, 'completed')">
                                    <i class="bi bi-check2-all me-1"></i> Selesai Dimasak
                                </button>
                            @endif

                            @if(!$order->pembayaran || $order->pembayaran->status !== 'paid')
                                <button class="btn btn-sm btn-outline-danger flex-grow-1 fw-bold btn-touch" onclick="payOrder({{ $order->id }})">
                                    <i class="bi bi-cash-stack me-1"></i> Terima Bayar
                                </button>
                                <button class="btn btn-sm btn-danger flex-grow-1 fw-bold btn-touch" onclick="voidOrder({{ $order->id }})">
                                    <i class="bi bi-trash me-1"></i> Void
                                </button>
                                <div class="w-100 m-0 p-0"></div> <!-- Break line -->
                                @if($order->detail_pesanan->sum('jumlah') > 1)
                                <button class="btn btn-sm btn-outline-warning w-100 fw-bold mt-1 btn-touch" data-details="{{ json_encode($order->detail_pesanan) }}" onclick="openSplitModal({{ $order->id }}, this)">
                                    <i class="bi bi-layout-split me-1"></i> Pisah Bon (Split Bill)
                                </button>
                                @endif
                            @else
                                @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                                @if($printerActive)
                                    <button class="btn btn-sm btn-info text-white flex-grow-1 fw-bold btn-touch" onclick="printThermal({{ $order->id }})">
                                        <i class="bi bi-printer me-1"></i> Cetak Thermal
                                    </button>
                                @endif
                                <a href="{{ route('kasir.order.receipt', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-bold btn-touch">
                                    <i class="bi bi-file-earmark-text me-1"></i> Cetak Browser
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="d-inline-block  text-white p-4 rounded-circle mb-3">
                    <i class="bi bi-receipt text-white-50" style="font-size: 3rem;"></i>
                </div>
                <h5 class="fw-bold text-white-50">Belum Ada Pesanan Masuk</h5>
                <p class="text-white-50">Pesanan dari konsumen akan muncul di sini.</p>
            </div>
        @endforelse