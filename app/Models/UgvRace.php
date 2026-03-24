<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UgvRace extends Model
{
    use HasFactory;

    protected $fillable = [
        'combat_shift_id',
        'ugv_race_plan_id',
        'coordinates',
        'ugv_drone_id',
        'start_time',
        'end_time',
        'stream_status',
        'mission_type',
        'result',
        'comment',
        'shift_type',
        'video_path',
        'checkpoints',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'stream_status' => 'boolean',
        'shift_type' => \App\Enums\ShiftTypeEnum::class,
        'checkpoints' => 'array',
    ];

    public function combatShift(): BelongsTo
    {
        return $this->belongsTo(CombatShift::class);
    }

    public function racePlan(): BelongsTo
    {
        return $this->belongsTo(UgvRacePlan::class, 'ugv_race_plan_id');
    }

    public function drone(): BelongsTo
    {
        return $this->belongsTo(UgvDrone::class, 'ugv_drone_id')->withTrashed();
    }

    public function ammunition(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Ammunition::class, 'ugv_race_ammunition', 'ugv_race_id', 'ammunition_id')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the total quantity of ammunition for this race.
     */
    public function getTotalAmmunitionQuantityAttribute(): int
    {
        return $this->ammunition->sum('pivot.quantity');
    }
}
