<?php

namespace App\Http\Controllers;

use App\Models\Menu;

class PublicController extends Controller
{
    public function home() {
        $featuredMenus = Menu::where('is_available', true)->take(3)->get();
        return view('public.home', compact('featuredMenus'));
    }

    public function katalog() {
        // Menampilkan semua menu (Read-only), menu tersedia tampil di atas
        $menus = Menu::orderBy('is_available', 'desc')->orderBy('nama_menu', 'asc')->get();
        $promos = \App\Models\Promo::with('menus')->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->get();

        $promoMenuIds = [];
        foreach($promos as $promo) {
            if($promo->type == 'package') {
                foreach($promo->menus as $pm) {
                    $promoMenuIds[] = $pm->id;
                }
            }
        }

        $mejas = \App\Models\Meja::where('is_available', true)->orderBy('nama_meja_atau_nomor', 'asc')->get();

        return view('public.katalog', compact('menus', 'promos', 'promoMenuIds', 'mejas'));
    }

    public function lokasi() {
        return view('public.lokasi');
    }

    public function kontak() {
        return view('public.kontak');
    }
}