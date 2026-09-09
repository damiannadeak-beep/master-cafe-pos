<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KasirLoginController extends Controller
{
    /**
     * Menampilkan formulir login khusus kasir.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            if (Auth::user()->hasRole('kasir')) {
                return redirect()->route('kasir.pos');
            }
            if (Auth::user()->hasRole('pemilik')) {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('auth.kasir-login');
    }

    /**
     * Memproses autentikasi kasir.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Verifikasi role kasir atau pemilik
            if (!$user->hasRole('kasir') && !$user->hasRole('pemilik')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Akses ditolak. Akun ini tidak memiliki hak akses kasir.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('kasir.pos'));
        }

        throw ValidationException::withMessages([
            'email' => 'Kredensial kasir yang diberikan tidak cocok.',
        ]);
    }
}
