<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirDefenceAmmunition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'air_defence_ammunition';

    protected $fillable = [
        'name',
        'type',
        'status',
    ];

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'spent' => 'secondary',
            default => 'info',
        };
    }
}
