<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Pengeluaran extends Model
{
    protected $table = 'pengeluarans';
    protected $fillable = ['tanggal', 'deskripsi', 'nominal', 'keterangan', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}