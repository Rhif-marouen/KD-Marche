<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\StockHistory;

class ProductObserver
{
    /**
     * Gère les mises à jour du stock et l'historique
     */
    public function updated(Product $product)
    {
        if ($product->isDirty('stock')) {
            $oldStock = $product->getOriginal('stock');
            $newStock = $product->stock;
            
            // Calcul de la différence absolue
            $quantity = abs($newStock - $oldStock);
            
            // Détermination du type d'opération
            $type = ($newStock > $oldStock) ? 'in' : 'out';

            // Création de l'entrée d'historique
            StockHistory::create([
                'product_id' => $product->id,
                'quantity' => $quantity, // Correction du nom de colonne
                'type' => $type,
                'old_stock' => $oldStock, // Optionnel : sauvegarde de l'ancienne valeur
                'new_stock' => $newStock  // Optionnel : sauvegarde de la nouvelle valeur
            ]);
        }
    }
}