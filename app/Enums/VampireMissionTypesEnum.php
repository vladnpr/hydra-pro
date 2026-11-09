<?php

namespace App\Enums;

enum VampireMissionTypesEnum: string
{
    case LOGISTICS = 'logistics';
    case COMBAT = 'combat';

    public function label(): string
    {
        return match($this) {
            self::LOGISTICS => 'Логістика',
            self::COMBAT => 'Ударна',
        };
    }

}
