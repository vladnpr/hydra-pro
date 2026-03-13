<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ammunition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ammunition';

    protected $fillable = [
        'name',
        'type',
        'status',
    ];

    /**
     * Get the status color for AdminLTE badges.
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            1, true => 'success',
            0, false => 'danger',
            default => 'info',
        };
    }
}
