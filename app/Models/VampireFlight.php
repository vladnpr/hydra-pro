<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VampireFlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'combat_shift_id',
        'vampire_flight_plan_id',
        'vampire_drone_id',
        'start_time',
        'end_time',
        'stream_status',
        'mission_type',
        'result',
        'comment',
        'shift_type',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'stream_status' => 'boolean',
        'shift_type' => \App\Enums\ShiftTypeEnum::class,
    ];

    public function combatShift(): BelongsTo
    {
        return $this->belongsTo(CombatShift::class);
    }

    public function flightPlan(): BelongsTo
    {
        return $this->belongsTo(VampireFlightPlan::class, 'vampire_flight_plan_id');
    }

    public function drone(): BelongsTo
    {
        return $this->belongsTo(VampireDrone::class, 'vampire_drone_id')->withTrashed();
    }

    public function ammunition(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Ammunition::class, 'vampire_flight_ammunition', 'vampire_flight_id', 'ammunition_id')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Get the total quantity of ammunition for this flight.
     */
    public function getTotalAmmunitionQuantityAttribute(): int
    {
        return $this->ammunition->sum('pivot.quantity');
    }
}
