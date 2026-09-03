@extends('layouts.app') 

@section('content')
<div class="container mt-5 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg border-0 rounded-4" style="background-color: #161b22; border: 1px solid #21262d !important;">
                <div class="card-header border-0 py-4 text-center" style="background: var(--gradient-bronze); border-radius: 1rem 1rem 0 0;">
                    <i class="bi bi-receipt text-white mb-2 d-block" style="font-size: 2.5rem;"></i>
                    <h4 class="mb-0 text-white fw-bold" style="font-family: 'Rye', serif;">Ringkasan Pesanan</h4>
                    <p class="text-white-50 mb-0 small">ID Pesanan: #{{ $pesanan->id }}</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    <ul class="list-group list-group-flush mb-4">
                        @foreach($pesanan->detail_pesanan as $detail)
                        <li class="list-group-item d-flex justify-content-between align-items-center bg-transparent border-secondary py-3 px-0">
                            <div>
                                <h6 class="my-0 text-white fw-bold">{{ $detail->menu->nama_menu }}</h6>
                                <small class="text-secondary">{{ $detail->jumlah }}x @ Rp {{ number_format($detail->menu->harga, 0, ',', '.') }}</small>
                            </div>
                            <span class="text-white fw-semibold">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                        </li>
                        @endforeach
                    </ul>
                    
                    <div class="p-4 rounded-4 mb-4" style="background-color: #0e1217; border: 1px dashed #21262d;">
                        <div class="d-flex justify-content-between fw-bold fs-4">
                            <span class="text-secondary">Total Bayar</span>
                            <span style="color: #c08e5c;">Rp {{ number_format($pembayaran->total_bayar, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                                        <div class="d-grid mt-4">
                        @if($pembayaran->snap_token)
                        <!-- Tombol Bayar Midtrans -->
                        <button id="pay-button" class="btn btn-lg fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; padding: 12px 20px;">
                            Bayar Sekarang <i class="bi bi-credit-card ms-2"></i>
                        </button>
                        @else
                        <!-- Tombol Manual -->
                        <a href="{{ url('/konsumen/profil') }}" class="btn btn-lg fw-bold rounded-pill shadow btn-touch" style="background: var(--gradient-bronze); color: white; border: none; padding: 12px 20px;">
                            Tutup & Bayar di Kasir <i class="bi bi-check-circle ms-2"></i>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($pembayaran->snap_token)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $pembayaran->snap_token }}', {
            onSuccess: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onPending: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onError: function (result) {
                alert("Pembayaran Gagal!");
            },
            onClose: function () {
                console.log('User closed popup without finishing payment');
            }
        });
    };
</script>
@endif
<script>
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $pembayaran->snap_token }}', {
            onSuccess: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onPending: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onError: function (result) {
                alert("Pembayaran Gagal!");
            },
            onClose: function () {
                console.log('User closed popup without finishing payment');
            }
        });
    };
</script>
@endsection

@if($pembayaran->snap_token)
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
    document.getElementById('pay-button').onclick = function () {
        snap.pay('{{ $pembayaran->snap_token }}', {
            onSuccess: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onPending: function (result) {
                window.location.href = "{{ url('/konsumen/profil') }}";
            },
            onError: function (result) {
                alert("Pembayaran Gagal!");
            },
            onClose: function () {
                console.log('User closed popup without finishing payment');
            }
        });
    };
</script>
@endif
@endsection