<?php

namespace App\DTOs;

use App\Enums\ShiftTypeEnum;
use App\Enums\UGVMissionResultEnum;
use App\Enums\UGVMissionTypeEnum;
use Carbon\Carbon;

class UGVRaceDTO
{
    public function __construct(
        private int $raceID,
        private int $combatShiftID,
        private int $droneID,
        private string $droneName,
        private string $droneSerialNumber,
        private ?array $ammunition,
        private ?string $coordinates,
        private UGVMissionTypeEnum $missionType,
        private UGVMissionResultEnum $result,
        private ?string $comment,
        private ShiftTypeEnum $shiftType,
        private ?string $videoPath,
        private Carbon $startTime,
        private Carbon $endTime
    )
    {
    }

    public function getRaceID(): int
    {
        return $this->raceID;
    }

    public function getCombatShiftID(): int
    {
        return $this->combatShiftID;
    }

    public function getDroneID(): int
    {
        return $this->droneID;
    }

    public function getDroneName(): string
    {
        return $this->droneName;
    }

    public function getDroneSerialNumber(): string
    {
        return $this->droneSerialNumber;
    }

    public function getAmmunition(): ?array
    {
        return $this->ammunition;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function getMissionType(): UGVMissionTypeEnum
    {
        return $this->missionType;
    }

    public function getResult(): UGVMissionResultEnum
    {
        return $this->result;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getShiftType(): ShiftTypeEnum
    {
        return $this->shiftType;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function getStartTime(): Carbon
    {
        return $this->startTime;
    }

    public function getEndTime(): Carbon
    {
        return $this->endTime;
    }
}
