<?php

namespace App\Http\Controllers;

use App\Models\StockHistory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StockHistoryController extends Controller
{
    /**
     * Afficher l'historique des stocks d'un produit spécifique.
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);

        $history = StockHistory::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'message' => 'Historique du stock récupéré avec succès.',
            'product' => $product->name,
            'history' => $history
        ], Response::HTTP_OK);
    }

    /**
     * Ajouter une nouvelle entrée à l'historique du stock.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity_change' => 'required|integer',
            'operation_type' => 'required|string|in:addition,subtraction',
            'note' => 'nullable|string|max:255'
        ]);

        $stockHistory = StockHistory::create([
            'product_id' => $request->product_id,
            'quantity_change' => $request->quantity_change,
            'operation_type' => $request->operation_type,
            'note' => $request->note,
        ]);

        return response()->json([
            'message' => 'Historique de stock enregistré avec succès.',
            'data' => $stockHistory
        ], Response::HTTP_CREATED);
    }

    /**
     * Afficher un enregistrement spécifique de l'historique.
     */
    public function show($id)
    {
        $history = StockHistory::findOrFail($id);

        return response()->json([
            'message' => 'Détail de l\'historique du stock.',
            'data' => $history
        ], Response::HTTP_OK);
    }

    /**
     * Supprimer un enregistrement de l'historique du stock.
     */
    public function destroy($id)
    {
        $history = StockHistory::findOrFail($id);
        $history->delete();

        return response()->json([
            'message' => 'Enregistrement de l\'historique supprimé avec succès.'
        ], Response::HTTP_OK);
    }
}
