<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductRequest;

class AdminProductController extends Controller
{
    /**
     * Affiche tous les produits (admin)
     */
    public function index()
    {
        $products = Product::with(['category', ])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * Crée un nouveau produit
     */
    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            
            /** @var FilesystemAdapter $storage */
            $storage = Storage::disk('public');
            $validated['image_url'] = $storage->url($path); 
        }
        // 3. Créer le produit avec l'URL de l'image
        $product = Product::create($validated);

        return new ProductResource($product->load('category', 'stockHistory'));
    }
    
    /**
     * Affiche un produit spécifique
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load(['category', 'stockHistory']));
    }

    /**
     * Met à jour un produit
     */
    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        
        return new ProductResource($product->fresh()->load('category'));
    }

    /**
     * Supprime un produit
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Produit supprimé avec succès',
            'deleted_id' => $product->id
        ]);
    }
}