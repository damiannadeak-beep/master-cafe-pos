@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-0 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Pesanan Konsumen Aktif</h4>
        <button class="btn btn-outline-secondary btn-sm btn-touch" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Segarkan Data
        </button>
    </div>

    <div class="row g-4">
        @include("components.kasir.active-order-card")
    </div>
</div>

@include("components.kasir.payment-modal")

@include("components.kasir.qris-modal")

@include("components.kasir.split-bill-modal")

@include("components.kasir.verification-modal")

@include("components.kasir.pesanan-aktif-scripts")
@endsection

