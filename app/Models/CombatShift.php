<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CombatShift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'position_id',
        'status',
        'started_at',
        'ended_at',
        'damaged_drones',
        'damaged_coils',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'damaged_drones' => 'array',
        'damaged_coils' => 'array',
    ];

    /**
     * Get the type from the related position.
     */
    public function getTypeAttribute(): ?\App\Enums\PositionTypesEnum
    {
        return $this->position?->type ? \App\Enums\PositionTypesEnum::tryFrom($this->position->type) : null;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'combat_shift_user');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function drones(): BelongsToMany
    {
        return $this->belongsToMany(Drone::class, 'combat_shift_drone')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }


    public function ammunition(): BelongsToMany
    {
        return $this->belongsToMany(Ammunition::class, 'combat_shift_ammunition')
            ->withTrashed()
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function crew(): HasMany
    {
        return $this->hasMany(CombatShiftCrew::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(CombatShiftFlight::class)->whereHas('position', function($q) {
            $q->where('type', \App\Enums\PositionTypesEnum::FPV->value);
        });
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
