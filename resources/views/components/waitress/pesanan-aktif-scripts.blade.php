<script>
    window.WaitressConfig = Object.assign(window.WaitressConfig || {}, {
        pesananAktifUrl: "{{ route('kasir.pesanan_aktif') }}",
        csrfToken: "{{ csrf_token() }}"
    });
</script>
<script src="{{ asset('js/waitress/pesanan-aktif.js') }}?v={{ file_exists(public_path('js/waitress/pesanan-aktif.js')) ? filemtime(public_path('js/waitress/pesanan-aktif.js')) : '1.0' }}"></script>
