<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drone extends Model
{
    use HasFactory;

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
            'active' => 'success',
            'maintenance' => 'warning',
            'damaged' => 'danger',
            'retired' => 'secondary',
            default => 'info',
        };
    }
}
