<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\Meja;

class AdminMejaController extends Controller
{
    public function index()
    {
        $mejas = Meja::orderBy('nama_meja_atau_nomor')->get();
        return view('admin.meja.index', compact('mejas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_meja_atau_nomor' => 'required|string|max:50|unique:meja',
        ]);

        Meja::create($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'nama_meja_atau_nomor' => 'required|string|max:50|unique:meja,nama_meja_atau_nomor,' . $id,
        ]);

        Meja::findOrFail($id)->update($data);
        return redirect()->route('admin.meja.index')->with('success', 'Meja berhasil diupdate.');
    }

    public function destroy($id)
    {
        $meja = Meja::findOrFail($id);

        // 1. Cek apakah ada pesanan yang MASIH AKTIF atau BELUM LUNAS di meja ini
        $activeOrders = $meja->pesanan()
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'processing', 'ready'])
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
            ->count();

        if ($activeOrders > 0) {
            return redirect()->route('admin.meja.index')->with('error', "Meja '{$meja->nama_meja_atau_nomor}' tidak dapat dihapus karena saat ini masih digunakan oleh {$activeOrders} pesanan aktif/belum lunas. Harap selesaikan transaksi meja tersebut terlebih dahulu.");
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 2. Lepaskan relasi id_meja pada riwayat transaksi lama agar histori pembukuan tetap aman
            $meja->pesanan()->update(['id_meja' => null]);

            // 3. Hapus notifikasi terkait meja ini (jika ada)
            \App\Models\Notification::where('id_meja', $meja->id)->delete();

            // 4. Hapus meja secara permanen
            $namaMeja = $meja->nama_meja_atau_nomor;
            $meja->delete();

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('admin.meja.index')->with('success', "Meja '{$namaMeja}' berhasil dihapus.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('admin.meja.index')->with('error', 'Gagal menghapus meja: ' . $e->getMessage());
        }
    }

    public function printQr($id)
    {
        $meja = Meja::findOrFail($id);
        // FIX #4 (IDOR): Generate Signed URL agar ID meja tidak bisa dimanipulasi
        // URL berlaku tanpa batas waktu, namun memiliki signature kriptografi
        $url = URL::signedRoute('konsumen.menu.meja', ['id_meja' => $meja->id]);
        
        return view('admin.meja.print_qr', compact('meja', 'url'));
    }
}

