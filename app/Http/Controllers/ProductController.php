<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin'])->only(['store', 'update', 'destroy', 'adjustStock']);
    }

    // Pour la liste des produits publics
    public function index(Request $request) 
    {
        // Vérification de l'abonnement de l'utilisateur
        // Si l'utilisateur n'est pas authentifié ou n'a pas d'abonnement actif, on renvoie une erreur 403
    
         // Vérifier l'authentification ET l'abonnement
    if (!$request->user() || !$request->user()->is_active) {
        return response()->json([
            'message' => 'Abonnement actif requis',
            'user_active' => $request->user()?->is_active // Debug
        ], 403);
    }

        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->paginate(12);

            return ProductResource::collection(Product::paginate(12));
    }

    // Version admin protégée
    public function adminIndex()
    {
        $this->authorize('view-admin', Product::class);
        return ProductResource::collection(Product::with('stockHistory')->paginate(12));
    }

    /**
     * Créer un nouveau produit
     */
    public function store(ProductRequest $request): JsonResponse
    {
        Log::info('File received: ' . $request->hasFile('image'));

        if ($request->hasFile('image')) {
            Log::info('File name: ' . $request->file('image')->getClientOriginalName());
        }
        
        // Récupération des données validées
        $validated = $request->validated();
        
        // Traitement du fichier image
        if ($request->hasFile('image')) {
            // Stockage du fichier dans le dossier "products" du disque "public"
            $path = $request->file('image')->store('products', 'public');
            /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
            $storage = Storage::disk('public');
            // Génération de l'URL accessible publiquement
            $validated['image_url'] = $storage->url($path);
        }
        
        $product = Product::create($validated);
        
        return (new ProductResource($product->load('category')))
            ->response()
            ->setStatusCode(201);
            try {
                $path = $request->file('image')->store('products', 'public');
            } catch (\Exception $e) {
                Log::error('Échec du stockage : '.$e->getMessage());
                abort(500, 'Erreur de stockage du fichier');
            }
    }

    /**
     * Afficher un produit spécifique
     */
    public function show(Product $product): JsonResponse
    {
        return (new ProductResource($product->load(['category', 'stockHistory'])))->response();
    }

    /**
     * Mettre à jour un produit
     */
    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();
        
        // Si une nouvelle image est fournie, supprimer l'ancienne et stocker la nouvelle
        if ($request->hasFile('image')) {
            // Suppression de l'ancienne image si elle existe
            if ($product->image_url) {
                // On extrait le chemin relatif depuis l'URL (en supposant que l'URL est de type APP_URL/storage/...)
                $oldPath = str_replace(env('APP_URL').'/storage/', '', $product->image_url);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                } else {
                    Log::warning("L'ancien fichier n'existe pas: $oldPath");
                }
            }
            
            // Stockage de la nouvelle image
            $path = $request->file('image')->store('products', 'public');
            /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
            $publicDisk = Storage::disk('public');
            $validated['image_url'] = $publicDisk->url($path);
        }
        
        $product->update($validated);
        
        return (new ProductResource($product->fresh()->load('category')))->response();
    }

    /**
     * Supprimer un produit
     */
    public function destroy(Product $product)
    {
        try {
            $this->authorize('delete', $product);
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Produit supprimé.',
                'deleted_id' => $product->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Échec de la suppression.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ajuster le stock manuellement
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
     * Recherche de produits
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
