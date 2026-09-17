<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tablet Waitress - Master Cafe</title>
    @include("layouts.includes.head-assets")
    <link rel="stylesheet" href="{{ asset('css/waitress-layout.css') }}?v={{ file_exists(public_path('css/waitress-layout.css')) ? filemtime(public_path('css/waitress-layout.css')) : '1.0' }}">
</head>
<body>
    <div id="app" class="kasir-layout">
        @include("layouts.includes.waitress-topbar")

        <main class="kasir-main">
            <div class="kasir-container">
                @yield('content')
            </div>
        </main>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <!-- Local Audio Element for Bell Sound -->
    <audio id="kasirBellAudio" src="{{ asset('sounds/bell.wav') }}" preload="auto"></audio>

    @include('components.webpush')

    <!-- Waitress Layout Global Scripts -->
    <script>
        window.WaitressLayoutConfig = {
            activeOrdersCountUrl: "{{ route('kasir.active_orders_count') }}",
            notificationsUrl: "{{ url('/kasir/api/notifications') }}",
            csrfToken: "{{ csrf_token() }}",
            bellAudioSrc: "{{ asset('sounds/bell.wav') }}"
        };
    </script>
    <script src="{{ asset('js/waitress/toast.js') }}?v={{ file_exists(public_path('js/waitress/toast.js')) ? filemtime(public_path('js/waitress/toast.js')) : '1.0' }}"></script>
    <script src="{{ asset('js/waitress/layout-core.js') }}?v={{ file_exists(public_path('js/waitress/layout-core.js')) ? filemtime(public_path('js/waitress/layout-core.js')) : '1.0' }}"></script>
</body>
</html>
