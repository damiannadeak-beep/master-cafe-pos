<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageFallbackController extends Controller
{
    /**
     * Menyajikan file publik/gambar secara fail-safe (terutama pada hosting cPanel tanpa symlink).
     */
    public function show(string $path)
    {
        // Sanitasi path untuk mencegah directory traversal
        $cleanPath = ltrim(str_replace(['../', '..\\'], '', $path), '/\\');

        $searchPaths = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
        ];

        // Dapatkan base home directory dari env/server (fail-safe cPanel)
        $homeDir = env('STORAGE_FALLBACK_ROOT') ?: env('HOME') ?: getenv('HOME');
        $domain = env('APP_DOMAIN', 'mastercafe.nadeak.net');

        if ($homeDir) {
            $searchPaths = array_merge($searchPaths, [
                $homeDir . '/' . $domain . '/public/storage/' . $cleanPath,
                $homeDir . '/' . $domain . '/storage/app/public/' . $cleanPath,
                $homeDir . '/' . $domain . '/storage/' . $cleanPath,
                $homeDir . '/public_html/storage/app/public/' . $cleanPath,
                $homeDir . '/public_html/storage/' . $cleanPath,
                $homeDir . '/public_html/public/storage/' . $cleanPath,
                $homeDir . '/public_html/' . $domain . '/public/storage/' . $cleanPath,
                $homeDir . '/public_html/' . $domain . '/storage/app/public/' . $cleanPath,
                $homeDir . '/public_html/' . $domain . '/storage/' . $cleanPath,
                $homeDir . '/repositories/master-cafe-pos/storage/app/public/' . $cleanPath,
                $homeDir . '/repositories/master-cafe-pos/public/storage/' . $cleanPath,
            ]);
        }

        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $searchPaths[] = $docRoot . '/storage/' . $cleanPath;
            $searchPaths[] = $docRoot . '/public/storage/' . $cleanPath;
            $searchPaths[] = $docRoot . '/storage/app/public/' . $cleanPath;
        }

        foreach (array_unique($searchPaths) as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                $mime = mime_content_type($candidate) ?: 'image/png';
                return response()->file($candidate, [
                    'Content-Type' => $mime,
                    'Cache-Control' => 'public, max-age=86400'
                ]);
            }
        }

        // Fail-safe: jika gambar belum ditemukan, kembalikan logo resmi Master Cafe
        $logoPaths = [
            public_path('images/logo.png'),
            base_path('public/images/logo.png'),
        ];

        if ($homeDir) {
            $logoPaths[] = $homeDir . '/public_html/' . $domain . '/images/logo.png';
            $logoPaths[] = $homeDir . '/repositories/master-cafe-pos/public/images/logo.png';
        }

        foreach ($logoPaths as $logo) {
            if (file_exists($logo) && is_file($logo)) {
                try {
                    return response()->file($logo, [
                        'Content-Type' => 'image/png',
                        'Cache-Control' => 'public, max-age=86400'
                    ]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Logo serve failed: ' . $e->getMessage());
                }
            }
        }

        return redirect('/images/logo.png');
    }
}
