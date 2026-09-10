<?php

namespace App\Services;

use App\Models\Menu;
use App\Traits\HandlesImageUpload;

class MenuService
{
    use HandlesImageUpload;

    /**
     * Ambil list menu terpaginasi dengan filter stok dan kategori.
     */
    public function getPaginatedMenus(?string $filter = null, ?string $category = null, int $perPage = 10)
    {
        $query = Menu::query();

        if ($filter === 'low') {
            $query->where('stok', '<', 10);
        }

        if ($category) {
            $query->where('kategori', $category);
        }

        return $query->orderBy('nama_menu')->paginate($perPage)->withQueryString();
    }

    /**
     * Buat menu baru + upload gambar.
     */
    public function createMenu(array $data, array $requestData): Menu
    {
        // Produk baru otomatis berstatus Tersedia dan stok dummy
        $data['is_available'] = true;
        $data['stok'] = 999;

        $imageFile = $requestData['image'] ?? request()->file('image');
        if ($imageFile && $imageFile instanceof \Illuminate\Http\UploadedFile && $imageFile->isValid()) {
            $data['image'] = $this->processImageUpload($imageFile);
        } else {
            unset($data['image']);
        }

        return Menu::create($data);
    }

    /**
     * Update menu + upload gambar baru (jika ada).
     */
    public function updateMenu(Menu $menu, array $data, array $requestData): Menu
    {
        // Pertahankan status ketersediaan yang dikelola oleh waitress
        unset($data['is_available']);
        unset($data['stok']);

        $imageFile = $requestData['image'] ?? request()->file('image');
        if ($imageFile && $imageFile instanceof \Illuminate\Http\UploadedFile && $imageFile->isValid()) {
            $this->deleteOldImage($menu->image);
            $data['image'] = $this->processImageUpload($imageFile);
        } else {
            unset($data['image']);
        }

        $menu->update($data);

        return $menu;
    }

    /**
     * Update stok menu saja.
     */
    public function updateStock(Menu $menu, int $stok): void
    {
        $menu->stok = $stok;
        $menu->save();
    }
}
