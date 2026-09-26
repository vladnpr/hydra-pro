<?php

namespace App\DTOs;

use App\Enums\CombatShiftStatus;
use App\Enums\PositionTypesEnum;
readonly class DutyReportCombatShiftDTO
{
    public function __construct(
        private int    $combatShiftID,
        private int    $positionID,
        private string $positionName,
        private PositionTypesEnum $type,
        private CombatShiftStatus $status,
        private ?int    $userID,
        private string $startedAt
    )
    {
    }

    public function getPositionID(): int
    {
        return $this->positionID;
    }

    public function getType(): PositionTypesEnum
    {
        return $this->type;
    }

    public function getCombatShiftID(): int
    {
        return $this->combatShiftID;
    }

    public function getPositionName(): string
    {
        return $this->positionName;
    }

    public function getStatus(): CombatShiftStatus
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
