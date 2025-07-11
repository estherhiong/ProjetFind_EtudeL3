<?php

namespace App\Http\Controllers;

use App\Models\Comptes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompteController extends Controller
{
    /**
     * Enregistrer un nouveau compte admin (accessible seulement par les admins)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registerAdmin(Request $request)
    {
        // Validation des données d'entrée
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:comptes',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['client', 'admin'])]
        ]);

        // Création du compte avec le mot de passe hashé
        $compte = Comptes::create([
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'date_connexion' => now()
        ]);

        // Retourne le compte créé sans le mot de passe
        return response()->json([
            'message' => 'Compte créé avec succès',
            'compte' => $compte->makeHidden(['password'])
        ], 201);
    }

    /**
     * Récupérer tous les comptes (pour l'admin dashboard)
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Récupère tous les comptes triés par date de connexion
        $comptes = Comptes::orderBy('date_connexion', 'desc')->get();
        
        // Retourne les comptes sans les mots de passe
        return response()->json($comptes->makeHidden(['password']));
    }

    /**
     * Rechercher un compte par email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        // Validation de l'email
        $request->validate([
            'email' => 'required|email'
        ]);

        // Recherche du compte par email
        $compte = Comptes::where('email', $request->email)->first();

        if (!$compte) {
            return response()->json(['message' => 'Compte non trouvé'], 404);
        }

        // Retourne le compte trouvé sans le mot de passe
        return response()->json($compte->makeHidden(['password']));
    }

    /**
     * Mettre à jour un compte
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Recherche du compte à mettre à jour
        $compte = Comptes::find($id);

        if (!$compte) {
            return response()->json(['message' => 'Compte non trouvé'], 404);
        }

        // Validation des données d'entrée
        $validatedData = $request->validate([
            'username' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('comptes')->ignore($compte->id_compte, 'id_compte')],
            'role' => ['sometimes', Rule::in(['client', 'admin'])]
        ]);

        // Mise à jour du compte
        $compte->update($validatedData);

        // Retourne le compte mis à jour sans le mot de passe
        return response()->json([
            'message' => 'Compte mis à jour avec succès',
            'compte' => $compte->makeHidden(['password'])
        ]);
    }

    /**
     * Supprimer un compte
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Recherche du compte à supprimer
        $compte = Comptes::find($id);

        if (!$compte) {
            return response()->json(['message' => 'Compte non trouvé'], 404);
        }

        // Suppression du compte
        $compte->delete();

        return response()->json(['message' => 'Compte supprimé avec succès']);
    }
}