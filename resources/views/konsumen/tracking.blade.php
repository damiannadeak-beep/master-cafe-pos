@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{ asset('css/tracking.css') }}?v={{ file_exists(public_path('css/tracking.css')) ? filemtime(public_path('css/tracking.css')) : '1.0' }}">

<div class="container mt-3 mt-md-4 mb-5 pb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">

            @include('components.konsumen.tracking-header')

            @include('components.konsumen.tracking-stepper')

            @include('components.konsumen.tracking-summary')

            @include('components.konsumen.tracking-rating')

        </div>
    </div>
</div>

@php
    $serverOrdersList = [];
    if (isset($activeTableOrders) && $activeTableOrders->count() > 0) {
        foreach ($activeTableOrders as $act) {
            $serverOrdersList[] = [
                'token' => $act->order_token,
                'id' => $act->id,
                'label' => $meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Takeaway',
                'type' => $act->tipe_pesanan,
                'status' => $act->status,
                'time' => $act->created_at->timestamp * 1000
            ];
        }
    } else {
        $serverOrdersList[] = [
            'token' => $pesanan->order_token,
            'id' => $pesanan->id,
            'label' => $meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Takeaway',
            'type' => $pesanan->tipe_pesanan,
            'status' => $pesanan->status,
            'time' => $pesanan->created_at->timestamp * 1000
        ];
    }
@endphp

<script>
    window.TrackingConfig = {
        orderToken: "{{ $pesanan->order_token }}",
        pesananId: {{ $pesanan->id }},
        trackingMejaId: {{ $meja ? $meja->id : 0 }},
        orderLabel: "{{ $meja ? 'Meja ' . $meja->nama_meja_atau_nomor : 'Takeaway' }}",
        orderType: "{{ $pesanan->tipe_pesanan ?? 'dine_in' }}",
        orderStatus: "{{ $pesanan->status }}",
        paymentStatus: "{{ $pembayaran->status ?? 'unpaid' }}",
        menuUrl: "{{ $meja ? URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]) : route('menu_takeaway') }}",
        cancelUrl: "{{ url('/konsumen/order/' . $pesanan->id . '/cancel') }}",
        callBellUrl: "{{ url('/call-bell') }}",
        ratingUrl: "{{ url('/rating/store') }}",
        statusApiUrl: "{{ url('/api/tracking/' . $pesanan->order_token . '/status') }}",
        csrfToken: "{{ csrf_token() }}",
        serverOrders: {!! json_encode($serverOrdersList) !!}
    };
</script>
<script src="{{ asset('js/konsumen/tracking.js') }}?v={{ file_exists(public_path('js/konsumen/tracking.js')) ? filemtime(public_path('js/konsumen/tracking.js')) : '1.0' }}"></script>
@endsection
