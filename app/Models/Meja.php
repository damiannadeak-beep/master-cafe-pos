<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    protected $table = 'meja';
    protected $fillable = ['nama_meja_atau_nomor', 'is_available', 'qr_code'];

    public function getNomorMejaAttribute()
    {
        return $this->nama_meja_atau_nomor;
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_meja');
    }

    /**
     * Sinkronisasi status ketersediaan semua meja berdasarkan pesanan aktif & belum bayar.
     */
    public static function syncAllAvailability(): void
    {
        $occupiedTableIds = Pesanan::whereNotNull('id_meja')
            ->where(function ($q) {
                $q->whereIn('status', ['pending', 'processing'])
                  ->orWhereHas('pembayaran', function ($p) {
                      $p->where('status', 'unpaid');
                  });
            })
            ->whereNotIn('status', ['cancelled', 'void'])
            ->distinct()
            ->pluck('id_meja')
            ->all();

        static::whereNotIn('id', $occupiedTableIds)->where('is_available', false)->update(['is_available' => true]);
        static::whereIn('id', $occupiedTableIds)->where('is_available', true)->update(['is_available' => false]);
    }
}