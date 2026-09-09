@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-0 py-3" style="height: 100%; overflow-y: auto; overflow-x: hidden; padding-bottom: 5rem !important;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <h4 class="fw-bold mb-0">Pesanan Konsumen Aktif</h4>
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 ms-3 small d-none d-md-inline-flex align-items-center">
                <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Realtime Auto-Sync
            </span>
        </div>
        <button id="btn-refresh-orders" class="btn btn-outline-secondary btn-sm btn-touch rounded-pill px-3 shadow-sm" onclick="window.reloadActiveOrdersCards()" title="Klik untuk cek pembaruan pesanan">
            <i class="bi bi-arrow-clockwise me-1"></i> Segarkan Data
        </button>
    </div>

    <div class="row g-4" id="active-orders-container">
        @include("components.kasir.active-order-card")
    </div>
</div>

@include("components.kasir.payment-modal")

@include("components.kasir.void-modal")

@include("components.kasir.qris-modal")

@include("components.kasir.split-bill-modal")

@include("components.kasir.verification-modal")

@include("components.kasir.pesanan-aktif-scripts")
@endsection

