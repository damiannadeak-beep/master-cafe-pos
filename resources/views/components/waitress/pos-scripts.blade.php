<script>
    window.allMenus = {!! json_encode($menus) !!};
    window.defaultLogoUrl = "{{ asset('images/logo.png') }}";
    window.printerActive = {{ \App\Models\Setting::getVal('printer_active') == '1' ? 'true' : 'false' }};
    window.csrfToken = "{{ csrf_token() }}";
    window.manualOrderUrl = "{{ url('/kasir/manual-order') }}";
</script>
<script src="{{ asset('js/waitress/pos-order.js') }}?v={{ file_exists(public_path('js/waitress/pos-order.js')) ? filemtime(public_path('js/waitress/pos-order.js')) : '1.0' }}"></script>
