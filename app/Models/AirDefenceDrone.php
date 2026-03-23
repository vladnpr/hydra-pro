<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AirDefenceDrone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'model',
        'status',
    ];

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'success',
            'lost' => 'danger',
            'repair' => 'warning',
            default => 'info',
        };
    }
}
