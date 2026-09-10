<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermintaanBelanja;
use App\Models\Bahan;

class PermintaanBelanjaController extends Controller
{
    // ==========================================
    // SISI ADMIN
    // ==========================================

    public function adminIndex()
    {
        $permintaans = PermintaanBelanja::with('user')
                        ->orderByRaw("FIELD(status, 'menunggu', 'sudah_dibeli', 'ditolak')")
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
                        
        return view('admin.permintaan_belanja.index', compact('permintaans'));
    }

    public function adminUpdateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:menunggu,sudah_dibeli,ditolak'
        ]);

        $permintaan = PermintaanBelanja::findOrFail($id);
        $permintaan->update(['status' => $request->status]);

        // Notify Kasir
        $kasirs = \App\Models\User::role('kasir')->get();
        $statusText = $request->status == 'sudah_dibeli' ? 'Sudah Dibeli' : 'Ditolak';
        \Illuminate\Support\Facades\Notification::send($kasirs, new \App\Notifications\WebPushNotification(
            'Status Belanja Update',
            'Permintaan ' . $permintaan->nama_barang . ' ' . $statusText . ' oleh Admin.',
            '/kasir/permintaan-belanja'
        ));

        return redirect()->back()->with('success', 'Status permintaan berhasil diperbarui.');
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|array',
            'nama_barang.*' => 'required|string|max:255',
            'jumlah_ditambah' => 'required|array',
            'jumlah_ditambah.*' => 'required|string|max:255',
            'catatan' => 'nullable|string'
        ]);

        foreach ($request->nama_barang as $index => $nama) {
            $jumlah = $request->jumlah_ditambah[$index];

            PermintaanBelanja::create([
                'user_id' => auth()->id(),
                'nama_barang' => $nama,
                'sisa_stok' => null,
                'jumlah_diminta' => $jumlah,
                'catatan' => $request->catatan,
                'status' => 'sudah_dibeli'
            ]);
        }

        // Notify Kasir
        $kasirs = \App\Models\User::role('kasir')->get();
        \Illuminate\Support\Facades\Notification::send($kasirs, new \App\Notifications\WebPushNotification(
            'Admin Menambah Barang',
            'Admin telah mencatat pembelian baru. Cek riwayat belanja.',
            '/kasir/permintaan-belanja'
        ));

        return redirect()->back()->with('success', 'Daftar belanja berhasil dicatat ke riwayat.');
    }
}
