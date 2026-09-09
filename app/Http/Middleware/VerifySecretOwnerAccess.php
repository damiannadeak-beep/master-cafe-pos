<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySecretOwnerAccess
{
    /**
     * Menjaga jalur rahasia pemilik dengan verifikasi kunci URL (Secret Key).
     * Jika tidak cocok atau mencoba menebak, sistem menyamarkan respon menjadi 404 Not Found murni.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requiredKey = config('auth.owner_key');

        if (!empty($requiredKey)) {
            // Jika belum terverifikasi di sesi browser saat ini
            if ($request->session()->get('owner_gate_passed') !== true) {
                // Periksa apakah URL menyertakan parameter ?key= yang valid
                if ($request->query('key') === $requiredKey) {
                    $request->session()->put('owner_gate_passed', true);
                } else {
                    // Decoy: Berikan 404 Not Found seolah rute tidak pernah ada
                    abort(404);
                }
            }
        }

        return $next($request);
    }
}
