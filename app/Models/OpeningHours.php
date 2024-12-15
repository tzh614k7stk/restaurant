<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningHours extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'day',
        'open',
        'close',
        'closed',
    ];

    protected $casts = [
        'closed' => 'boolean',
    ];
}
