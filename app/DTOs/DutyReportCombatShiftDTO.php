<?php

namespace App\DTOs;

use App\Enums\CombatShiftStatus;
use App\Enums\PositionTypesEnum;

class DutyReportCombatShiftDTO
{
    public function __construct(
        private readonly int    $combaShiftID,
        private readonly string $positionName,
        private readonly PositionTypesEnum $type,
        private readonly CombatShiftStatus $status,
        private readonly ?int    $userID,
        private readonly string $startedAt
    )
    {
    }

    public function getType(): PositionTypesEnum
    {
        return $this->type;
    }

    public function getCombaShiftID(): int
    {
        return $this->combaShiftID;
    }

    public function getPositionName(): string
    {
        return $this->positionName;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUserID(): ?int
    {
        return $this->userID;
    }

    public function getStartedAt(): string
    {
        return $this->startedAt;
    }
}
