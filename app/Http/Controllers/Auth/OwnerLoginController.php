<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class OwnerLoginController extends Controller
{
    /**
     * Menampilkan formulir login khusus pemilik (Owner/Admin).
     */
    public function showLoginForm()
    {
        if (Auth::check() && Auth::user()->hasRole('pemilik')) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.owner-login');
    }

    /**
     * Memproses autentikasi khusus pemilik.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Verifikasi ketat role pemilik
            if (!$user->hasRole('pemilik')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Akses ditolak. Akun ini tidak memiliki hak akses pemilik.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ]);
    }
}
