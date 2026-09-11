<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Setting;
use App\Services\MenuService;
use App\Http\Requests\Admin\{StoreMenuRequest, UpdateMenuRequest};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AdminMenuController extends Controller
{
    public function index(Request $request, MenuService $menuService)
    {
        $menus = $menuService->getPaginatedMenus(
            $request->query('filter'),
            $request->query('category')
        );
        $pageTitle = 'Manajemen Menu';
        $showStockPage = false;

        return view('admin.menu.index', compact('menus', 'pageTitle', 'showStockPage'));
    }

    public function stok(Request $request, MenuService $menuService)
    {
        $menus = $menuService->getPaginatedMenus(
            $request->query('filter'),
            $request->query('category')
        );
        $pageTitle = 'Manajemen Stok';
        $showStockPage = true;

        return view('admin.menu.index', compact('menus', 'pageTitle', 'showStockPage'));
    }

    public function create()
    {
        return view('admin.menu.form', ['menu' => new Menu()]);
    }

    public function store(StoreMenuRequest $request, MenuService $menuService)
    {
        if ($request->hasFile('image') && !$request->file('image')->isValid()) {
            return redirect()->back()->withInput()->with('error', 'Gagal mengunggah foto. Ukuran file foto terlalu besar (maksimal 2MB). Silakan kompres atau pilih foto lain.');
        }
        $menuService->createMenu($request->validated(), $request->all());
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $menu = Menu::findOrFail($id);
        return view('admin.menu.form', compact('menu'));
    }

    public function update(UpdateMenuRequest $request, $id, MenuService $menuService)
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_OK && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errCode = $_FILES['image']['error'];
            $errMsg = "Gagal upload (Kode Error PHP {$errCode}): ";
            if ($errCode == UPLOAD_ERR_INI_SIZE || $errCode == UPLOAD_ERR_FORM_SIZE) {
                $errMsg .= "Ukuran file foto terlalu besar melampaui batas PHP server (" . ini_get('upload_max_filesize') . "). Silakan kompres foto menjadi di bawah 1 MB.";
            } else {
                $errMsg .= "Server menolak file foto ini.";
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }



        $menu = Menu::findOrFail($id);
        $menuService->updateMenu($menu, $request->validated(), $request->all());
        return redirect()->route('admin.menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $menu = Menu::findOrFail($id);

        // 1. Cek apakah menu sedang ada di pesanan aktif (pending / processing)
        $activeOrdersCount = \App\Models\DetailPesanan::where('id_menu', $menu->id)
            ->whereHas('pesanan', function ($q) {
                $q->whereIn('status', ['pending', 'processing']);
            })
            ->count();

        if ($activeOrdersCount > 0) {
            return redirect()->route('admin.menu.index')->with('error', "Produk '{$menu->nama_menu}' tidak dapat dihapus karena saat ini sedang aktif diproses di dapur pada {$activeOrdersCount} pesanan. Harap selesaikan pesanan tersebut terlebih dahulu.");
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // Lepaskan dari resep bahan & paket promo jika ada
            $menu->bahans()->detach();
            \Illuminate\Support\Facades\DB::table('promo_menu')->where('menu_id', $menu->id)->delete();

            // Soft-delete menu
            $namaMenu = $menu->nama_menu;
            $menu->delete();

            \Illuminate\Support\Facades\DB::commit();
            return redirect()->route('admin.menu.index')->with('success', "Produk '{$namaMenu}' berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.menu.index')->with('error', 'Database server belum menjalankan migrasi soft delete. Silakan jalankan `php artisan migrate` di terminal cPanel.');
            }
            return redirect()->route('admin.menu.index')->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->route('admin.menu.index')->with('error', 'Gagal menghapus produk: ' . $e->getMessage());
        }
    }

    public function updateStock(Request $request, $id, MenuService $menuService)
    {
        $data = $request->validate(['stok' => 'required|integer|min:0']);
        $menu = Menu::findOrFail($id);
        $menuService->updateStock($menu, (int) $data['stok']);
        return back()->with('success', 'Stok berhasil diperbarui.');
    }

    public function generateAiDescription(Request $request)
    {
        $request->validate(['nama_menu' => 'required|string']);
        $namaMenu = $request->nama_menu;

        $apiKey = Setting::getVal('gemini_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'API Key Gemini belum dikonfigurasi di Pengaturan.'], 400);
        }

        $prompt = "Buatkan deskripsi makanan/minuman yang menggugah selera untuk menu bernama '{$namaMenu}'. Deskripsi harus singkat, maksimal 2 kalimat, gaya bahasa santai khas Indonesia yang cocok untuk jualan restoran/Master Cafe. Jangan gunakan kata-kata yang terlalu kaku.";

        try {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                return response()->json(['description' => trim($text)]);
            }

            $errorMsg = $response->json('error.message') ?? 'Unknown error';
            Log::error('Gemini API Error: ' . $response->body());
            return response()->json(['error' => 'Gagal menghubungi server AI. ' . $errorMsg], 500);
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem: ' . $e->getMessage()], 500);
        }
    }
}

