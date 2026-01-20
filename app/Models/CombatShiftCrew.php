<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CombatShiftCrew extends Model
{
    use HasFactory;

    protected $table = 'combat_shift_crew';

    protected $fillable = [
        'combat_shift_id',
        'callsign',
        'role',
    ];

    public function combatShift(): BelongsTo
    {
        return $this->belongsTo(CombatShift::class);
    }
}
