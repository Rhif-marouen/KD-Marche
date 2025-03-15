<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Enregistrement d'un nouvel utilisateur
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|string|email|max:255|unique:users',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'is_active' => true, // Consider changing to false if email verification is required
            'is_admin'  => false,
        ]);

        // Création d'un token d'accès via Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token
        ], 201);
    }

    // Connexion de l'utilisateur
  /*  public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        // Vérification de l'utilisateur, du mot de passe et du statut actif
        if (!$user || !Hash::check($request->password, $user->password) || !$user->is_active ) {
            return response()->json(['message' => 'Identifiants invalides ou compte désactivé'], 401);
        } 

        // Création du token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'user'  => $user,
            'token' => $token,
            
        ], 200);
    } */

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8'
        ]);
    
        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }
    
        $user = $request->user();
        
        return response()->json([
            'token' => $user->createToken('auth-token')->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => (bool)$user->is_admin // Conversion explicite
            ]
        ]);
    }
    // Retourne le profil de l'utilisateur authentifié
    public function userProfile(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }

    // Déconnexion : révocation du token courant
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }
}