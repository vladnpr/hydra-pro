<?php

namespace App\Enums;

enum ReconMissionResultsEnum: string
{
    case SUCCESS = 'success';
    case BOARD_LOOSED = 'board_loosed';
    case OTHER = 'other';

}
