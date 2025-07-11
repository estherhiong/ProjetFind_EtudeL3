<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{ 
    protected $primaryKey = 'id_reserv';

    protected $fillable = [
        'date_debut',
        'date_fin',
        'moyen_paie',
        'montant',
        'num_contrat',
        'statut',
        'moyen_reserv',
    ];


    public function comptes()
    {
        return $this->belongsToMany(Comptes::class, 'effectuer', 'id_reserv', 'id_compte')
        ->withPivot('date_reserv2')
        ->withTimestamps(); // Si vous avez created_at/updated_at
    }
    /*public function documents()
    {
        return $this->belongsToMany(Document::class, 'soumettre', 'id_reserv', 'id_document');
    }*/

        protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
    ];

    // Relation avec véhicule

    public function vehicules()
    {
        return $this->belongsToMany(Vehicule::class, 'concerner', 'id_reserv', 'num_vil')->withPivot('date_reservation');
    }

    public function commentaires()
    {
        return $this->belongsToMany(Commentaires::class, 'concerner2', 'id_reserv', 'id_com');
    }
}

