<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $fillable = [
        'id_pesanan', 'metode', 'status', 'total_bayar', 'bukti_bayar', 'snap_token', 'tanggal',
        'uang_diterima', 'uang_kembalian', 'catatan_kembalian', 'midtrans_order_id',
    ];

    public function getStatusEnumAttribute(): ?\App\Enums\PaymentStatus
    {
        return \App\Enums\PaymentStatus::tryFrom($this->status);
    }

    public function getMethodEnumAttribute(): ?\App\Enums\PaymentMethod
    {
        return \App\Enums\PaymentMethod::tryFrom($this->metode);
    }

    public function isPaid(): bool
    {
        return $this->status === \App\Enums\PaymentStatus::PAID->value;
    }

    public function isUnpaid(): bool
    {
        return $this->status === \App\Enums\PaymentStatus::UNPAID->value;
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }
}