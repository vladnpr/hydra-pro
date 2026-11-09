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

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPositionId(): int
    {
        return $this->position_id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    public function getLostAt(): ?Carbon
    {
        return $this->lost_at;
    }

    public function getDeletedAt(): ?Carbon
    {
        return $this->deleted_at;
    }
}
