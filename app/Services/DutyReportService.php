<?php

namespace App\Services;

use App\Enums\PositionTypesEnum;
use App\Repositories\Contracts\PositionRepositoryInterface;
use App\Repositories\DutyReportRepository;

class DutyReportService
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository,
        private DutyReportRepository $dutyReportRepository
    )
    {
    }

    public function dutyReport(): array
    {
        $activePositons = $this->positionRepository->getActive();

        foreach ($activePositons as $activePositon) {
            if ($activePositon->type === PositionTypesEnum::FPV->value) {
                $inventoryData = $this->dutyReportRepository->fpvInventoryData($activePositon->id);
            }
        }

        return [];
    }
}
