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

    public function getDroneId(): int
    {
        return $this->drone_id;
    }

    public function getDroneName(): string
    {
        return $this->drone_name;
    }

    public function getDroneSerialNumber(): string
    {
        return $this->drone_serial_number;
    }

    public function getShiftType(): ShiftTypeEnum
    {
        return $this->shift_type;
    }

    public function getDroneStatus(): DroneStatusEnum
    {
        return $this->drone_status;
    }
}
