<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;

class WaitressStokController extends Controller
{
    /**
     * Tampilkan halaman status ketersediaan menu untuk waitress
     */
    public function index()
    {
        $menus = Menu::orderBy('kategori')->orderBy('nama_menu')->get();

        return view('waitress.stok.index', compact('menus'));
    }

    /**
     * Update ketersediaan menu secara massal
     */
    public function update(Request $request)
    {
        $request->validate([
            'menu_available' => 'nullable|array',
            'menu_available.*' => 'in:0,1',
        ]);

        if ($request->has('menu_available')) {
            foreach ($request->menu_available as $id => $isAvailable) {
                Menu::where('id', $id)->update([
                    'is_available' => (bool)$isAvailable
                ]);
            }
        }

        return redirect()->back()->with('success', 'Status ketersediaan menu berhasil diperbarui.');
    }

    /**
     * Toggle cepat ketersediaan 1 menu via AJAX atau tombol
     */
    public function toggle(Request $request, $id)
    {
        $menu = Menu::findOrFail($id);
        
        if ($request->has('is_available')) {
            $targetAvailable = filter_var($request->input('is_available'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($targetAvailable !== null) {
                $menu->is_available = $targetAvailable;
            } else {
                $menu->is_available = (bool)$request->input('is_available');
            }
        } else {
            $menu->is_available = !$menu->is_available;
        }
        
        $menu->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_available' => (bool)$menu->is_available,
                'message' => 'Status menu ' . $menu->nama_menu . ' diubah menjadi ' . ($menu->is_available ? 'Tersedia' : 'Habis') . '.'
            ]);
        }

        return redirect()->back()->with('success', 'Status ' . $menu->nama_menu . ' berhasil diubah.');
    }
}
