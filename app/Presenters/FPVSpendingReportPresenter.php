<?php

namespace App\Presenters;

use Illuminate\Support\Collection;

class FPVSpendingReportPresenter
{
    public function __construct(
        private readonly int $shiftId,
        private readonly string $positionName,
        private readonly ?Collection $drones,
        private readonly ?Collection $ammunition,
    )
    {
    }

    public function getDrones(): ?Collection
    {
        return $this->drones;
    }

    public function getAmmunition(): ?Collection
    {
        return $this->ammunition;
    }

    public function getPositionName(): string
    {
        return $this->positionName;
    }


    public function getShiftId(): int
    {
        return $this->shiftId;
    }
}
