<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\PublicProductResource;
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin'])->only(['store', 'update', 'destroy', 'adjustStock']);
        //$this->middleware(['auth:sanctum'])->only(['addToCart']);
    }

    // Ajouter dans app/Http/Controllers/ProductController.php
public function index()
{
    $products = Product::with('category')
        ->where('stock', '>', 0)
        ->paginate(12);

    return PublicProductResource::collection($products);
}

// Version admin protégée
public function adminIndex()
{
    $this->authorize('view-admin', Product::class);
    return ProductResource::collection(Product::with('stockHistory')->paginate(12));
}

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Créer un nouveau produit",
     *     security={ {"sanctum": {} }},
     *     @OA\Response(response=201, description="Produit créé"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function store(ProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());

        return (new ProductResource($product->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Afficher un produit spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détails du produit"),
     *     @OA\Response(response=404, description="Produit non trouvé")
     * )
     */
    public function show(Product $product): JsonResponse
    {
        return (new ProductResource($product->load(['category', 'stockHistory'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Mettre à jour un produit",
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Produit mis à jour"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return (new ProductResource($product->fresh()->load('category')))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Supprimer un produit",
     *     security={ {"sanctum": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Produit supprimé"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            Gate::authorize('delete', $product);
            
            $product->delete();

            return response()->json([
                'message' => __('Product successfully deleted'),
                'deleted_id' => $product->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => __('Deletion failed'),
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/products/{id}/adjust-stock",
     *     summary="Ajuster le stock manuellement",
     *     security={ {"sanctum": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity","type"},
     *             @OA\Property(property="quantity", type="integer"),
     *             @OA\Property(property="type", type="string", enum={"in","out"})
     *         )
     *     )
     * )
     */
    public function adjustStock(Request $request, Product $product): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out'
        ]);

        $quantity = $request->type === 'in' 
            ? $product->stock + $request->quantity
            : $product->stock - $request->quantity;

        $product->update(['stock' => max($quantity, 0)]);

        return (new ProductResource($product))->response();
    }

    /**
     * @OA\Get(
     *     path="/api/products/search",
     *     summary="Recherche de produits",
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Résultats de recherche")
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->input('query');

        $products = Product::where('name', 'like', "%{$searchTerm}%")
            ->orWhere('description', 'like', "%{$searchTerm}%")
            ->with('category')
            ->paginate(10);

        return ProductResource::collection($products)->response();
    }
}