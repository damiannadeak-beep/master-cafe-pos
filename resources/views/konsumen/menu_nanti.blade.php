@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="alert border-0 rounded-4 d-flex justify-content-between align-items-center shadow-sm p-3 p-md-4 mb-4" 
         style="background: var(--gradient-surface); border: 1px solid #21262d !important;">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3" 
                 style="width: 48px; height: 48px; background: rgba(192, 142, 92, 0.15); border: 1px solid rgba(192, 142, 92, 0.3); color: #c08e5c; flex-shrink: 0;">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="text-white fw-bold fs-5" style="font-family: 'Outfit', sans-serif !important;">
                        Pesan Untuk Nanti
                    </span>
                    <span class="badge rounded-pill px-2 py-1 fw-semibold" style="background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); font-size: 0.7rem;">
                        Pre-Order
                    </span>
                </div>
                <small class="text-secondary d-block" style="font-size: 0.82rem;">Pesanan Anda akan disiapkan sesuai waktu yang dipilih</small>
            </div>
        </div>
        <div>
            <img src="{{ asset('images/logo.png') }}" alt="Master Cafe" class="rounded-circle shadow-sm" style="height: 44px; width: 44px; object-fit: cover; border: 1px solid rgba(192, 142, 92, 0.3);">
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
