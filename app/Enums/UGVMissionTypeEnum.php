<?php

namespace App\Enums;

enum UGVMissionTypeEnum: string
{
    case COMBAT = 'combat';
    case EVACUATION = 'evac';
    case LOGISTICS = 'logistics';
}
