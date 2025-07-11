<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $primaryKey = 'id_cli';

    protected $fillable = [
        'nom_cli',
        'pre_nom',
    ];

    public function compte()
    {
        return $this->hasOne(Compte::class, 'id_compte');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_cli');
    }
}

