<?php

namespace App\Enums;

enum VampireMissionResultsEnum: string
{
    // TODO: make one common mission results for all types of drones
    case SUCCESS = 'worked';
    case FAILURE = 'not_worked';
    case LOSS = 'loss';

    public function label(): string
    {
        return match($this) {
            self::SUCCESS => 'Успішно',
            self::FAILURE => 'Не відпрацювали',
            self::LOSS => 'Втрата борта',
        };
    }
}
