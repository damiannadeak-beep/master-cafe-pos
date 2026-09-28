<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request; // WAJIB DITAMBAHKAN UNTUK MENANGKAP REQUEST

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Tentukan batas maksimal percobaan login (rate limiting / anti brute-force).
     *
     * @var int
     */
    protected $maxAttempts = 3;

    /**
     * Tentukan waktu blokir (dalam menit) jika melebihi batas percobaan login.
     *
     * @var int
     */
    protected $decayMinutes = 2;

    /**
     * Where to redirect users after login (Ini hanya fallback default).
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Keluar dari sesi secara aman & anggun.
     * Mencegah error 419 Page Expired atau loop redirect jika sesi sudah kadaluarsa.
     */
    public function logout(Request $request)
    {
        $redirectUrl = '/';

        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            if ($user->hasRole('pemilik')) {
                $redirectUrl = route('owner.login');
            } elseif ($user->hasRole('kasir')) {
                $redirectUrl = route('kasir.login');
            }

            \Illuminate\Support\Facades\Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirectUrl);
    }

    /**
     * The user has been authenticated.
     * Fungsi ini akan dieksekusi otomatis SETELAH user berhasil login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        // Cek jabatan (role) menggunakan Spatie dan arahkan ke URL yang sesuai
        if ($user->hasRole('pemilik')) {
            return redirect()->route('admin.dashboard');
        } 
        
        if ($user->hasRole('kasir')) {
            return redirect()->route('kasir.pos');
        } 
        
        if ($user->hasRole('konsumen')) {
            return redirect('/'); // Diarahkan ke beranda
        }

        // Jika tidak punya role apapun, kembalikan ke halaman awal
        return redirect('/');
    }

    /**
     * Tampilkan halaman login khusus Staf Kasir & Pemilik.
     */
    public function showStaffLoginForm()
    {
        return view('auth.staff-login');
    }
}