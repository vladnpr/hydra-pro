<?php


namespace App\Services\DutyReports\DutyReportsStrategy;

use App\DTOs\DutyReportCombatShiftDTO;
use App\Enums\PositionTypesEnum;

class DutyReportsContext
{
    private array $context = [
        PositionTypesEnum::FPV->value => FPVReportStrategy::class,
        PositionTypesEnum::AIR_DEFENCE->value => AIrDefenceReportStrategy::class,
        PositionTypesEnum::RECON->value => ReconReportStrategy::class,
        PositionTypesEnum::UGV->value => UGVReportStrategy::class,
        PositionTypesEnum::VAMPIRE->value => VampireReportStrategy::class,
    ];

    private DutyReportStrategy $strategy;


    public function __construct(DutyReportCombatShiftDTO $shift)
    {
        $this->strategy = new $this->context[$shift->getType()];
    }

    public function getReport()
    {
        return $this->strategy->getReport();
    }
}
