@extends('layouts.app')

@section('content')
<div class="container pt-3 pt-md-4 pb-5 mb-5">
    <div class="card border-0 rounded-4 shadow-sm p-3 p-md-4 mb-4 mt-2 mt-md-3" 
         style="background: linear-gradient(135deg, #1c2128 0%, #161b22 100%); border: 1px solid #21262d !important; border-left: 4px solid #3b82f6 !important;">
        <div class="d-flex justify-content-between align-items-center">
            <div style="min-width: 0;">
                <div class="d-flex align-items-center flex-wrap gap-1 gap-sm-2 mb-1">
                    <h5 class="text-white fw-bold mb-0 d-flex align-items-center flex-wrap gap-1">
                        <span><i class="bi bi-clock-history me-1" style="color: #60a5fa;"></i> Pesan Untuk Nanti</span>
                        <span class="text-secondary small fw-normal fs-6 text-nowrap">(Pre-Order)</span>
                    </h5>
                </div>
                <small class="text-secondary d-block" style="font-size: 0.82rem;">Pesanan Anda akan disiapkan sesuai waktu yang dipilih</small>
            </div>
        </div>
    </div>

    @include('components.konsumen.promo-banner')
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-2">
        <h5 class="fw-bold mb-0 text-white">Menu Tersedia</h5>
        @include('components.konsumen.kategori-filter')
    </div>
    
    <div class="row g-3 g-md-4 align-items-stretch">
        @include('components.konsumen.menu-card')
    </div>
    
    <div style="height: 140px;"></div>
</div>

@include('components.konsumen.variant-modal')
@include('components.konsumen.dynamic-price-modal')
@include('components.konsumen.cart-bar')
@include('components.konsumen.menu-scripts', ['orderType' => 'dine_in'])
@endsection
