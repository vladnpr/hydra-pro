<?php

namespace App\Enums;

enum ReconMissionTypesEnum: string
{
    case RECON = 'recon';
    case COMBAT = 'combat';
    case OTHER = 'delivery';
}
