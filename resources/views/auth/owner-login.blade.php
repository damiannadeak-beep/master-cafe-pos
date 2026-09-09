<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Otoritas Pemilik — Master Cafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#0b0f17] text-stone-200 min-h-screen flex items-center justify-center p-4 selection:bg-amber-500 selection:text-black">
    <div class="fixed inset-0 pointer-events-none opacity-20 bg-[radial-gradient(#c08e5c_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <div class="w-full max-w-md bg-[#131926]/90 border border-stone-800 rounded-3xl p-8 sm:p-10 shadow-2xl backdrop-blur-xl relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-amber-600/30 to-amber-400/10 border border-amber-500/30 text-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-amber-900/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Portal Pemilik</h1>
            <p class="text-xs text-stone-400 mt-1">Area terisolasi khusus pemilik & pengelola utama</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-xs text-rose-300 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('owner.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-400 mb-2">Email Pemilik</label>
                <input type="email" name="email" required autofocus value="{{ old('email') }}"
                    class="w-full bg-[#0b0f17]/80 border border-stone-800 rounded-xl px-4 py-3 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-400 mb-2">Kata Sandi</label>
                <input type="password" name="password" required
                    class="w-full bg-[#0b0f17]/80 border border-stone-800 rounded-xl px-4 py-3 text-sm text-white placeholder-stone-600 focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500 transition">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-stone-400 select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#0b0f17] border-stone-700 text-amber-500 focus:ring-0">
                    Ingat sesi perangkat ini
                </label>
            </div>

            <button type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-amber-600 to-amber-700 hover:from-amber-500 hover:to-amber-600 active:scale-[0.99] text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-amber-900/30">
                Masuk ke Dashboard
            </button>
        </form>

        <div class="mt-8 text-center border-t border-stone-800/80 pt-6">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-stone-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500/80 animate-pulse"></span>
                Koneksi Terenkripsi & Dipantau
            </span>
        </div>
    </div>
</body>
</html>
