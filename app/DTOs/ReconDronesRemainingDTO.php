<?php

namespace App\DTOs;

use App\Enums\DroneStatusEnum;
use App\Enums\ShiftTypeEnum;

final readonly class ReconDronesRemainingDTO
{
    public function __construct(
        private int $id,
        private string $drone_name,
        private string $drone_serial_number,
        private  ShiftTypeEnum $drone_shift_type,
        private DroneStatusEnum $drone_status
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDroneName(): string
    {
        return $this->drone_name;
    }

    public function getDroneSerialNumber(): string
    {
        return $this->drone_serial_number;
    }

    public function getDroneShiftType(): ShiftTypeEnum
    {
        return $this->drone_shift_type;
    }

    public function getDroneStatus(): DroneStatusEnum
    {
        return $this->drone_status;
    }
}
