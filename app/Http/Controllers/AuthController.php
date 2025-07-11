<?php
namespace App\Http\Controllers;

use App\Models\Comptes;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class AuthController extends Controller
{
   public function register(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|confirmed',
    ],
    [
        'email.unique' => "adresse mail existante",
    ]);

    $user = Comptes::create([
        'username' => $request->username,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => 'client', // Ajout du rôle client par défaut
    ]);

    Auth::login($user);

    // Retourner le token pour une connexion immédiate
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Utilisateur inscrit avec succès',
        'access_token' => $token,
        'token_type' => 'Bearer'
    ]);
}
 
public function login(Request $request)
{
    $credentials = $request->only('email', 'password');
    $user = Comptes::where('email', $credentials['email'])->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not found',
        ], 404);
    }

    if (Auth::attempt($credentials)) {
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'name' => $user->username,
                'email' => $user->email,
                'role' => $user->role // Ajout crucial du rôle
            ],
            'message' => 'Login successful',
        ]);
    }

    return response()->json([
        'success' => false,
        'message' => 'Invalid email or password',
    ], 401);
}


    public function registerAdmin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string',
            'role' => 'nullable'
        ],
        [
            'email.unique' => "adresse mail existante",
   
        ]);
        Comptes::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? '0',
        ]);
           // Retourner un token API si nécessaire 
           return response()->json(['message' => 'Utilisateur inscrit avec succès']);
        }

        

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function getAuthenticatedCompte(Request $request)
    {
        return response()->json([
            'compte' => $request->user(),
            'role' => $request->user()->role
        ]);
    }

}