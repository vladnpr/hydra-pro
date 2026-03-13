<?php

namespace App\Enums;

enum ShiftTypeEnum: string
{
    case DAY = 'day';
    case NIGHT = 'night';

    public function label(): string
    {
        return match($this) {
            self::DAY => 'Денна',
            self::NIGHT => 'Нічна',
        };
    }
}
