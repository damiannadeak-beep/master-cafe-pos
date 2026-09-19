<script>
    window.ConsumerMenuConfig = {
        allMenus: @json($menus),
        orderType: '{{ $orderType ?? "dine_in" }}',
        hasMeja: {{ isset($meja) ? 'true' : 'false' }},
        mejaId: '{{ $meja->id ?? "" }}',
        mejaLabel: '{{ isset($meja) ? "Meja " . $meja->nama_meja_atau_nomor : "Takeaway" }}',
        isGeofenceActive: {{ \App\Models\Setting::getVal('geofence_active', '0') == '1' ? 'true' : 'false' }},
        orderAddUrl: '{{ url("/konsumen/order/add") }}',
        csrfToken: '{{ csrf_token() }}'
    };
</script>

<script src="{{ asset('js/konsumen/modules/menu-catalog.js') }}?v={{ file_exists(public_path('js/konsumen/modules/menu-catalog.js')) ? filemtime(public_path('js/konsumen/modules/menu-catalog.js')) : '1.0' }}"></script>
<script src="{{ asset('js/konsumen/modules/menu-cart.js') }}?v={{ file_exists(public_path('js/konsumen/modules/menu-cart.js')) ? filemtime(public_path('js/konsumen/modules/menu-cart.js')) : '1.0' }}"></script>
<script src="{{ asset('js/konsumen/menu-order.js') }}?v={{ file_exists(public_path('js/konsumen/menu-order.js')) ? filemtime(public_path('js/konsumen/menu-order.js')) : '1.0' }}"></script>
