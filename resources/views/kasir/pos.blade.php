@extends('layouts.kasir')

@section('content')
<div class="container-fluid px-0 h-100">
    <div class="row gx-4 m-0 align-items-start h-100">
        <div class="col-lg-8 col-md-7 h-100" style="overflow-y: auto; padding-right: 1rem;">
            <x-pos.product-grid :menus="$menus" />
        </div>
        <div class="col-lg-4 col-md-5 h-100" style="overflow-y: auto; border-left: 1px solid #21262d;">
            <x-pos.cart-sidebar :mejas="$mejas" :promos="$promos" />
        </div>
    </div>
</div>

<x-pos.modals />

@include("components.kasir.pos-scripts")

<style>
    .kasir-main { 
        padding: 1.25rem 1.5rem 1rem 1.5rem !important; 
    }
    .kasir-container { 
        padding: 0 !important; 
        overflow: hidden !important; 
        max-width: 100% !important; 
        height: 100% !important;
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



