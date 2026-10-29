<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class WaitressLoginController extends Controller
{
    /**
     * Menampilkan formulir login khusus waitress.
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

        return view('auth.waitress-login');
    }

    /**
     * Memproses autentikasi waitress.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Verifikasi role kasir/waitress atau pemilik
            if (!$user->hasRole('kasir') && !$user->hasRole('pemilik')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Akses ditolak. Akun ini tidak memiliki hak akses staf waitress.'
                    ], 403);
                }

                throw ValidationException::withMessages([
                    'email' => 'Akses ditolak. Akun ini tidak memiliki hak akses staf waitress.',
                ]);
            }

            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil sebagai Kasir/Waitress.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->getRoleNames()->first(),
                    ],
                    'redirect_url' => route('kasir.pos'),
                ]);
            }

            return redirect()->intended(route('kasir.pos'));
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Kredensial staf yang diberikan tidak cocok.'
            ], 422);
        }

        throw ValidationException::withMessages([
            'email' => 'Kredensial staf yang diberikan tidak cocok.',
        ]);
    }
}
