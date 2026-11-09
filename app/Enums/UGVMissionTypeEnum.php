<?php

namespace App\Enums;

enum UGVMissionTypeEnum: string
{
    case COMBAT = 'combat';
    case EVACUATION = 'evac';
    case LOGISTICS = 'logistics';

    public function label(): string
    {
        return match($this) {
            self::COMBAT => 'Бойова',
            self::EVACUATION => 'Евакуація',
            self::LOGISTICS => 'Логістика',
        };
    }
}
