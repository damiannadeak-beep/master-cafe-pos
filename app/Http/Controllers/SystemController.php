<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SystemController extends Controller
{
    /**
     * Clear application optimization cache.
     */
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        return redirect()->back()->with('success', 'Cache berhasil dibersihkan.');
    }

    /**
     * End guest consumer order session and clear session tokens.
     */
    public function selesaiSesi(Request $request)
    {
        session()->forget(['order_token', 'active_order_id']);
        $redirectUrl = $request->query('redirect', url('/katalog'));
        return redirect($redirectUrl);
    }

    /**
     * Profile picture fallback handler.
     */
    public function profileImageFallback($filename)
    {
        $cleanName = basename($filename);
        $searchPaths = [
            public_path('uploads/profil/' . $cleanName),
            base_path('public/uploads/profil/' . $cleanName),
            '/home/nadp3189/repositories/master-cafe-pos/public/uploads/profil/' . $cleanName,
            '/home/nadp3189/public_html/mastercafe.nadeak.net/uploads/profil/' . $cleanName,
            '/home/nadp3189/public_html/uploads/profil/' . $cleanName,
        ];

        foreach ($searchPaths as $path) {
            if (file_exists($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mimeType = match ($ext) {
                    'png' => 'image/png',
                    'webp' => 'image/webp',
                    'gif' => 'image/gif',
                    default => 'image/jpeg',
                };
                return response()->file($path, ['Content-Type' => $mimeType]);
            }
        }

        $name = auth()->check() ? auth()->user()->name : 'User';
        return redirect('https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=c08e5c&color=fff&size=120');
    }
}
