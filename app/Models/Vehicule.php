<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicule extends Model
{
    protected $primaryKey = 'num_vil';

    protected $fillable = [
        'marque',
        'place', 
        'prix',
        'description',
        'disponibilite',
        'kilometrage',
        'num_chassis'
    ];

    // Relation avec les réservations
    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'concerner', 'num_vil', 'id_reserv')
        ->withPivot('date_reservation');
    }

    // Relation avec les images
    public function images()
    {
        return $this->hasMany(Image::class, 'num_vil')
        ->orderBy('order'); // Tri par ordre d'affichage
    }
}