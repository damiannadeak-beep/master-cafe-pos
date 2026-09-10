<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Pengeluaran, User};
use Carbon\Carbon;

class AdminPengeluaranController extends Controller
{
    /**
     * Menampilkan Riwayat / Log Pengeluaran Kasir (Kas Kecil Waitress).
     * Halaman ini Read-Only untuk Owner (tidak ada form input).
     */
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', Carbon::now()->endOfMonth()->toDateString());
        $waitressId = $request->query('waitress_id');

        $query = Pengeluaran::with('user')
            ->whereBetween('tanggal', [$startDate, $endDate]);

        if ($waitressId) {
            $query->where('user_id', $waitressId);
        }

        $totalNominal = (clone $query)->sum('nominal');
        $totalTransaksi = (clone $query)->count();

        $pengeluarans = $query->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $waitresses = User::whereHas('roles', fn($q) => $q->where('name', 'kasir'))->orderBy('name')->get();

        return view('admin.pengeluaran.index', compact(
            'pengeluarans',
            'startDate',
            'endDate',
            'waitressId',
            'totalNominal',
            'totalTransaksi',
            'waitresses'
        ));
    }

    /**
     * Hapus pencatatan jika terjadi kesalahan input oleh waitress (Hak Akses Owner).
     */
    public function destroy($id)
    {
        Pengeluaran::findOrFail($id)->delete();
        return redirect()->route('admin.pengeluaran.index')->with('success', 'Data pengeluaran kasir berhasil dihapus.');
    }
}
