<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;

class KasirMejaController extends Controller
{
    /**
     * Menampilkan daftar meja untuk dikelola Kasir
     */
    public function index(Request $request)
    {
        $mejas = Meja::with(['pesanan' => function ($q) {
            $q->where(function ($sub) {
                $sub->whereIn('status', ['pending', 'processing', 'ready'])
                    ->orWhere(function ($comp) {
                        $comp->where('status', 'completed')
                             ->where(function ($pSub) {
                                 $pSub->whereDoesntHave('pembayaran')
                                      ->orWhereHas('pembayaran', function ($p) {
                                          $p->where('status', '!=', 'paid');
                                      });
                             });
                    });
            })
            ->with(['konsumen', 'pembayaran', 'detailPesanan.menu'])
            ->latest();
        }])->orderBy('nama_meja_atau_nomor')->get();

        $totalMeja = $mejas->count();
        $mejaAdaPesanan = $mejas->filter(fn($m) => $m->pesanan->isNotEmpty())->count();
        $mejaKosong = $totalMeja - $mejaAdaPesanan;

        if ($request->ajax() || $request->wantsJson() || $request->query('grid_only')) {
            return view('kasir.meja.grid', compact('mejas', 'totalMeja', 'mejaAdaPesanan', 'mejaKosong'))->render();
        }

        return view('kasir.meja.index', compact('mejas', 'totalMeja', 'mejaAdaPesanan', 'mejaKosong'));
    }

    /**
     * Mengubah status ketersediaan meja (Tersedia / Terisi)
     */
    public function toggle($id)
    {
        try {
            $meja = Meja::findOrFail($id);
            $meja->update([
                'is_available' => !$meja->is_available
            ]);

            $statusName = $meja->is_available ? 'Tersedia' : 'Terisi';

            // Broadcast real-time event ke kasir lain
            try {
                broadcast(new \App\Events\MejaStatusUpdated($meja));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[KasirMejaController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            return response()->json([
                'message' => 'Status meja berhasil diubah menjadi ' . $statusName,
                'is_available' => $meja->is_available
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
