<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    /**
     * Get the status color for AdminLTE badges.
     */
    public function getStatusColorAttribute()
    {
        return $this->status ? 'success' : 'danger';
    }
}
