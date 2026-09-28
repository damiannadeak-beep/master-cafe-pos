@extends('layouts.waitress')

@section('content')
<div class="container-fluid px-0 pos-main-container">
    <div class="row gx-4 m-0 align-items-start pos-main-row">
        <div class="col-lg-8 col-md-7 pos-catalog-col">
            <x-pos.product-grid :menus="$menus" />
        </div>
        <div class="col-lg-4 col-md-5 pos-cart-col" id="pos-cart-section">
            <x-pos.cart-sidebar :mejas="$mejas" :promos="$promos" />
        </div>
    </div>
</div>

<!-- Mobile Quick Floating Cart Indicator (< 768px) -->
<div id="mobile-pos-cart-pill" class="d-md-none fixed-bottom p-3" style="display: none; z-index: 1040; pointer-events: none;">
    <button type="button" onclick="document.getElementById('pos-cart-section')?.scrollIntoView({behavior: 'smooth'})" class="btn w-100 rounded-pill py-2 px-3 fw-bold d-flex justify-content-between align-items-center shadow-lg" style="background: var(--gradient-bronze, linear-gradient(135deg, #c08e5c 0%, #986c43 100%)); color: white; border: none; pointer-events: auto; backdrop-filter: blur(8px);">
        <span class="d-flex align-items-center gap-2">
            <i class="bi bi-cart3 fs-5"></i>
            <span id="mobile-pos-cart-badge" class="badge rounded-2 bg-dark text-white px-2 py-1">0 Item</span>
        </span>
        <span class="d-flex align-items-center gap-1">
            <span id="mobile-pos-cart-total" class="fw-bold">Rp 0</span>
            <i class="bi bi-arrow-down-short fs-5 ms-1"></i>
        </span>
    </button>
</div>

<x-pos.modals />

@include("components.waitress.pos-scripts")

<style>
    /* Desktop & Tablet (>= 768px) */
    @media (min-width: 768px) {
        html, body, .kasir-layout { height: 100vh !important; overflow: hidden !important; }
        .kasir-main { padding: 0 !important; overflow: hidden !important; }
        .kasir-container { padding: 0 !important; overflow: hidden !important; height: 100% !important; }
        .pos-main-container { height: calc(100vh - 125px); margin-top: 15px; }
        .pos-main-row { height: 100%; }
        .pos-catalog-col { height: 100%; overflow-y: auto; padding-right: 1rem; }
        .pos-cart-col { height: 100%; overflow-y: auto; border-left: 1px solid #21262d; }
    }

    /* Mobile Phone (< 768px): Membuka scroll penuh agar kasir dapat melihat keranjang pesanan */
    @media (max-width: 767.98px) {
        html, body, .kasir-layout {
            height: auto !important;
            min-height: 100vh !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
        }
        .kasir-main {
            padding: 0.5rem 0.25rem 5rem 0.25rem !important;
            overflow: visible !important;
        }
        .kasir-container {
            padding: 0 !important;
            overflow: visible !important;
            height: auto !important;
        }
        .pos-main-container {
            height: auto !important;
            margin-top: 5px;
        }
        .pos-main-row {
            height: auto !important;
        }
        .pos-catalog-col {
            height: auto !important;
            overflow: visible !important;
            padding-right: 0 !important;
            margin-bottom: 1.5rem;
        }
        .pos-cart-col {
            height: auto !important;
            overflow: visible !important;
            border-left: none !important;
            border-top: 2px dashed #21262d;
            padding-top: 1.5rem;
        }
    }

    .item-menu:hover { background-color: #F0E9DD; transform: translateY(-3px); border: 1px solid #3E2723 !important; }

    /* Hilangkan panah atas/bawah pada input number agar angka benar-benar di tengah */
    input[type=number]::-webkit-inner-spin-button, 
    input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; 
        margin: 0; 
    }
    input[type=number] {
        -moz-appearance: textfield; /* Firefox */
    }
</style>
@endsection



