<script>
    window.WaitressConfig = Object.assign(window.WaitressConfig || {}, {
        pesananAktifUrl: "{{ route('kasir.pesanan_aktif') }}",
        ordersCountUrl: "{{ route('kasir.active_orders_count') }}",
        csrfToken: "{{ csrf_token() }}"
    });
</script>

{{-- Modular Waitress Active Orders Scripts --}}
@php
    $modules = [
        'js/waitress/pesanan-aktif.js',
        'js/waitress/modules/modal.js',
        'js/waitress/modules/sync.js',
        'js/waitress/modules/status.js',
        'js/waitress/modules/payment.js',
        'js/waitress/modules/void.js',
        'js/waitress/modules/split-bill.js',
        'js/waitress/modules/verification.js',
    ];
@endphp

@foreach ($modules as $mod)
    <script src="{{ asset($mod) }}?v={{ file_exists(public_path($mod)) ? filemtime(public_path($mod)) : '1.0' }}"></script>
@endforeach
