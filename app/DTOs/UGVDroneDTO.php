<?php

namespace App\DTOs;

use Carbon\Carbon;

class UGVDroneDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public int $position_id,
        public string $status,
        public Carbon $created_at,
        public Carbon $updated_at,
        public ?Carbon $lost_at,
        public ?Carbon $deleted_at,
    )
    {
    }
}
