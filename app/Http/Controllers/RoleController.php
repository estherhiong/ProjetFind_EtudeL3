<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Affiche la liste des rôles
     */
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    /**
     * Crée un nouveau rôle
     */
    public function store(Request $request)
    {
        $request->validate([
            'intitule' => 'required|string|max:20|unique:roles',
        ]);

        $role = Role::create($request->all());
        return response()->json($role, 201);
    }

    /**
     * Affiche un rôle spécifique
     */
    public function show($id)
    {
        $role = Role::findOrFail($id);
        return response()->json($role);
    }

    /**
     * Met à jour un rôle
     */
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $request->validate([
            'intitule' => 'required|string|max:20|unique:roles,intitule,'.$role->id_role.',id_role',
        ]);

        $role->update($request->all());
        return response()->json($role);
    }

    /**
     * Supprime un rôle
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(null, 204);
    }

    /**
     * Méthode utilitaire pour initialiser les rôles (à appeler une fois)
     */
    public function initializeRoles()
    {
        $roles = [
            ['intitule' => Role::ADMIN],
            ['intitule' => Role::CLIENT],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }

        return response()->json(['message' => 'Roles initialized successfully']);
    }
}