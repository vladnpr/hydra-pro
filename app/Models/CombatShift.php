<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Relations\HasMany;

class CombatShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_id',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function drones(): BelongsToMany
    {
        return $this->belongsToMany(Drone::class, 'combat_shift_drone')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function ammunition(): BelongsToMany
    {
        return $this->belongsToMany(Ammunition::class, 'combat_shift_ammunition')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function crew(): HasMany
    {
        return $this->hasMany(CombatShiftCrew::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(CombatShiftFlight::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'opened' => 'success',
            'closed' => 'secondary',
            default => 'info',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'opened' => 'Відкрито',
            'closed' => 'Закрито',
            default => $this->status,
        };
    }
}
