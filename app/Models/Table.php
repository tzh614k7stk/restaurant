<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'seats',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
