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
            self::DAY => 'День',
            self::NIGHT => 'Ніч',
            self::BOTH => 'День/Ніч',
        };
    }
}
