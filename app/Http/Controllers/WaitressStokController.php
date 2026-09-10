<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class WaitressStokController extends Controller
{
    /**
     * Tampilkan halaman update stok produk waitress
     */
    public function index()
    {
        $menus = Menu::orderBy('nama_menu')->get();

        return view('waitress.stok.index', compact('menus'));
    }

    /**
     * Update stok menu berdasarkan input form
     */
    public function update(Request $request)
    {
        $request->validate([
            'menu' => 'nullable|array',
            'menu.*' => 'integer|min:0',
        ]);

        if ($request->has('menu')) {
            foreach ($request->menu as $id => $stok) {
                $is_available = isset($request->menu_available[$id]) && $request->menu_available[$id] == '1' ? true : false;
                
                if ($stok <= 0) {
                    $is_available = false;
                }

                Menu::where('id', $id)->update([
                    'stok' => $stok,
                    'is_available' => $is_available
                ]);
            }
        }

        return redirect()->back()->with('success', 'Stok produk berhasil diperbarui.');
    }
}
