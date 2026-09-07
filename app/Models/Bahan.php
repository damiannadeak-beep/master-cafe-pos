<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Bahan extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['nama_bahan', 'satuan', 'stok', 'harga_beli'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Bahan has been {$eventName}");
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'bahan_menu', 'bahan_id', 'menu_id')
                    ->withPivot('jumlah_dibutuhkan')
                    ->withTimestamps();
    }
}