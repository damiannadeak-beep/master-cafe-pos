<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengeluaranBisnis extends Model
{
    protected $table = 'pengeluaran_bisnis';

    protected $fillable = [
        'tanggal',
        'kategori',
        'deskripsi',
        'nominal',
        'keterangan',
        'bukti_nota',
        'user_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function kategoriLabels(): array
    {
        return [
            'stok_bahan' => 'Belanja Stok Bahan Baku',
            'gaji' => 'Gaji & Insentif Karyawan',
            'operasional' => 'Operasional & Utilitas',
            'sewa' => 'Sewa Tempat & Bangunan',
            'pemeliharaan' => 'Pemeliharaan & Perlengkapan',
            'lainnya' => 'Pengeluaran Lainnya',
        ];
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::kategoriLabels()[$this->kategori] ?? ucfirst($this->kategori);
    }
}
