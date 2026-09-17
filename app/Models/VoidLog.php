<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoidLog extends Model
{
    protected $table = 'void_logs';
    protected $fillable = ['pesanan_id', 'kasir_id', 'alasan', 'total_nilai'];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id')->withTrashed();
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }
}
