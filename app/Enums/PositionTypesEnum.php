<?php

namespace App\Enums;

enum PositionTypesEnum: string
{
    case FPV = 'fpv';
    case RECON = 'recon';
    case VAMPIRE = 'vampire';
    case UGV = 'ugv';
    case AIR_DEFENCE = 'air_defence';
}
