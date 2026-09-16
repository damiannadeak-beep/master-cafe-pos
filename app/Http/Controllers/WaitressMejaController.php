<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meja;

class WaitressMejaController extends Controller
{
    /**
     * Menampilkan daftar meja untuk dikelola Waitress
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
            ->with(['konsumen', 'pembayaran', 'detail_pesanan.menu'])
            ->latest();
        }])->orderBy('nama_meja_atau_nomor')->get();

        $totalMeja = $mejas->count();
        $mejaAdaPesanan = $mejas->filter(fn($m) => $m->pesanan->isNotEmpty())->count();
        $mejaKosong = $totalMeja - $mejaAdaPesanan;

        // Ambil panggilan meja aktif (unread call_bell notifications)
        $activeCallsMap = \App\Models\Notification::where('type', 'call_bell')
            ->where('is_read', false)
            ->whereNotNull('id_meja')
            ->get()
            ->keyBy('id_meja');

        if ($request->ajax() || $request->wantsJson() || $request->query('grid_only')) {
            return view('waitress.meja.grid', compact('mejas', 'totalMeja', 'mejaAdaPesanan', 'mejaKosong', 'activeCallsMap'))->render();
        }

        return view('waitress.meja.index', compact('mejas', 'totalMeja', 'mejaAdaPesanan', 'mejaKosong', 'activeCallsMap'));
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

            try {
                broadcast(new \App\Events\MejaStatusUpdated($meja));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[WaitressMejaController] Gagal broadcast WebSocket: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Status meja ' . $meja->nama_meja_atau_nomor . ' diubah menjadi ' . $statusName,
                'is_available' => $meja->is_available
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah status meja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memindahkan pesanan aktif dari meja asal ke meja tujuan
     */
    public function pindahMeja(Request $request)
    {
        $request->validate([
            'from_meja_id' => 'required|exists:meja,id',
            'to_meja_id' => 'required|exists:meja,id|different:from_meja_id',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $fromMeja = Meja::findOrFail($request->from_meja_id);
            $toMeja = Meja::findOrFail($request->to_meja_id);

            $activeOrders = \App\Models\Pesanan::where('id_meja', $fromMeja->id)
                ->whereIn('status', ['pending', 'processing', 'ready'])
                ->get();

            if ($activeOrders->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada pesanan aktif di meja ini.'], 422);
            }

            foreach ($activeOrders as $order) {
                $order->update(['id_meja' => $toMeja->id]);
            }

            // Meja asal sekarang kosong, meja tujuan sekarang terisi
            $fromMeja->update(['is_available' => true]);
            $toMeja->update(['is_available' => false]);

            \Illuminate\Support\Facades\DB::commit();

            try {
                broadcast(new \App\Events\MejaStatusUpdated($fromMeja));
                broadcast(new \App\Events\MejaStatusUpdated($toMeja));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[WaitressMejaController] Gagal broadcast WebSocket pindah meja: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => "Pesanan berhasil dipindahkan dari {$fromMeja->nama_meja_atau_nomor} ke {$toMeja->nama_meja_atau_nomor}.",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal memindahkan meja: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Menggabungkan pesanan dari satu meja ke meja tujuan
     */
    public function gabungMeja(Request $request)
    {
        $request->validate([
            'source_meja_id' => 'required|exists:meja,id',
            'target_meja_id' => 'required|exists:meja,id|different:source_meja_id',
        ]);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $sourceMeja = Meja::findOrFail($request->source_meja_id);
            $targetMeja = Meja::findOrFail($request->target_meja_id);

            $sourceOrders = \App\Models\Pesanan::where('id_meja', $sourceMeja->id)
                ->whereIn('status', ['pending', 'processing', 'ready'])
                ->get();

            if ($sourceOrders->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak ada pesanan aktif di meja asal.'], 422);
            }

            foreach ($sourceOrders as $order) {
                $order->update(['id_meja' => $targetMeja->id]);
            }

            $sourceMeja->update(['is_available' => true]);
            $targetMeja->update(['is_available' => false]);

            \Illuminate\Support\Facades\DB::commit();

            try {
                broadcast(new \App\Events\MejaStatusUpdated($sourceMeja));
                broadcast(new \App\Events\MejaStatusUpdated($targetMeja));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[WaitressMejaController] Gagal broadcast WebSocket gabung meja: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => "Meja {$sourceMeja->nama_meja_atau_nomor} berhasil digabungkan ke {$targetMeja->nama_meja_atau_nomor}.",
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal menggabungkan meja: ' . $e->getMessage()], 500);
        }
    }
}
