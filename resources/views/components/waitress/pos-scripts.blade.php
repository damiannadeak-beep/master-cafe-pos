<script>
    window.allMenus = {!! json_encode($menus) !!};
    window.defaultLogoUrl = "{{ asset('images/logo.png') }}";
    window.printerActive = {{ \App\Models\Setting::getVal('printer_active') == '1' ? 'true' : 'false' }};
    window.csrfToken = "{{ csrf_token() }}";
    window.manualOrderUrl = "{{ url('/kasir/manual-order') }}";
</script>

{{-- Modular Waitress POS Order Scripts --}}
@php
    $posModules = [
        'js/waitress/modules/pos/pos-cart.js',
        'js/waitress/modules/pos/pos-catalog.js',
        'js/waitress/modules/pos/pos-variant.js',
        'js/waitress/modules/pos/pos-checkout.js',
        'js/waitress/pos-order.js',
    ];
@endphp

@foreach ($posModules as $pMod)
    <script src="{{ asset($pMod) }}?v={{ file_exists(public_path($pMod)) ? filemtime(public_path($pMod)) : '1.0' }}"></script>
@endforeach
