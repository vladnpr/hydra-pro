<?php

namespace App\Services\DutyReports\DutyReportsStrategy;

use App\Enums\PositionTypesEnum;

final class DutyReportStrategyResolver
{
    private array $strategies = [
        PositionTypesEnum::FPV->value => FPVReportStrategy::class,
        PositionTypesEnum::AIR_DEFENCE->value => AIrDefenceReportStrategy::class,
        PositionTypesEnum::RECON->value => ReconReportStrategy::class,
        PositionTypesEnum::UGV->value => UGVReportStrategy::class,
        PositionTypesEnum::VAMPIRE->value => VampireReportStrategy::class,
    ];

    public function resolve(PositionTypesEnum $type): DutyReportStrategy
    {
        $strategyClass = $this->strategies[$type->value];
        return app($strategyClass);
    }
}
