<?php

namespace App\Enums;

enum UGVMissionResultEnum: string
{
    case SUCCESS = 'worked';
    case FAILURE = 'not_worked';
    case LOSS = 'loss';

    public function label(): string
    {
        return match($this) {
            self::SUCCESS => 'Успішно',
            self::FAILURE => 'Не відпрацювали',
            self::LOSS => 'Втрата борту',
        };
    }
}
