<?php

namespace App\Enums;

enum DroneStatusEnum: string
{
    case ACTIVE = 'active';
    case LOST = 'lost';
    case NON_OPERATIONAL = 'non_operational';
    case REPAIR = 'repair';
}
