@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="card border-0 rounded-4 shadow-sm p-3 p-md-4 mb-4" 
         style="background: linear-gradient(135deg, #1c2128 0%, #161b22 100%); border: 1px solid #21262d !important; border-left: 4px solid #3b82f6 !important;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                    <h5 class="text-white fw-bold mb-0 text-nowrap" style="font-family: 'Outfit', sans-serif !important;">
                        <i class="bi bi-clock-history me-1" style="color: #60a5fa;"></i> Pesan Untuk Nanti
                    </h5>
                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.7rem;">
                        Pre-Order
                    </span>
                </div>
                <small class="text-secondary d-block" style="font-size: 0.82rem;">Pesanan Anda akan disiapkan sesuai waktu yang dipilih</small>
            </div>
        </div>
    </div>

    @include('components.konsumen.promo-banner')
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mt-4 mb-3 gap-2">
        <h5 class="fw-bold mb-0 text-white" style="font-family: 'Outfit', sans-serif;">Menu Tersedia</h5>
        @include('components.konsumen.kategori-filter')
    </div>
    
    <div class="row g-4">
        @include('components.konsumen.menu-card')
    </div>
    
    <div style="height: 140px;"></div>
</div>

@include('components.konsumen.variant-modal')
@include('components.konsumen.cart-bar')
@include('components.konsumen.menu-scripts', ['orderType' => 'dine_in'])
@endsection
