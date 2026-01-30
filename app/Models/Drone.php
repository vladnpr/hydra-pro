<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Drone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'model',
        'status',
    ];

    /**
     * Get the status color for AdminLTE badges.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            1 => 'success',
            0 => 'danger',
            default => 'info',
        };
    }
}
