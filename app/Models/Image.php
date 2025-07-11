<?php

// Déclaration de l'espace de nom (namespace) pour organiser le code selon la structure Laravel
namespace App\Models;

// Importation de la classe Model de base de Laravel
use Illuminate\Database\Eloquent\Model;

// Définition de la classe Image qui étend le Model Eloquent
class Image extends Model
{
    // Liste des champs qui peuvent être assignés massivement (via create() ou fill())
    protected $fillable = [
        'path',      // Chemin de stockage de l'image (ex: "nom-image.jpg")
        'order',     // Ordre d'affichage de l'image parmi les autres
        'num_vil'    // Clé étrangère liant l'image à un véhicule
    ];

    // Champs calculés à ajouter automatiquement aux résultats JSON
    protected $appends = ['full_path']; // Ajoute l'URL complète via getFullPathAttribute()

    // Conversion automatique des types de champs
    protected $casts = [
        'order' => 'integer',   // Garantit que 'order' est un entier
        'num_vil' => 'integer'  // Garantit que 'num_vil' est un entier (pour la clé étrangère)
    ];

        // Ajoutez cette méthode au modèle Image
    public function getUrlAttribute()
    {
        if (!$this->path) return null;
        return asset('storage/vehicules/' . $this->path);
    }
    
    // Accesseur pour obtenir l'URL complète de l'image (attribut "full_path")
    public function getFullPathAttribute()
    {
        // Si le chemin est vide ou null, retourne null
        if (!$this->path) {
            return null;
        }

        // Si le chemin est déjà une URL valide (ex: http://...), le retourne tel quel
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        // Génère l'URL publique en combinant :
        // - asset() : génère l'URL de base du projet (ex: http://nom-domaine.com)
        // - 'vehicules/' : dossier de stockage dans "public/"
        // - ltrim($this->path, '/') : supprime les "/" initiaux pour éviter les doublons (ex: "/image.jpg" → "image.jpg")
        return asset('storage/vehicules/' . ltrim($this->path, '/'));
    }

    // Définit la relation "belongsTo" avec le modèle Vehicule
    public function vehicule()
    {
        // Une image appartient à un véhicule via la clé étrangère "num_vil"
        return $this->belongsTo(Vehicule::class, 'num_vil');
    }
}