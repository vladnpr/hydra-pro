<?php

namespace App\Models;

use App\Enums\ShiftTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconDrone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial_number',
        'status',
        'position_id',
        'shift_type',
    ];

    protected $casts = [
        'shift_type' => ShiftTypeEnum::class,
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the status color for AdminLTE badges.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'lost' => 'danger',
            'repair' => 'warning',
            'non_operational' => 'secondary',
            default => 'info',
        };
    }
}
