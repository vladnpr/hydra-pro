<?php

namespace App\DTOs;

use Carbon\Carbon;

final readonly class FPVFlightDTO
{
    public function __construct(
        private int $id,
        private Carbon $flight_time,
        private string $coordinates,
        private string $mission,
        private string $mission_result,
        private string $drone_name,
        private string $ammunition_name,
        private string $detonation,
        private ?string $video_path,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFlightTime(): Carbon
    {
        return $this->flight_time;
    }

    public function getCoordinates(): string
    {
        return $this->coordinates;
    }

    public function getMission(): string
    {
        return $this->mission;
    }

    public function getMissionResult(): string
    {
        return $this->mission_result;
    }

    public function getDroneName(): string
    {
        return $this->drone_name;
    }

    public function getAmmunitionName(): string
    {
        return $this->ammunition_name;
    }

    public function getDetonation(): string
    {
        return $this->detonation;
    }

    public function getVideoPath(): ?string
    {
        return $this->video_path;
    }
}
