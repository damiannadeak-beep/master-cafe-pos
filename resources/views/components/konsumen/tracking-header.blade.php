{{-- Multi-Order Switcher Bar (Jika pelanggan memiliki lebih dari 1 pesanan aktif) --}}
@if(isset($activeTableOrders) && $activeTableOrders->count() > 1)
    <div class="card tracking-card shadow-sm p-3 mb-3" style="border: 1px solid rgba(192, 142, 92, 0.4) !important; background: linear-gradient(135deg, rgba(192, 142, 92, 0.12) 0%, rgba(22, 27, 34, 0.95) 100%);">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <span class="text-white small fw-bold">
                <i class="bi bi-layers-fill me-1 text-warning"></i> Anda Memiliki <strong>{{ $activeTableOrders->count() }} Pesanan Aktif</strong> di {{ $meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Antrean' }}:
            </span>
            <span class="badge rounded-pill bg-warning text-dark fw-bold px-2 py-1" style="font-size: 0.72rem;">
                {{ $activeTableOrders->count() }} Pesanan
            </span>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            @foreach($activeTableOrders as $idx => $actOrd)
                @php $isCurrent = ($actOrd->id === $pesanan->id); @endphp
                <a href="{{ url('/tracking/' . $actOrd->order_token) }}" 
                   class="btn btn-sm rounded-pill fw-bold d-inline-flex align-items-center gap-1.5 btn-touch {{ $isCurrent ? 'text-white' : 'btn-outline-secondary text-light' }}"
                   style="font-size: 0.8rem; {{ $isCurrent ? 'background: var(--gradient-bronze); border: none; box-shadow: 0 2px 8px rgba(192,142,92,0.4);' : '' }}">
                    <i class="bi {{ $isCurrent ? 'bi-check-circle-fill' : 'bi-receipt' }}"></i>
                    <span>{{ $idx === 0 ? 'Pesanan Awal' : 'Tambahan' }} (#{{ $actOrd->id }})</span>
                    @if($actOrd->status === 'processing')
                        <span class="badge bg-primary py-0 px-1 ms-1" style="font-size: 0.65rem;">Dimasak</span>
                    @elseif($actOrd->status === 'completed')
                        <span class="badge bg-success py-0 px-1 ms-1" style="font-size: 0.65rem;">Selesai</span>
                    @else
                        <span class="badge bg-warning text-dark py-0 px-1 ms-1" style="font-size: 0.65rem;">Menunggu</span>
                    @endif
                    @if($isCurrent)
                        <span class="badge bg-dark bg-opacity-50 text-white-50 ms-1" style="font-size: 0.65rem;">Sedang Dilihat</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
@else
    <div id="multi-order-switcher-bar" class="card tracking-card shadow-sm p-3 mb-3" style="display: none; border: 1px solid rgba(192, 142, 92, 0.35) !important;">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <span class="text-white small fw-bold">
                <i class="bi bi-receipt-cutoff me-1" style="color: #c08e5c;"></i> Pesanan Aktif Anda di Kafe:
            </span>
            <span class="badge rounded-pill" style="background: rgba(192, 142, 92, 0.2); color: #c08e5c;" id="multi-order-count-badge"></span>
        </div>
        <div class="d-flex gap-2 flex-wrap" id="multi-order-pills-container">
            <!-- Dynamic Order Switcher Pills -->
        </div>
    </div>
@endif

@if($pesanan->status === 'completed')
    <!-- Banner Pesanan Selesai & Tombol Selesai Utama -->
    <div class="alert border-0 rounded-4 p-4 mb-4 text-center shadow-lg" style="background: rgba(35, 134, 54, 0.15); border: 1px solid rgba(46, 160, 67, 0.35) !important;">
        <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
        <h5 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Pesanan Selesai & Pembayaran Lunas</h5>
        <p class="text-secondary small mb-3">Terima kasih telah berkunjung ke Master Cafe. Klik tombol di bawah untuk menyelesaikan sesi pesanan Anda.</p>
        <button onclick="finishCustomerSession()" class="btn btn-md rounded-pill px-4 py-2 fw-bold shadow-sm" style="background: var(--gradient-bronze); color: white; border: none;">
            <i class="bi bi-check2-circle me-1"></i> Selesai
        </button>
        <p class="text-white-50 small mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i> Butuh struk? Silakan minta ke kasir kami.
        </p>
    </div>
@elseif($pesanan->status === 'cancelled')
    <!-- Banner Pesanan Dibatalkan / Dihapus oleh Kasir -->
    <div class="alert border-0 rounded-4 p-4 mb-4 text-center shadow-lg" style="background: rgba(220, 53, 69, 0.15); border: 1px solid rgba(220, 53, 69, 0.35) !important;">
        <i class="bi bi-x-circle-fill text-danger fs-1 mb-2 d-block"></i>
        <h5 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Pesanan Telah Dibatalkan / Dihapus</h5>
        <p class="text-secondary small mb-3">Pesanan Anda telah dibatalkan oleh Kasir. Silakan klik tombol di bawah untuk kembali ke menu dan membuat pesanan baru.</p>
        <button onclick="finishCustomerSession()" class="btn btn-md btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm">
            <i class="bi bi-arrow-left-circle me-1"></i> Kembali ke Menu / Pesan Baru
        </button>
    </div>
@endif

<!-- Card Header Status Meja -->
<div class="card tracking-card shadow-lg p-3 p-md-4 mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <span class="badge rounded-pill px-3 py-1 mb-2 fw-semibold" style="background: rgba(192, 142, 92, 0.15); color: #c08e5c; border: 1px solid rgba(192, 142, 92, 0.3); font-size: 0.8rem;">
                <i class="bi bi-geo-alt-fill me-1"></i> {{ ($pesanan->tipe_pesanan ?? '') === 'takeaway' ? 'Bawa Pulang (Takeaway)' : ($meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Pesanan Cafe') }}
            </span>
            <h4 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                Order #{{ $pesanan->id }}
            </h4>
            <p class="text-secondary small mb-0">
                Atas Nama: <strong class="text-white">{{ $pesanan->customer_name }}</strong>
                @if(!empty($pesanan->guest_phone))
                    &bull; <i class="bi bi-whatsapp text-success me-1"></i><strong class="text-white">{{ $pesanan->guest_phone }}</strong>
                @endif
                &bull; {{ $pesanan->created_at->translatedFormat('d M Y, H:i') }}
            </p>
        </div>

        <div class="text-end">
            <div id="badge-status-container">
                @if($pesanan->status === 'completed')
                    <button onclick="finishCustomerSession()" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-sm">
                        <i class="bi bi-check-all me-1"></i> Selesai
                    </button>
                @elseif($pesanan->status === 'processing')
                    <span class="badge bg-primary bg-opacity-25 text-primary border border-primary px-3 py-2 rounded-pill fs-6 pulse-animation">
                        <i class="bi bi-cup-hot-fill me-1"></i> Sedang Disiapkan
                    </span>
                @elseif($pesanan->status === 'cancelled')
                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-2 rounded-pill fs-6">
                        <i class="bi bi-x-circle me-1"></i> Dibatalkan
                    </span>
                @else
                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-2 rounded-pill fs-6">
                        <i class="bi bi-clock-history me-1"></i> Menunggu
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Tombol Panggil Pelayan & Tambah Pesanan -->
    @if($meja)
        <div class="mt-3 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-secondary">Ingin menambah pesanan atau butuh bantuan?</small>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]) }}" class="btn btn-sm rounded-pill px-3 fw-bold btn-touch" style="background: var(--gradient-bronze); color: white; border: none;">
                    <i class="bi bi-plus-circle me-1"></i> Tambah Menu di Meja Ini
                </a>
                <button id="btnCallBell" onclick="callWaiter({{ $meja->id }})" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold btn-touch">
                    <i class="bi bi-bell-fill me-1"></i> Panggil Pelayan
                </button>
            </div>
        </div>
    @elseif(($pesanan->tipe_pesanan ?? '') === 'takeaway')
        <div class="mt-3 pt-3 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <small class="text-secondary">Ingin memesan menu bungkus lainnya?</small>
            <a href="{{ route('menu_takeaway') }}" class="btn btn-sm rounded-pill px-3 fw-bold btn-touch" style="background: var(--gradient-bronze); color: white; border: none;">
                <i class="bi bi-plus-circle me-1"></i> Pesan Menu Tambahan (Bungkus)
            </a>
        </div>
    @endif
</div>
