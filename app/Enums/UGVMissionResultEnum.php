<?php

namespace App\Enums;

enum UGVMissionResultEnum: string
{
    case SUCCESS = 'worked';
    case FAILURE = 'not_worked';
    case LOSS = 'loss';
}
