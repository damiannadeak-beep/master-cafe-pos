<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Menu extends Model
{
    use LogsActivity;

    protected $table = 'menu';
    protected $fillable = ['nama_menu', 'harga', 'stok', 'image', 'kategori', 'is_available', 'is_dynamic_price', 'variants_json', 'deskripsi'];

    protected $casts = [
        'is_available' => 'boolean',
        'is_dynamic_price' => 'boolean',
        'stok' => 'integer',
        'harga' => 'float',
    ];

    protected $appends = ['image_url'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Menu has been {$eventName}");
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) {
            return 'https://placehold.co/600x450/e9ecef/6c757d?text=Belum+Ada+Foto';
        }
        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }
        $path = str_contains($this->image, '/') ? $this->image : 'menus/' . $this->image;
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return '/storage/' . ltrim($path, '/');
    }

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_menu');
    }

    public function bahans()
    {
        return $this->belongsToMany(Bahan::class, 'bahan_menu', 'menu_id', 'bahan_id')
                    ->withPivot('jumlah_dibutuhkan')
                    ->withTimestamps();
    }
}