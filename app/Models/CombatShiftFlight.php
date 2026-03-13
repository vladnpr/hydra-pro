<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatShiftFlight extends Model
{
    use HasFactory, SoftDeletes;

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

    protected static function booted()
    {
        static::created(function ($flight) {
            if ($flight->combat_shift_id && $flight->ammunition_id) {
                app(\App\Services\CombatShiftsAdminService::class)->updateAmmunitionQuantity($flight->combat_shift_id, $flight->ammunition_id, -1);
            }
            if ($flight->combat_shift_id && $flight->drone_id) {
                app(\App\Services\CombatShiftsAdminService::class)->updateDroneQuantity($flight->combat_shift_id, $flight->drone_id, -1);
            }
        });

        static::deleted(function ($flight) {
            if ($flight->combat_shift_id && $flight->ammunition_id) {
                app(\App\Services\CombatShiftsAdminService::class)->updateAmmunitionQuantity($flight->combat_shift_id, $flight->ammunition_id, 1);
            }
            if ($flight->combat_shift_id && $flight->drone_id) {
                app(\App\Services\CombatShiftsAdminService::class)->updateDroneQuantity($flight->combat_shift_id, $flight->drone_id, 1);
            }
        });

        static::updating(function ($flight) {
            $oldAmmunitionId = $flight->getOriginal('ammunition_id');
            $newAmmunitionId = $flight->ammunition_id;
            $oldDroneId = $flight->getOriginal('drone_id');
            $newDroneId = $flight->drone_id;

            if ($oldAmmunitionId != $newAmmunitionId) {
                if ($flight->combat_shift_id && $oldAmmunitionId) {
                    app(\App\Services\CombatShiftsAdminService::class)->updateAmmunitionQuantity($flight->combat_shift_id, $oldAmmunitionId, 1);
                }
                if ($flight->combat_shift_id && $newAmmunitionId) {
                    app(\App\Services\CombatShiftsAdminService::class)->updateAmmunitionQuantity($flight->combat_shift_id, $newAmmunitionId, -1);
                }
            }

            if ($oldDroneId != $newDroneId) {
                if ($flight->combat_shift_id && $oldDroneId) {
                    app(\App\Services\CombatShiftsAdminService::class)->updateDroneQuantity($flight->combat_shift_id, $oldDroneId, 1);
                }
                if ($flight->combat_shift_id && $newDroneId) {
                    app(\App\Services\CombatShiftsAdminService::class)->updateDroneQuantity($flight->combat_shift_id, $newDroneId, -1);
                }
            }
        });
    }

    public function position(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Position::class, CombatShift::class, 'id', 'id', 'combat_shift_id', 'position_id');
    }
}
