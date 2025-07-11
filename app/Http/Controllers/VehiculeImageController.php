<?php

namespace App\Http\Controllers;

use App\Models\Vehicule;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VehiculeImageController extends Controller
{
    /**
     * Ajoute des images à un véhicule existant
     * 
     * @param Request $request Les données de la requête (contient les images à uploader)
     * @param int $vehiculeId L'ID du véhicule auquel lier les images
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $vehiculeId)
    {
        // Validation des données entrantes
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5', // Entre 1 et 5 images obligatoires
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048' // Chaque image doit être au format jpeg, png, jpg ou gif et faire moins de 2MB
        ]);

        // Si la validation échoue, retourne les erreurs
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422); // Code HTTP 422 : Unprocessable Entity
        }

        // Vérifie que le véhicule existe
        $vehicule = Vehicule::find($vehiculeId);
        if (!$vehicule) {
            return response()->json([
                'success' => false,
                'message' => 'Véhicule non trouvé'
            ], 404); // Code HTTP 404 : Not Found
        }

        try {
            // Récupère le dernier ordre utilisé pour les images de ce véhicule (ou 0 si aucune image)
            $lastOrder = Image::where('num_vil', $vehiculeId)->max('order') ?? 0;
            
            $uploadedImages = []; // Stocke les images uploadées pour rollback en cas d'erreur
            
            // Traite chaque image uploadée
            foreach ($request->file('images') as $image) {
                // Stocke physiquement l'image dans le dossier 'public/vehicules'
                $path = $image->store('vehicules', 'public');
                
                // Crée l'entrée en base de données
                $imageModel = Image::create([
                    'num_vil' => $vehiculeId, // Lie l'image au véhicule
                    'path' => $path,          // Chemin de stockage
                    'order' => ++$lastOrder  // Incrémente l'ordre
                ]);
                
                $uploadedImages[] = $imageModel; // Ajoute à la liste des uploads réussis
            }

            // Retourne une réponse de succès avec les images uploadées et le véhicule mis à jour
            return response()->json([
                'success' => true,
                'message' => 'Images ajoutées avec succès',
                'images' => $uploadedImages,
                'vehicule' => $vehicule->load(['images' => function($query) {
                    $query->orderBy('order'); // Charge les images triées par ordre
                }])
            ], 201); // Code HTTP 201 : Created

        } catch (\Exception $e) {
            // En cas d'erreur, supprime les images déjà uploadées (rollback)
            if (!empty($uploadedImages)) {
                foreach ($uploadedImages as $image) {
                    Storage::disk('public')->delete($image->path); // Supprime le fichier
                    $image->delete(); // Supprime l'entrée en base
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement des images',
                'error' => $e->getMessage() // Détail de l'erreur
            ], 500); // Code HTTP 500 : Internal Server Error
        }
    } 

    /**
     * Supprime toutes les images d'un véhicule
     * 
     * @param int $vehiculeId L'ID du véhicule
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAll($vehiculeId)
    {
        // Récupère le véhicule
        $vehicule = Vehicule::find($vehiculeId);
        
        // Si le véhicule n'existe pas
        if (!$vehicule) {
            return response()->json([
                'message' => 'Véhicule non trouvé'
            ], 404);
        }

        // Parcourt toutes les images du véhicule
        foreach ($vehicule->images as $image) {
            Storage::disk('public')->delete($image->path); // Supprime le fichier
            $image->delete(); // Supprime l'entrée en base
        }

        return response()->json([
            'message' => 'Toutes les images ont été supprimées'
        ], 200); // Code HTTP 200 : OK
    }

    /**
     * Supprime une image spécifique
     * 
     * @param int $imageId L'ID de l'image à supprimer
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($imageId)
    {
        // Récupère l'image
        $image = Image::find($imageId);
        
        // Si l'image n'existe pas
        if (!$image) {
            return response()->json([
                'success' => false,
                'message' => 'Image non trouvée'
            ], 404);
        }

        try {
            // Suppression du fichier physique
            Storage::disk('public')->delete($image->path);
            
            // Suppression de l'entrée en base
            $image->delete();

            // Réorganisation des images restantes
            $this->reorderImages($image->num_vil);

            return response()->json([
                'success' => true,
                'message' => 'Image supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour l'ordre des images
     * 
     * @param Request $request Contient le nouvel ordre des images
     * @param int $vehiculeId L'ID du véhicule concerné
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, $vehiculeId)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'images' => 'required|array', // Doit contenir un tableau d'images
            'images.*.id' => 'required|exists:images,id', // Chaque image doit exister
            'images.*.order' => 'required|integer|min:0' // L'ordre doit être un entier positif
        ]);

        // Si validation échoue
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Met à jour l'ordre de chaque image
            foreach ($request->images as $imageData) {
                Image::where('id', $imageData['id'])
                    ->where('num_vil', $vehiculeId)
                    ->update(['order' => $imageData['order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Ordre des images mis à jour avec succès'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Réorganise l'ordre des images après une suppression (méthode privée)
     * 
     * @param int $vehiculeId L'ID du véhicule concerné
     */
    private function reorderImages($vehiculeId)
    {
        // Récupère toutes les images du véhicule triées par ordre actuel
        $images = Image::where('num_vil', $vehiculeId)
            ->orderBy('order')
            ->get();

        $order = 1; // Réinitialise l'ordre à 1
        foreach ($images as $image) {
            $image->update(['order' => $order++]); // Met à jour l'ordre de chaque image
        }
    }
}