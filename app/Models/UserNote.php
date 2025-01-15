<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
