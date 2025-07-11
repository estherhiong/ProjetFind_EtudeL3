<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class VehiculeController extends Controller
{
    /**
     * Récupère tous les véhicules avec leurs images triées par ordre
     * 
     * @return \Illuminate\Http\JsonResponse
     */
  public function index()
{
    try {
        // Chargement avec les images triées et URL complète
        $vehicules = Vehicule::with(['images' => function($query) {
            $query->orderBy('order');
        }])->get();

        // Transformation des URLs des images
        $vehicules->each(function ($vehicule) {
            $vehicule->images->each(function ($image) {
                $image->url = asset('storage/vehicules/' . $image->path);
            });
        });

        return response()->json([
            'success' => true,
            'vehicules' => $vehicules
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de chargement',
            'error' => $e->getMessage()
        ], 500);
    }
}

 /**
 * Récupère la liste des véhicules disponibles pour le client
 * avec gestion robuste des images manquantes
 * 
 * @return \Illuminate\Http\JsonResponse
 */
public function showListeVehicule()
{
    try {
        $vehicles = Vehicule::where('disponibilite', true)
            ->with(['images' => function($query) {
                $query->orderBy('order')->take(1); // Prend seulement la première image
            }])
            ->get()
            ->map(function ($vehicule) {
                // Détermine l'URL de l'image avec fallback
                $imageUrl = $vehicule->images->isNotEmpty() 
                    ? asset('storage/vehicules/' . $vehicule->images->first()->path)
                    : asset('storage/vehicules/default-car.png');

                return [
                    'num_vil' => $vehicule->num_vil,
                    'marque' => $vehicule->marque,
                    'description' => $vehicule->description,
                    'place' => $vehicule->place,
                    'kilometrage' => $vehicule->kilometrage,
                    'prix' => (float)$vehicule->prix,
                    'disponibilite' => (bool)$vehicule->disponibilite,
                    'images' => [
                        [
                            'path' => $vehicule->images->isNotEmpty() 
                                ? $vehicule->images->first()->path 
                                : 'default-car.png',
                            'url' => $imageUrl
                        ]
                    ]
                ];
            });

        return response()->json($vehicles);

    } catch (\Exception $e) {
        Log::error('Erreur dans showListeVehicule: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Erreur de chargement des véhicules',
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Récupère un véhicule spécifique avec ses images
     * 
     * @param int $id L'ID du véhicule
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Trouve le véhicule avec ses images triées par ordre
        $vehicule = Vehicule::with(['images' => function($query) {
          $query->orderBy('order')->append('full_path');
        }])->find($id);
        
        // Si le véhicule n'existe pas, retourne une erreur 404
        if (!$vehicule) {
            return response()->json([
                'message' => 'Véhicule non trouvé'
            ], 404);
        }
        
        // Retourne le véhicule au format JSON
        return response()->json([
            'vehicule' => $vehicule
        ], 200);
    }

    /**
     * Crée un nouveau véhicule avec ses images
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'marque' => 'required|string|max:55',          // Obligatoire, max 55 caractères
            'place' => 'required|integer|min:1',           // Nombre de places (min 1)
            'prix' => 'required|numeric|min:0',            // Prix positif
            'description' => 'required|string|max:255',    // Description max 255 caractères
            'kilometrage' => 'required|integer|min:0',    // Kilométrage positif
            'num_chassis' => 'required|string|unique:vehicules', // Doit être unique
            'disponibilite' => 'required|boolean',         // Booléen
            'images' => 'sometimes|array',                 // Optionnel: tableau d'images
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048' // Formats acceptés (2MB max)
        ]);

        // Si validation échoue, retourne les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422); // Code 422: Unprocessable Entity
        }

        // Crée le véhicule avec toutes les données sauf les images
        $vehicule = Vehicule::create($request->except('images'));

        // Si des images sont jointes, les traite
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                // Stocke l'image dans le dossier 'public/vehicules'
                $path = $image->store('vehicules', 'public');
                
                // Crée l'entrée en base de données
                Image::create([
                    'num_vil' => $vehicule->num_vil, // ID du véhicule
                    'path' => $path,                 // Chemin de stockage
                    'order' => Image::where('num_vil', $vehicule->num_vil)->count() + 1 // Ordre incrémenté
                ]);
            }
        }

        // Retourne le véhicule créé avec ses images (code HTTP 201: Created)
        return response()->json([
            'vehicule' => $vehicule->load('images')
        ], 201);
    }

    /**
     * Met à jour un véhicule existant
     * 
     * @param Request $request
     * @param int $id L'ID du véhicule
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Trouve le véhicule
        $vehicule = Vehicule::find($id);
        
        // Si non trouvé, retourne erreur 404
        if (!$vehicule) {
            return response()->json([
                'message' => 'Véhicule non trouvé'
            ], 404);
        }

        // Validation des données (champs optionnels)
        $validator = Validator::make($request->all(), [
            'marque' => 'sometimes|string|max:55',
            'place' => 'sometimes|integer|min:1',
            'prix' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string|max:255',
            'kilometrage' => 'sometimes|integer|min:0',
            'num_chassis' => 'sometimes|string|unique:vehicules,num_chassis,'.$vehicule->num_vil.',num_vil', // Unique sauf pour l'actuel
            'disponibilite' => 'sometimes|boolean',
            'images' => 'sometimes|array|min:1|max:5', // Entre 1 et 5 images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Formats acceptés
        ]);

        // Si validation échoue
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Met à jour uniquement les champs fournis
        $vehicule->update($request->only([
            'marque', 'place', 'prix', 'description', 
            'kilometrage', 'num_chassis', 'disponibilite'
        ]));

        // Si de nouvelles images sont fournies
        if ($request->hasFile('images')) {
            // Supprime les anciennes images (fichiers + entrées BDD)
            $this->deleteImages($vehicule);
            
            // Stocke les nouvelles images
            $this->storeImages($request, $vehicule->num_vil);
        }

        // Retourne le véhicule mis à jour avec ses images triées
        return response()->json([
            'message' => 'Véhicule mis à jour avec succès',
            'vehicule' => $vehicule->load(['images' => function($query) {
                $query->orderBy('order');
            }])
        ], 200);
    }

    /**
     * Supprime un véhicule et ses images
     * 
     * @param int $id L'ID du véhicule
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Trouve le véhicule
        $vehicule = Vehicule::find($id);
        
        // Si non trouvé
        if (!$vehicule) {
            return response()->json([
                'message' => 'Véhicule non trouvé'
            ], 404);
        }

        // Supprime les images associées (méthode privée)
        $this->deleteImages($vehicule);

        // Supprime le véhicule
        $vehicule->delete();

        return response()->json([
            'message' => 'Véhicule supprimé avec succès'
        ], 200);
    }

    /**
     * Méthode privée: Stocke les images pour un véhicule
     * 
     * @param Request $request
     * @param int $vehiculeId
     */
    private function storeImages(Request $request, $vehiculeId)
    {
        $order = 0; // Commence l'ordre à 0

        foreach ($request->file('images') as $image) {
            // Stocke physiquement l'image
            $path = $image->store('vehicules', 'public');
            
            // Crée l'entrée en base
            Image::create([
                'num_vil' => $vehiculeId,
                'path' => $path,
                'order' => $order++ // Incrémente l'ordre
            ]);
        }
    }

    /**
 * Récupère la liste de tous les numéros de chassis
 * 
 * @return \Illuminate\Http\JsonResponse
 */
public function chassisList()
{
    try {
        $chassisList = Vehicule::pluck('num_chassis')->all();
        
        return response()->json([
            'success' => true,
            'data' => $chassisList
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de chargement',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Recherche améliorée de véhicules
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Validation des paramètres d'entrée
        $validator = Validator::make($request->all(), [
            'chassis' => 'sometimes|string',  // Paramètre optionnel de type string
            'marque' => 'sometimes|string'    // Paramètre optionnel de type string
        ]);

        // Si la validation échoue, retourne les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Construction de la requête avec les images associées triées
            $query = Vehicule::with(['images' => function($query) {
                $query->orderBy('order'); // Tri des images par leur ordre
            }]);

            // Filtre par numéro de chassis si présent dans la requête
            if ($request->filled('chassis')) {
                $query->where('num_chassis', 'like', '%' . $request->chassis . '%');
            }

            // Filtre par marque si présent dans la requête
            if ($request->filled('marque')) {
                $query->where('marque', 'like', '%' . $request->marque . '%');
            }

            // Exécution de la requête et récupération des résultats
            $vehicles = $query->get();

            // Si aucun résultat n'est trouvé
            if ($vehicles->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun véhicule trouvé'
                ], 404);
            }

            // Retourne les véhicules trouvés
            return response()->json([
                'success' => true,
                'count' => $vehicles->count(),
                'vehicles' => $vehicles
            ]);

        } catch (\Exception $e) {
            // En cas d'erreur, log et retourne un message d'erreur
            Log::error('Erreur recherche véhicule: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la recherche',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Méthode privée: Supprime les images d'un véhicule
     * 
     * @param Vehicule $vehicule
     */
    private function deleteImages(Vehicule $vehicule)
    {
        foreach ($vehicule->images as $image) {
            // Supprime le fichier physique
            Storage::disk('public')->delete($image->path);
            
            // Supprime l'entrée en base
            $image->delete();
        }
    }
    
}