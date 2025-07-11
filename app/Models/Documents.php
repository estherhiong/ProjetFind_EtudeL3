<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $primaryKey = 'id_document';

    protected $fillable = [
        'chemin_acces',
        'id_compte',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'id_compte');
    }
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'soumettre', 'id_document', 'id_reserv');
    }
}

