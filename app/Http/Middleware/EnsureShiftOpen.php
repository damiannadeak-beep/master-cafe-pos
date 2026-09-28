<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureShiftOpen
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hanya berlaku untuk role 'kasir'
        if (auth()->check() && auth()->user()->hasRole('kasir')) {
            $userId = auth()->id();
            $userShift = auth()->user()->shift ?? 'pagi';

            // Cek apakah kasir ini sudah punya shift yang aktif
            $myShift = \App\Models\KasirShift::where('user_id', $userId)
                ->where('status', 'open')
                ->first();

            // Cek apakah ada kasir lain dalam shift yang sama yang aktif
            $teamShift = \App\Models\KasirShift::where('status', 'open')
                ->where('user_id', '!=', $userId)
                ->whereHas('user', function($q) use ($userShift) {
                    $q->where('shift', $userShift);
                })
                ->first();

            // Jika belum ada shift sama sekali, otomatis inisialisasi shift aktif tanpa memblokir kasir
            if (!$myShift && !$teamShift) {
                \App\Models\KasirShift::create([
                    'user_id' => $userId,
                    'modal_awal' => 0,
                    'waktu_buka' => now(),
                    'status' => 'open'
                ]);
            }

            // Jika mencoba membuka halaman buka shift secara manual, arahkan langsung ke POS
            if ($request->routeIs('kasir.shift.buka') || $request->routeIs('kasir.shift.storeBuka')) {
                return redirect()->route('kasir.pos');
            }
        }

        return $next($request);
    }
}
