<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantConfig extends Model
{
    protected $table = 'restaurant_config';
    
    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'value',
    ];
}
