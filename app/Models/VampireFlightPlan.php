<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VampireFlightPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'combat_shift_id',
        'position_name',
        'coordinates',
        'order',
        'status',
    ];

    public function combatShift(): BelongsTo
    {
        return $this->belongsTo(CombatShift::class);
    }

    public function flights(): HasMany
    {
        return $this->hasMany(VampireFlight::class);
    }
}
