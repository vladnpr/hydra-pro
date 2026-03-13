<?php

namespace App\Enums;

enum ShiftTypeEnum: string
{
    case DAY = 'day';
    case NIGHT = 'night';
    case BOTH = 'both';

    public function label(): string
    {
        return match($this) {
            self::DAY => 'Денна',
            self::NIGHT => 'Нічна',
            self::BOTH => 'Обидві',
        };
    }
}
