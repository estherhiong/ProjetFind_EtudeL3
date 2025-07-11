<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Comptes extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $table = 'comptes';
    protected $primaryKey = 'id_compte';

    protected $fillable = [
        'email',
        'password',
        'username',
        'role', 
        'date_connexion'
    ];

    protected $hidden = [
        'password',
    ];

    // Ajoutez ceci pour le casting
    protected $casts = [
        'date_connexion' => 'datetime'
    ];
    
    // Méthode pour déterminer si c'est un admin
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function reservations()
    {
        return $this->belongsToMany(Reservation::class, 'effectuer', 'id_compte', 'id_reserv')
        ->withPivot('date_reserv2')
        ->withTimestamps();
    }
}