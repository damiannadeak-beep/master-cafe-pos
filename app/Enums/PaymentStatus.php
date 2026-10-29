<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    case PENDING_VERIFICATION = 'pending_verification';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID => 'Belum Lunas',
            self::PAID => 'Lunas',
            self::PENDING_VERIFICATION => 'Menunggu Verifikasi',
            self::CANCELLED => 'Dibatalkan',
        };
    }
}
