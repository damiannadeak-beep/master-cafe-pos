@forelse($orders as $order)
            <div class="col-md-6 col-lg-4" id="order-card-{{ $order->id }}">
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
                        <div class="p-3 rounded-3 mb-3" style="background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="background: rgba(192, 142, 92, 0.15); width: 36px; height: 36px; color: #c08e5c;">
                                        <i class="{{ $order->tipe_pesanan == 'takeaway' ? 'bi bi-bag-check-fill' : 'bi bi-shop' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-white fs-6">
                                            {{ $order->tipe_pesanan == 'takeaway' ? 'Takeaway (Bungkus)' : 'Dine-In (Makan di Tempat)' }}
                                        </h6>
                                        <small class="text-secondary" style="font-size: 0.8rem;">
                                            @if($order->tipe_pesanan == 'takeaway')
                                                <span class="badge rounded-pill text-bg-warning px-2">Bawa Pulang</span>
                                            @elseif($order->tipe_pesanan == 'dine_in' && !$order->id_meja)
                                                <span class="text-danger fw-bold"><i class="bi bi-geo-alt"></i> Belum Pilih Meja</span>
                                            @else
                                                <span class="badge rounded-pill text-bg-secondary px-2">Meja {{ $order->meja->nama_meja_atau_nomor ?? '?' }}</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Detail Pemesan & No WA -->
                            <div class="pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-1">
                                <div>
                                    <div class="small text-white fw-bold">
                                        <i class="bi bi-person-fill text-warning me-1"></i> Pemesan: <span class="text-white">{{ $order->customer_name }}</span>
                                    </div>
                                    @if(!empty($order->guest_phone))
                                        <div class="small text-white-50 mt-1">
                                            <i class="bi bi-whatsapp text-success me-1"></i> WA: <span class="text-white">{{ $order->guest_phone }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if(!empty($order->guest_phone))
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $order->guest_phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                        $waMessage = rawurlencode("Halo Kak {$order->customer_name}, pesanan Master Cafe #{$order->id} Anda sudah siap! Silakan diambil di kasir/counter. Terima kasih!");
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMessage }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                        <i class="bi bi-whatsapp me-1"></i> Chat WA
                                    </a>
                                @endif
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

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-white-50 small">Status Bayar</span>
                            @if($order->pembayaran && $order->pembayaran->status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle"></i> Lunas ({{ strtoupper($order->pembayaran->metode ?? 'QRIS') }})</span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x-circle"></i> Belum Lunas</span>
                            @endif
                        </div>

                        @if($order->tipe_pesanan === 'dine_in' && $order->pembayaran && $order->pembayaran->metode === 'cash' && $order->pembayaran->status !== 'paid')
                            <div class="p-2 mb-3 rounded-3" style="background: rgba(234, 179, 8, 0.12); border: 1px solid rgba(234, 179, 8, 0.35);">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-cash-stack me-1"></i>BAYAR TUNAI DI MEJA</span>
                                    <small class="text-warning fw-bold">Siapkan Uang</small>
                                </div>
                                @if($order->pembayaran->uang_kembalian > 0)
                                    <div class="small text-white fw-bold">
                                        ⚠️ Siapkan Kembalian: <span class="text-warning fs-6">Rp {{ number_format($order->pembayaran->uang_kembalian, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="small text-white-50">
                                        (Tamu bayar uang: Rp {{ number_format($order->pembayaran->uang_diterima, 0, ',', '.') }})
                                    </div>
                                @elseif($order->pembayaran->uang_diterima)
                                    <div class="small text-success fw-bold">
                                        ✅ Uang Pas: Rp {{ number_format($order->pembayaran->uang_diterima, 0, ',', '.') }} (Tanpa Kembalian)
                                    </div>
                                @else
                                    <div class="small text-white-50">
                                        Tamu akan bayar tunai ke Waitress saat pesanan tiba di meja.
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top">
                            <!-- Tombol Cetak Tiket Dapur / Koki -->
                            <a href="{{ route('kasir.order.kitchen', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-info flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" title="Cetak rincian pesanan untuk Koki / Barista">
                                <i class="bi bi-journal-text me-1"></i> Tiket Dapur / Koki
                            </a>

                            @if($order->status === 'pending')
                                <button type="button" class="btn btn-sm btn-primary flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus({{ $order->id }}, 'processing', this)">
                                    <i class="bi bi-fire me-1"></i> Konfirmasi & Dimasak
                                </button>
                            @endif
                            
                            @if($order->status === 'processing')
                                <button type="button" class="btn btn-sm btn-success flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.updateOrderStatus({{ $order->id }}, 'completed', this)">
                                    <i class="bi bi-check2-all me-1"></i> {{ $order->tipe_pesanan === 'takeaway' ? 'Selesai & Serahkan Pesanan' : 'Selesai Dimasak' }}
                                </button>
                            @endif

                            @if(!$order->pembayaran || $order->pembayaran->status !== 'paid')
                                @if($order->pembayaran && $order->pembayaran->status === 'pending_verification')
                                    <button type="button" class="btn btn-sm btn-warning text-dark flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.verifyPayment({{ $order->id }}, '{{ asset('storage/' . $order->pembayaran->bukti_bayar) }}')">
                                        <i class="bi bi-image me-1"></i> Cek Bukti Bayar
                                    </button>
                                @else
                                    @php $totalPayActive = (int) ($order->pembayaran?->total_bayar ?? ($order->total - ($order->discount_amount ?? 0))); @endphp
                                    <button type="button" class="btn btn-sm btn-outline-danger flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.payOrder({{ $order->id }}, {{ $totalPayActive }})">
                                        <i class="bi bi-cash-stack me-1"></i> Terima Bayar
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-danger flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.voidOrder({{ $order->id }})">
                                    <i class="bi bi-trash me-1"></i> Void
                                </button>
                                <div class="w-100 m-0 p-0"></div> <!-- Break line -->
                                @if($order->detail_pesanan->sum('jumlah') > 1)
                                <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-bold mt-1 btn-touch d-flex justify-content-center align-items-center" data-details="{{ json_encode($order->detail_pesanan) }}" onclick="window.openSplitModal({{ $order->id }}, this)">
                                    <i class="bi bi-layout-split me-1"></i> Pisah Bon (Split Bill)
                                </button>
                                @endif
                            @else
                                @php $printerActive = \App\Models\Setting::getVal('printer_active') == '1'; @endphp
                                @if($printerActive)
                                    <button type="button" class="btn btn-sm btn-info text-white flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center" onclick="window.printThermal({{ $order->id }})">
                                        <i class="bi bi-printer me-1"></i> Cetak Thermal
                                    </button>
                                @endif
                                <a href="{{ route('kasir.order.receipt', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1 fw-bold btn-touch d-flex justify-content-center align-items-center">
                                    <i class="bi bi-file-earmark-text me-1"></i> Struk Kasir (Browser)
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