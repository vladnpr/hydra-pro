<?php

namespace App\Enums;

enum DroneShiftRestrictionEnum: string
{
    case DAY = 'day';
    case NIGHT = 'night';
    case BOTH = 'both';

    public function label(): string
    {
        return match($this) {
            self::DAY => 'Лише денна',
            self::NIGHT => 'Лише нічна',
            self::BOTH => 'Обидві',
        };
    }
}
