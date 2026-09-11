<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SendPromoEmail;
use App\Models\Promo;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PromoActiveMail;

class AdminPromoController extends Controller
{
    public function index()
    {
        $promos = Promo::with('menus')->where('type', 'package')->orderBy('id', 'desc')->paginate(15);
        return view('admin.promo.index', compact('promos'));
    }

    public function create()
    {
        $allMenus = Menu::where('is_available', true)
            ->orderBy('nama_menu', 'asc')
            ->get(['id', 'nama_menu', 'harga', 'kategori', 'image']);

        return view('admin.promo.form', [
            'promo' => new Promo([
                'type' => 'package',
                'is_active' => true,
                'discount_type' => 'nominal'
            ]),
            'allMenus' => $allMenus
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'days' => 'nullable|array',
            'days.*' => 'string',
            'package_menus' => 'required|array|min:1',
            'package_menus.*' => 'nullable|exists:menus,id',
            'package_qty' => 'nullable|array',
            'package_qty.*' => 'nullable|integer|min:1',
        ]);

        $data['type'] = 'package';
        $data['discount_type'] = 'nominal';
        
        // Filter menu kosong
        $validMenus = array_filter($request->input('package_menus', []), fn($m) => !empty($m));
        if (empty($validMenus)) {
            return back()->withInput()->withErrors([
                'package_menus' => 'Paket Hemat (Bundling) wajib menyertakan minimal 1 menu pilihan.'
            ]);
        }

        $data['is_active'] = $request->has('is_active');
        $promo = Promo::create($data);

        // Sync Paket Menus
        if ($request->has('package_menus')) {
            $menus = $request->input('package_menus');
            $qtys = $request->input('package_qty');
            $syncData = [];
            foreach ($menus as $index => $menuId) {
                if (!empty($menuId)) {
                    $qty = isset($qtys[$index]) ? max(1, (int)$qtys[$index]) : 1;
                    $syncData[$menuId] = ['jumlah' => $qty];
                }
            }
            $promo->menus()->sync($syncData);
        }

        // Notifikasi email jika promo langsung aktif
        if ($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())) {
            $users = User::whereNotNull('email')->where('email', '!=', '')->cursor();
            foreach ($users as $u) {
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        
        if (function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($promo)->log('Membuat paket hemat baru: ' . $promo->title);
        }

        return redirect()->route('admin.promo.index')->with('success', 'Paket hemat berhasil disimpan.');
    }

    public function edit($id)
    {
        $promo = Promo::with('menus')->findOrFail($id);
        $allMenus = Menu::where('is_available', true)
            ->orderBy('nama_menu', 'asc')
            ->get(['id', 'nama_menu', 'harga', 'kategori', 'image']);

        return view('admin.promo.form', compact('promo', 'allMenus'));
    }

    public function update(Request $request, $id)
    {
        $promo = Promo::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'days' => 'nullable|array',
            'days.*' => 'string',
            'package_menus' => 'required|array|min:1',
            'package_menus.*' => 'nullable|exists:menus,id',
            'package_qty' => 'nullable|array',
            'package_qty.*' => 'nullable|integer|min:1',
        ]);

        $data['type'] = 'package';
        $data['discount_type'] = 'nominal';
        
        $validMenus = array_filter($request->input('package_menus', []), fn($m) => !empty($m));
        if (empty($validMenus)) {
            return back()->withInput()->withErrors([
                'package_menus' => 'Paket Hemat (Bundling) wajib menyertakan minimal 1 menu pilihan.'
            ]);
        }

        $data['is_active'] = $request->has('is_active');
        $promo->update($data);

        // Sync Paket Menus
        if ($request->has('package_menus')) {
            $menus = $request->input('package_menus');
            $qtys = $request->input('package_qty');
            $syncData = [];
            foreach ($menus as $index => $menuId) {
                if (!empty($menuId)) {
                    $qty = isset($qtys[$index]) ? max(1, (int)$qtys[$index]) : 1;
                    $syncData[$menuId] = ['jumlah' => $qty];
                }
            }
            $promo->menus()->sync($syncData);
        }

        if ($promo->is_active && (!$promo->starts_at || $promo->starts_at <= now())) {
            $users = User::whereNotNull('email')->where('email', '!=', '')->cursor();
            foreach ($users as $u) {
                SendPromoEmail::dispatch($u->id, $promo);
            }
        }
        
        if (function_exists('activity')) {
            activity()->causedBy(auth()->user())->performedOn($promo)->log('Memperbarui paket hemat: ' . $promo->title);
        }

        return redirect()->route('admin.promo.index')->with('success', 'Paket hemat berhasil diperbarui.');
    }

    public function toggleStatus($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->is_active = !$promo->is_active;
        $promo->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $promo->is_active,
                'message' => 'Status promo ' . $promo->title . ' berhasil ' . ($promo->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.'
            ]);
        }

        return back()->with('success', 'Status promo berhasil diubah.');
    }

    public function destroy($id)
    {
        $promo = Promo::findOrFail($id);
        $promo->menus()->detach();
        $promo->delete();

        if (function_exists('activity')) {
            activity()->causedBy(auth()->user())->log('Menghapus promo: ' . $promo->title);
        }

        return redirect()->route('admin.promo.index')->with('success', 'Promo berhasil dihapus.');
    }
}
