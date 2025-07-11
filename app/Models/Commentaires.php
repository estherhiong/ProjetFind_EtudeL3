<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commentaires extends Model
{
    protected $primaryKey = 'id_com';

    protected $fillable = [
        'message',
        'date_com',
    ];

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'concerner2', 'id_com', 'id_reserv');
    }
}

