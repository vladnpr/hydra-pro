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
        'flight_time',
        'stream_status',
        'mission_type',
        'result',
        'comment',
    ];

    protected $casts = [
        'flight_time' => 'datetime',
        'stream_status' => 'boolean',
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
}
