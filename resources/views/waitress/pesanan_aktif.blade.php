@extends('layouts.waitress')

@section('content')
<style>
    .history-scope-filter {
        display: inline-flex;
        align-items: center;
        background-color: #0e1217;
        border: 1px solid #21262d;
        border-radius: 9999px;
        padding: 3px;
        gap: 4px;
    }
    .history-scope-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 5px 14px;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.2;
        color: #8b949e;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 9999px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        white-space: nowrap;
        user-select: none;
    }
    .history-scope-btn:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.06);
    }
    .history-scope-btn.active {
        color: #ffffff !important;
        background: #c08e5c !important;
        border-color: rgba(192, 142, 92, 0.5) !important;
        box-shadow: 0 2px 8px rgba(192, 142, 92, 0.35);
    }
    .history-scope-btn i {
        font-size: 0.82rem;
        opacity: 0.9;
    }
    .completed-card-luxury,
    .void-card-luxury {
        background-color: #141820 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -4px rgba(0, 0, 0, 0.45);
        padding: 1.25rem !important;
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .completed-card-luxury {
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .completed-card-luxury:hover {
        transform: translateY(-2px);
        border-color: rgba(255, 255, 255, 0.16) !important;
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.55);
    }
    .void-card-luxury {
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
    }
    .void-card-luxury:hover {
        transform: translateY(-2px);
        border-color: rgba(239, 68, 68, 0.35) !important;
        box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.55);
    }
    .completed-order-items::-webkit-scrollbar,
    .void-order-items::-webkit-scrollbar {
        width: 4px;
    }
    .completed-order-items::-webkit-scrollbar-track,
    .void-order-items::-webkit-scrollbar-track {
        background: transparent;
    }
    .completed-order-items::-webkit-scrollbar-thumb,
    .void-order-items::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 4px;
    }
</style>
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
            <div class="d-flex align-items-center gap-1 p-1 rounded-pill shadow-sm" style="background-color: #12161c; border: 1px solid #21262d;">
                <button type="button" class="nav-pill-btn active btn-touch" id="tab-btn-active" onclick="switchOrderTab('active')">
                    <i class="bi bi-fire me-1.5" style="color: #fbbf24;"></i> Pesanan Aktif
                    <span class="badge rounded-2 ms-2 px-2 shadow-sm" style="background: rgba(0, 0, 0, 0.3); color: #fff; font-size: 0.72rem;" id="tab-count-active">{{ $orders->count() }}</span>
                </button>
                <button type="button" class="nav-pill-btn btn-touch" id="tab-btn-completed" onclick="switchOrderTab('completed')">
                    <i class="bi bi-check2-all me-1.5" style="color: #34d399;"></i> Riwayat Selesai
                    <span class="badge rounded-2 ms-2 px-2" style="background: rgba(52, 211, 153, 0.15); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.3); font-size: 0.72rem;" id="tab-count-completed">{{ isset($groupedCompletedOrders) ? $groupedCompletedOrders->count() : $completedOrders->count() }}</span>
                </button>
                <button type="button" class="nav-pill-btn btn-touch" id="tab-btn-voided" onclick="switchOrderTab('voided')">
                    <i class="bi bi-trash3 me-1.5" style="color: #f87171;"></i> Riwayat Dibatalkan
                    <span class="badge rounded-2 ms-2 px-2" style="background: rgba(248, 113, 113, 0.15); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.3); font-size: 0.72rem;" id="tab-count-voided">{{ $voidedOrders->count() }}</span>
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
                    <div class="input-group input-group-sm pos-search-box">
                        <span class="input-group-text border-end-0 rounded-start-pill ps-3" style="background-color: #0e1217; border-color: #21262d; color: #8b949e;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill pos-search-input pe-3 text-white" 
                               id="search-completed-input" placeholder="Cari Order #, Meja, Nama Tamu..." 
                               onkeyup="filterCompletedOrders(this.value)" autocomplete="off"
                               style="background-color: #0e1217; border-color: #21262d;">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                    <div class="history-scope-filter">
                        <button type="button" class="history-scope-btn scope-btn-today {{ ($historyScope ?? 'today') === 'today' ? 'active' : '' }}" onclick="window.setHistoryScope('today')">
                            <i class="bi bi-calendar-check"></i> Hari Ini
                        </button>
                        <button type="button" class="history-scope-btn scope-btn-all {{ ($historyScope ?? 'today') === 'all' ? 'active' : '' }}" onclick="window.setHistoryScope('all')">
                            <i class="bi bi-clock-history"></i> Semua
                        </button>
                    </div>
                    <span class="text-secondary small fw-medium" id="completed-filter-info">
                        Menampilkan {{ isset($groupedCompletedOrders) ? $groupedCompletedOrders->count() : $completedOrders->count() }} sesi pesanan selesai {{ ($historyScope ?? 'today') === 'today' ? 'hari ini' : '(semua riwayat)' }}
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
                    <div class="input-group input-group-sm pos-search-box">
                        <span class="input-group-text border-end-0 rounded-start-pill ps-3" style="background-color: #0e1217; border-color: #21262d; color: #8b949e;">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 rounded-end-pill pos-search-input pe-3 text-white" 
                               id="search-voided-input" placeholder="Cari Order #, Meja, Alasan, Kasir..." 
                               onkeyup="filterVoidedOrders(this.value)" autocomplete="off"
                               style="background-color: #0e1217; border-color: #21262d;">
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-8 d-flex justify-content-md-end align-items-center gap-2 flex-wrap">
                    <div class="history-scope-filter">
                        <button type="button" class="history-scope-btn scope-btn-today {{ ($historyScope ?? 'today') === 'today' ? 'active' : '' }}" onclick="window.setHistoryScope('today')">
                            <i class="bi bi-calendar-check"></i> Hari Ini
                        </button>
                        <button type="button" class="history-scope-btn scope-btn-all {{ ($historyScope ?? 'today') === 'all' ? 'active' : '' }}" onclick="window.setHistoryScope('all')">
                            <i class="bi bi-clock-history"></i> Semua
                        </button>
                    </div>
                    <span class="text-secondary small fw-medium" id="voided-filter-info">
                        Menampilkan {{ $voidedOrders->count() }} pesanan dibatalkan {{ ($historyScope ?? 'today') === 'today' ? 'hari ini' : '(semua riwayat)' }}
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

