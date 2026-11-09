<?php

namespace App\DTOs;

final readonly class ADFlightsDTO
{
    public function __construct(
        private int $flightId,
        private ?string $coordinate,
        private string $startTime,
        private string $endTime,
        private string $result,
        private int $ammunitionId,
        private int $droneId,
        private string $ammunitionName,
        private string $detonation,
        private ?string $videoPath,
        private string $droneName,
    )
    {
    }

    public function getFlightId(): int
    {
        return $this->flightId;
    }

    public function getCoordinate(): ?string
    {
        return $this->coordinate;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function getResult(): string
    {
        return $this->result;
    }

    public function getAmmunitionId(): int
    {
        return $this->ammunitionId;
    }

    public function getDroneId(): int
    {
        return $this->droneId;
    }

    public function getAmmunitionName(): string
    {
        return $this->ammunitionName;
    }

    public function getDetonation(): string
    {
        return $this->detonation;
    }

    public function getVideoPath(): ?string
    {
        return $this->videoPath;
    }

    public function getDroneName(): string
    {
        return $this->droneName;
    }
}
