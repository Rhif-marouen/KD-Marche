<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\StripeService;
class AuthController extends Controller
{
   /*
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
            'is_active' => false, 
            'is_admin'  => false,
        ]);

        $checkoutUrl = app(StripeService::class)->createSubscription($user);
    
        return response()->json([
            'user' => $user,
            'payment_required' => true,
            'checkout_url' => $checkoutUrl
        ], 201);

      
    } */

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
    
        try {
            $user = User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'is_active'  => true, 
                'is_admin'   => false,
            ]);
            
            // Création du client Stripe
            $stripeService = new StripeService();
            $stripeCustomer = $stripeService->createCustomer($validated);
            $user->stripe_id = $stripeCustomer->id;
            $user->save();
    
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création du compte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
                'is_admin' => (bool)$user->is_admin,
                'is_active'=> (bool)$user->is_active // Conversion explicite
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