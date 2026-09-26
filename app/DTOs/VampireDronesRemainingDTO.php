<?php

namespace App\DTOs;

use App\Enums\DroneStatusEnum;
use App\Enums\ShiftTypeEnum;

class VampireDronesRemainingDTO
{
    public function __construct(
        public int $drone_id,
        public string $drone_name,
        public string $drone_serial_number,
        public ShiftTypeEnum $shift_type,
        public DroneStatusEnum $drone_status,
    )
    {
    }
}
