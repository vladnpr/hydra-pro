<?php

namespace App\DTOs;

use App\Enums\ReconMissionResultsEnum;
use App\Enums\ReconMissionTypesEnum;
use Carbon\Carbon;

class ReconFlightDTO
{
    public function __construct(
        private int                     $id,
        private ?string                 $flightCoordinates,
        private string                  $droneName,
        private string                  $droneSeriaNumber,
        private Carbon                  $startTime,
        private Carbon                  $landingTime,
        private ReconMissionTypesEnum   $missionType,
        private ?string                 $targetName,
        private ReconMissionResultsEnum $result,
        private ?string                 $description,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFlightCoordinates(): ?string
    {
        return $this->flightCoordinates;
    }

    public function getDroneName(): string
    {
        return $this->droneName;
    }

    public function getDroneSeriaNumber(): string
    {
        return $this->droneSeriaNumber;
    }

    public function getStartTime(): Carbon
    {
        return $this->startTime;
    }

    public function getLandingTime(): Carbon
    {
        return $this->landingTime;
    }

    public function getMissionType(): ReconMissionTypesEnum
    {
        return $this->missionType;
    }

    public function getTargetName(): ?string
    {
        return $this->targetName;
    }

    public function getResult(): ReconMissionResultsEnum
    {
        return $this->result;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
