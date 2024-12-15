<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'date',
        'time',
        'duration',
        'seats',
        'table_id',
        'user_id',
    ];

    protected $casts = [
        'date' => 'date',
        'time' => 'datetime',
        'duration' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
