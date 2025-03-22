<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    /**
     * Affiche toutes les catégories
     */
    public function index(): JsonResponse
    {
        $categories = Category::all();
        
        return response()->json([
            'data' => $categories
        ]);
    }
}