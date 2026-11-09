<?php

namespace App\DTOs;

use App\Enums\ShiftTypeEnum;
use App\Enums\VampireMissionResultsEnum;
use App\Enums\VampireMissionTypesEnum;

final readonly class VampireFlightDTO
{
    public function __construct(
        private int $flightId,
        private string $startTime,
        private string $endTime,
        private ?string $coordinates,
        private VampireMissionTypesEnum $missionType,
        private VampireMissionResultsEnum $result,
        private ShiftTypeEnum $shiftType,
        private ?string $comment,
        private ?string $videoPath,
        private string $droneName,
        private string $droneSerialNumber,
        private array $ammunition
    )
    {
    }

    public function getFlightId(): int
    {
        return $this->flightId;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function getCoordinates(): ?string
    {
        return $this->coordinates;
    }

    public function getMissionType(): VampireMissionTypesEnum
    {
        return $this->missionType;
    }

    public function getResult(): VampireMissionResultsEnum
    {
        return $this->result;
    }

    public function getShiftType(): ShiftTypeEnum
    {
        return $this->shiftType;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function getDroneName(): string
    {
        return $this->droneName;
    }

    public function getDroneSerialNumber(): string
    {
        return $this->droneSerialNumber;
    }

    public function getAmmunition(): array
    {
        return $this->ammunition;
    }
}
