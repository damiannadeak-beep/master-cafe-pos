<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PengeluaranBisnis;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class AdminPengeluaranBisnisController extends Controller
{
    /**
     * Menampilkan daftar pengeluaran bisnis / usaha yang dikelola langsung oleh Owner.
     */
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', Carbon::now()->format('Y-m'));
        $kategori = $request->query('kategori');

        try {
            $parsedDate = Carbon::createFromFormat('Y-m', $bulan);
            $startBulan = $parsedDate->copy()->startOfMonth()->toDateString();
            $endBulan = $parsedDate->copy()->endOfMonth()->toDateString();
        } catch (\Exception $e) {
            $bulan = Carbon::now()->format('Y-m');
            $startBulan = Carbon::now()->startOfMonth()->toDateString();
            $endBulan = Carbon::now()->endOfMonth()->toDateString();
        }

        $queryBulanIni = PengeluaranBisnis::whereBetween('tanggal', [$startBulan, $endBulan]);

        // Hitung metrik per kategori
        $totalSemua = (clone $queryBulanIni)->sum('nominal');
        $totalStokBahan = (clone $queryBulanIni)->where('kategori', 'stok_bahan')->sum('nominal');
        $totalGaji = (clone $queryBulanIni)->where('kategori', 'gaji')->sum('nominal');
        $totalOperasional = (clone $queryBulanIni)->where('kategori', 'operasional')->sum('nominal');
        $totalSewa = (clone $queryBulanIni)->where('kategori', 'sewa')->sum('nominal');
        $totalLainnya = (clone $queryBulanIni)->whereIn('kategori', ['pemeliharaan', 'lainnya'])->sum('nominal');

        $listQuery = PengeluaranBisnis::with('user')
            ->whereBetween('tanggal', [$startBulan, $endBulan]);

        if ($kategori) {
            $listQuery->where('kategori', $kategori);
        }

        $pengeluarans = $listQuery->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $kategoriLabels = PengeluaranBisnis::kategoriLabels();

        return view('admin.pengeluaran_bisnis.index', compact(
            'pengeluarans',
            'bulan',
            'kategori',
            'totalSemua',
            'totalStokBahan',
            'totalGaji',
            'totalOperasional',
            'totalSewa',
            'totalLainnya',
            'kategoriLabels'
        ));
    }

    /**
     * Menyimpan data pengeluaran bisnis baru yang diinput oleh Owner/Admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string|in:stok_bahan,gaji,operasional,sewa,pemeliharaan,lainnya',
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
            'bukti_nota' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('bukti_nota') && $request->file('bukti_nota')->isValid()) {
            $file = $request->file('bukti_nota');
            $fileName = 'nota_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/nota'), $fileName);
            $validated['bukti_nota'] = 'uploads/nota/' . $fileName;
        }

        PengeluaranBisnis::create($validated);

        return redirect()->route('admin.pengeluaran_bisnis.index', ['bulan' => Carbon::parse($validated['tanggal'])->format('Y-m')])
            ->with('success', 'Pengeluaran usaha berhasil dicatat.');
    }

    /**
     * Memperbarui data pengeluaran bisnis.
     */
    public function update(Request $request, $id)
    {
        $pengeluaran = PengeluaranBisnis::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori' => 'required|string|in:stok_bahan,gaji,operasional,sewa,pemeliharaan,lainnya',
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string',
            'bukti_nota' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($request->hasFile('bukti_nota') && $request->file('bukti_nota')->isValid()) {
            if ($pengeluaran->bukti_nota && file_exists(public_path($pengeluaran->bukti_nota))) {
                @unlink(public_path($pengeluaran->bukti_nota));
            }
            $file = $request->file('bukti_nota');
            $fileName = 'nota_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/nota'), $fileName);
            $validated['bukti_nota'] = 'uploads/nota/' . $fileName;
        }

        $pengeluaran->update($validated);

        return redirect()->back()->with('success', 'Data pengeluaran berhasil diperbarui.');
    }

    /**
     * Menghapus data pengeluaran bisnis.
     */
    public function destroy($id)
    {
        $pengeluaran = PengeluaranBisnis::findOrFail($id);

        if ($pengeluaran->bukti_nota && file_exists(public_path($pengeluaran->bukti_nota))) {
            @unlink(public_path($pengeluaran->bukti_nota));
        }

        $pengeluaran->delete();

        return redirect()->back()->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}
