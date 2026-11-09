<?php

namespace App\Enums;

enum PositionTypesEnum: string
{
    case FPV = 'fpv';
    case RECON = 'recon';
    case VAMPIRE = 'vampire';
    case UGV = 'ugv';
    case AIR_DEFENCE = 'air_defence';

    public function label(): string
    {
        return match($this) {
            self::FPV => 'FPV',
            self::RECON => 'Розвідка',
            self::VAMPIRE => 'Вампір',
            self::UGV => 'НРК',
            self::AIR_DEFENCE => "ППО FPV"
        };
    }
}
