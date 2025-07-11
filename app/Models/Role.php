<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_role';
    protected $fillable = ['intitule'];
    
    // Constantes pour les rôles (facultatif mais pratique)
    public const ADMIN = 'admin';
    public const CLIENT = 'client';
    
}