<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // Applique le middleware admin à toutes les méthodes
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    // Tableau de bord admin
    public function dashboard()
    {
        return response()->json([
            'message' => 'Bienvenue dans l\'interface admin'
        ]);
    }

    // Liste des utilisateurs (exemple de fonctionnalité admin)
    public function getUsers()
    {
        $users = User::all();
        return response()->json($users);
    }

    // Création de produits (exemple)
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
           'category' => 'required|string|in:A,B,C,D,E'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $product = Product::create($request->all());
        
        return response()->json($product, 201);
    }
}