<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Gerbang Kasir POS — Master Cafe</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#0c121e] text-slate-200 min-h-screen flex items-center justify-center p-4 selection:bg-emerald-500 selection:text-black">
    <div class="fixed inset-0 pointer-events-none opacity-15 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:24px_24px]"></div>

    <div class="w-full max-w-md bg-[#161f30]/90 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl backdrop-blur-xl relative z-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-gradient-to-tr from-emerald-600/30 to-teal-400/10 border border-emerald-500/30 text-emerald-400 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-900/20">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-white">Gerbang Kasir POS</h1>
            <p class="text-xs text-slate-400 mt-1">Sistem operasional kasir & manajemen meja</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-500/10 border border-rose-500/20 rounded-2xl text-xs text-rose-300 flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('kasir.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Email Kasir / Akun POS</label>
                <input type="email" name="email" required autofocus value="{{ old('email') }}"
                    class="w-full bg-[#0c121e]/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Kata Sandi</label>
                <input type="password" name="password" required
                    class="w-full bg-[#0c121e]/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition">
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-slate-400 select-none">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-[#0c121e] border-slate-700 text-emerald-500 focus:ring-0">
                    Ingat sesi perangkat kasir ini
                </label>
            </div>

            <button type="submit" 
                class="w-full py-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 active:scale-[0.99] text-white font-semibold text-sm rounded-xl transition shadow-lg shadow-emerald-900/30">
                Buka Mesin Kasir POS
            </button>
        </form>

        <div class="mt-8 text-center border-t border-slate-800/80 pt-6">
            <span class="inline-flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Terminal Kasir Terproteksi
            </span>
        </div>
    </div>
</body>
</html>
