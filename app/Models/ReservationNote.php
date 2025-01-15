<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'reservation_id',
        'note'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
