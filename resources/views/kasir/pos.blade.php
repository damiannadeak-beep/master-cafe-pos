@extends('layouts.kasir')

@section('content')
@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-0" style="height: calc(100vh - 110px);">
    <div class="row g-4 align-items-start" style="height: 100%;">
        <div class="col-lg-8 col-md-7" style="height: 100%; overflow-y: auto; padding-right: 1rem;">
            <x-pos.product-grid :menus="$menus" />
        </div>
        <div class="col-lg-4 col-md-5" style="height: 100%; overflow-y: auto; border-left: 1px solid #21262d;">
            <x-pos.cart-sidebar :mejas="$mejas" :promos="$promos" />
        </div>
    </div>
</div>

<x-pos.modals />

@include("components.kasir.pos-scripts")

<style>
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

