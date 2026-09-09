<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Pesanan, Rating};

class KonsumenController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // 1. Ambil Pesanan Aktif (Pending / Processing, ATAU Completed tapi belum dibayar)
        $pesananAktif = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'meja'])
            ->where('id_konsumen', $user->id)
            ->where(function($query) {
                $query->whereIn('status', ['pending', 'processing'])
                      ->orWhere(function($q) {
                          $q->where('status', 'completed')
                            ->whereHas('pembayaran', function($qPay) {
                                $qPay->where('status', '!=', 'paid');
                            });
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Ambil Riwayat Pesanan (Completed & Paid, atau Cancelled) beserta data Ratingnya
        $riwayat = Pesanan::with(['detail_pesanan.menu', 'pembayaran', 'rating', 'meja'])
            ->where('id_konsumen', $user->id)
            ->where(function($query) {
                $query->where('status', 'cancelled')
                      ->orWhere(function($q) {
                          $q->where('status', 'completed')
                            ->whereHas('pembayaran', function($qPay) {
                                $qPay->where('status', 'paid');
                            });
                      });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('konsumen.profil', compact('user', 'pesananAktif', 'riwayat'));
    }

    public function updateProfil(Request $request)
    {
        $user = User::findOrFail(auth()->id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'no_hp' => 'nullable|string|max:15',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ];

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = time() . '_' . uniqid() . '.' . $extension;
            $content = file_get_contents($file->getRealPath());

            // Tulis secara fisik ke seluruh lokasi uploads profil cPanel & lokal
            $destinations = [
                public_path('uploads/profil/' . $filename),
            ];

            if (isset($_SERVER['DOCUMENT_ROOT'])) {
                $destinations[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/profil/' . $filename;
            }

            $homeDir = env('HOME') ?: getenv('HOME') ?: '/home/nadp3189';
            if ($homeDir) {
                $destinations[] = $homeDir . '/public_html/mastercafe.nadeak.net/uploads/profil/' . $filename;
                $destinations[] = $homeDir . '/public_html/uploads/profil/' . $filename;
                $destinations[] = $homeDir . '/repositories/master-cafe-pos/public/uploads/profil/' . $filename;
            }

            foreach (array_unique($destinations) as $dest) {
                try {
                    $dir = dirname($dest);
                    if (!is_dir($dir)) {
                        @mkdir($dir, 0755, true);
                    }
                    @file_put_contents($dest, $content);
                    @chmod($dest, 0644);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal menyimpan foto profil ke {$dest}: " . $e->getMessage());
                }
            }

            // Hapus foto lama jika ada di seluruh destinasi
            if ($user->foto) {
                $oldDests = [
                    public_path('uploads/profil/' . $user->foto),
                ];
                if (isset($_SERVER['DOCUMENT_ROOT'])) {
                    $oldDests[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/uploads/profil/' . $user->foto;
                }
                if ($homeDir) {
                    $oldDests[] = $homeDir . '/public_html/mastercafe.nadeak.net/uploads/profil/' . $user->foto;
                    $oldDests[] = $homeDir . '/public_html/uploads/profil/' . $user->foto;
                    $oldDests[] = $homeDir . '/repositories/master-cafe-pos/public/uploads/profil/' . $user->foto;
                }
                foreach (array_unique($oldDests) as $oldFile) {
                    if (file_exists($oldFile)) {
                        @unlink($oldFile);
                    }
                }
            }

            $data['foto'] = $filename;
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function storeRating(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id',
            'order_token' => 'nullable|string',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'nullable|string|max:500'
        ]);

        $pesanan = \App\Models\Pesanan::findOrFail($request->id_pesanan);
        $token = $request->input('order_token') ?? $request->query('token') ?? session('order_token');

        $isAuthorized = false;
        if (auth()->check() && $pesanan->id_konsumen && $pesanan->id_konsumen == auth()->id()) {
            $isAuthorized = true;
        } elseif ($pesanan->order_token && $token && hash_equals((string)$pesanan->order_token, (string)$token)) {
            $isAuthorized = true;
        } elseif (!$pesanan->id_konsumen && session('active_order_id') == $pesanan->id) {
            $isAuthorized = true;
        }

        if (!$isAuthorized) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Anda tidak berhak memberikan rating untuk pesanan ini.'], 403);
            }
            return back()->withErrors(['id_pesanan' => 'Anda tidak berhak memberikan rating untuk pesanan ini.']);
        }

        Rating::updateOrCreate(
            ['id_pesanan' => $request->id_pesanan],
            [
                'id_konsumen' => auth()->id() ?? null,
                'rating' => $request->rating,
                'komentar' => $request->komentar,
                'tanggal' => now(),
            ]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['message' => 'Terima kasih atas ulasan Anda!']);
        }

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}