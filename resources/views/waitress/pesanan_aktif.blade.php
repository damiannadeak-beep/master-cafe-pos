@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 py-3" style="padding-bottom: 5rem !important;">
    <!-- Top Header & Segmented Pill Tabs -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                <h4 class="fw-bold mb-0 text-white" id="page-order-title">
                    <i class="bi bi-receipt text-accent me-1.5" style="color: #c08e5c;"></i> Monitor Pesanan Waitress
                </h4>
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 small d-inline-flex align-items-center">
                    <i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i> Realtime Auto-Sync
                </span>
            </div>
            <p class="text-white-50 mb-0 small">Pantau antrean pesanan aktif atau lihat kembali riwayat pesanan yang sudah selesai hari ini</p>
        </div>

        <!-- Segmented Pill Tabs -->
        <div class="d-flex align-items-center gap-2">
            <div class="d-flex align-items-center gap-1 p-1 rounded-pill border border-secondary border-opacity-25 shadow-sm" style="background-color: rgba(22, 27, 34, 0.85);">
                <button type="button" class="nav-pill-btn active btn-touch" id="tab-btn-active" onclick="switchOrderTab('active')">
                    <i class="bi bi-fire me-2 text-warning"></i> Pesanan Aktif
                    <span class="badge bg-danger rounded-pill ms-2 px-2 shadow-sm" id="tab-count-active">{{ $orders->count() }}</span>
                </button>
                <button type="button" class="nav-pill-btn btn-touch" id="tab-btn-completed" onclick="switchOrderTab('completed')">
                    <i class="bi bi-check2-all me-2 text-success"></i> Riwayat Selesai
                    <span class="badge rounded-pill ms-2 px-2 shadow-sm" style="background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3);" id="tab-count-completed">{{ isset($groupedCompletedOrders) ? $groupedCompletedOrders->count() : $completedOrders->count() }}</span>
                </button>
                <button type="button" class="nav-pill-btn btn-touch" id="tab-btn-voided" onclick="switchOrderTab('voided')">
                    <i class="bi bi-trash3 me-2 text-danger"></i> Riwayat Dibatalkan
                    <span class="badge rounded-pill ms-2 px-2 shadow-sm" style="background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);" id="tab-count-voided">{{ $voidedOrders->count() }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- PANE 1: Pesanan Aktif -->
    <div id="pane-active-orders">
        <div class="row g-4" id="active-orders-container">
            @include("components.waitress.active-order-card")
        </div>
    </div>

    <!-- PANE 2: Riwayat Pesanan Selesai (Hari Ini) -->
    <div id="pane-completed-orders" style="display: none;">
        <!-- Search & Filter Bar untuk Riwayat -->
        <div class="card border-0 rounded-4 p-3 mb-4 shadow-sm" style="background-color: rgba(22, 27, 34, 0.85); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control bg-dark border-secondary border-opacity-25 text-white" 
                               id="search-completed-input" placeholder="Cari Order #, Meja, Nama Tamu..." 
                               onkeyup="filterCompletedOrders(this.value)">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end align-items-center gap-2">
                    <span class="badge rounded-pill bg-dark border border-secondary text-secondary small px-3 py-1.5" id="completed-filter-info">
                        Menampilkan {{ isset($groupedCompletedOrders) ? $groupedCompletedOrders->count() : $completedOrders->count() }} sesi pesanan selesai hari ini
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4" id="completed-orders-container">
            @include("components.waitress.completed-order-card")
        </div>
    </div>

    <!-- PANE 3: Riwayat Pesanan Dibatalkan (Void / Cancelled) -->
    <div id="pane-voided-orders" style="display: none;">
        <!-- Search & Filter Bar untuk Riwayat Dibatalkan -->
        <div class="card border-0 rounded-4 p-3 mb-4 shadow-sm" style="background-color: rgba(22, 27, 34, 0.85); border: 1px solid rgba(255, 255, 255, 0.08) !important;">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary border-opacity-25 text-secondary">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control bg-dark border-secondary border-opacity-25 text-white" 
                               id="search-voided-input" placeholder="Cari Order #, Meja, Alasan, Kasir..." 
                               onkeyup="filterVoidedOrders(this.value)">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end align-items-center gap-2">
                    <span class="badge rounded-pill bg-dark border border-secondary text-secondary small px-3 py-1.5" id="voided-filter-info">
                        Menampilkan {{ $voidedOrders->count() }} pesanan dibatalkan
                    </span>
                </div>
            </div>
        </div>

        <div class="row g-4" id="voided-orders-container">
            @include("components.waitress.voided-order-card")
        </div>
    </div>
</div>

@include("components.waitress.payment-modal")

@include("components.waitress.void-modal")

@include("components.waitress.qris-modal")

@include("components.waitress.split-bill-modal")

@include("components.waitress.verification-modal")

@include("components.waitress.order-completed-modal")

@include("components.waitress.pesanan-aktif-scripts")
@endsection

