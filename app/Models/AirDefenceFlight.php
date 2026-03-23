<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirDefenceFlight extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_id',
        'air_defence_drone_id',
        'air_defence_ammunition_id',
        'coordinates',
        'start_time',
        'end_time',
        'stream',
        'result',
        'detonation',
        'comment',
        'video_path',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'detonation' => 'boolean',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function drone(): BelongsTo
    {
        return $this->belongsTo(AirDefenceDrone::class, 'air_defence_drone_id')->withTrashed();
    }

    public function ammunition(): BelongsTo
    {
        return $this->belongsTo(AirDefenceAmmunition::class, 'air_defence_ammunition_id')->withTrashed();
    }
}
