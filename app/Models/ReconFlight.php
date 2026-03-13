<?php

namespace App\Models;

use App\Enums\ReconMissionResultsEnum;
use App\Enums\ReconMissionTypesEnum;
use App\Enums\ShiftTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconFlight extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'combat_shift_id',
        'recon_drone_id',
        'recon_ammunition_id',
        'mission_type',
        'coordinates',
        'flight_time',
        'result',
        'shift_type',
        'stream_status',
        'video_path',
        'description',
    ];

    protected $casts = [
        'flight_time' => 'datetime',
        'mission_type' => ReconMissionTypesEnum::class,
        'result' => ReconMissionResultsEnum::class,
        'shift_type' => ShiftTypeEnum::class,
        'stream_status' => 'boolean',
    ];

    public function ammunition(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Ammunition::class, 'recon_flight_ammunition', 'recon_flight_id', 'ammunition_id')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function drone(): BelongsTo
    {
        return $this->belongsTo(ReconDrone::class, 'recon_drone_id')->withTrashed();
    }

    /**
     * Get the total quantity of ammunition for this flight.
     */
    public function getTotalAmmunitionQuantityAttribute(): int
    {
        return $this->ammunition->sum('pivot.quantity');
    }
}
