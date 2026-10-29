<?php

namespace App\Enums;

enum OrderType: string
{
    case DINE_IN = 'dine_in';
    case TAKEAWAY = 'takeaway';

    public function label(): string
    {
        return match ($this) {
            self::DINE_IN => 'Makan di Tempat (Dine In)',
            self::TAKEAWAY => 'Bawa Pulang (Takeaway)',
        };
    }
}
