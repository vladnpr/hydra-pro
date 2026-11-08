<?php

namespace App\Collections;

use App\DTOs\ADDutyReportDTO;
use App\DTOs\FPVDutyReportDTO;
use App\DTOs\ReconDutyReportDTO;
use App\DTOs\UGVDutyReportDTO;
use App\DTOs\VampireDutyReportDTO;

class DutyReportsDTOCollection extends BaseTypedCollection
{

    protected function getTypedClassNames(): array
    {
        return [
            ADDutyReportDTO::class,
            FPVDutyReportDTO::class,
            ReconDutyReportDTO::class,
            VampireDutyReportDTO::class,
            UGVDutyReportDTO::class,
        ];
    }
}
