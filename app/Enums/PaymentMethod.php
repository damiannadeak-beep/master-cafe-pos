<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case QRIS = 'qris';
    case SPLIT = 'split';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Tunai (Cash)',
            self::QRIS => 'QRIS / Midtrans',
            self::SPLIT => 'Split Bill (Bagi Tagihan)',
        };
    }
}
