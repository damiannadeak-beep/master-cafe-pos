<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengeluaran;

class WaitressPengeluaranController extends Controller
{
    public function index(Request $request)
    {
        $pengeluarans = Pengeluaran::where('user_id', auth()->id())
            ->orderBy('tanggal', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('waitress.pengeluaran.index', compact('pengeluarans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'deskripsi' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $data['tanggal'] = date('Y-m-d');
        $data['user_id'] = auth()->id();

        Pengeluaran::create($data);
        return redirect()->route('kasir.pengeluaran.index')->with('success', 'Data pengeluaran berhasil ditambahkan.');
    }
}
