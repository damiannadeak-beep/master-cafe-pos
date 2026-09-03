@extends('layouts.app')

@section('content')
<div class="container pb-5 mb-5">
    <div class="alert border-0 rounded-4 d-flex" style="background: var(--gradient-surface); border: 1px solid #21262d !important;" justify-content-between align-items-center shadow-sm">
        <div>
            <h5 class="mb-0 fw-bold text-white" style="font-family: 'Rye', serif;"><i class="fas fa-clock"></i> Pesan Untuk Nanti</h5>
            <small class="text-secondary">Silakan pilih menu Anda</small>
        </div>
        <img src="{{ asset('images/logo.png') }}" alt="Logo" class="rounded-circle shadow-sm" style="height: 48px; width: 48px; object-fit: cover; margin-bottom: 8px;">
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
@include('components.konsumen.menu-scripts', ['orderType' => 'pesan-nanti'])
@endsection