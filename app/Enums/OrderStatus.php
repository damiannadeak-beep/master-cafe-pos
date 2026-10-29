<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Menunggu Pembayaran / Dapur',
            self::PROCESSING => 'Sedang Diproses Dapur',
            self::COMPLETED => 'Selesai',
            self::CANCELLED => 'Dibatalkan',
            self::VOID => 'Void / Dihapus',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'badge-warning',
            self::PROCESSING => 'badge-primary',
            self::COMPLETED => 'badge-success',
            self::CANCELLED, self::VOID => 'badge-danger',
        };
    }
}
