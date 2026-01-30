<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatShiftFlight extends Model
{
    use HasFactory;

    protected $fillable = [
        'combat_shift_id',
        'drone_id',
        'ammunition_id',
        'coordinates',
        'flight_time',
        'result',
        'detonation',
        'stream',
        'note',
        'video_path',
    ];

    protected $casts = [
        'flight_time' => 'datetime',
    ];

    public function combatShift(): BelongsTo
    {
        return $this->belongsTo(CombatShift::class)->withTrashed();
    }

    public function drone(): BelongsTo
    {
        return $this->belongsTo(Drone::class)->withTrashed();
    }

    public function ammunition(): BelongsTo
    {
        return $this->belongsTo(Ammunition::class)->withTrashed();
    }
}
