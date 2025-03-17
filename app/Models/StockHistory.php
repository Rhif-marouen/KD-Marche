<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockHistory extends Model
{
    protected $table = 'stock_history'; 
   // use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'quantity_change',   // Quantité changée
        'operation_type',    // Type d'opération (ajout/soustraction)
        'note',              // Note additionnelle
        'old_stock',         // Stock avant l'opération
        'new_stock'          // Nouveau stock après l'opération
    ];

    /**
     * Relier l'historique à un produit.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Mutateur pour la quantité changée : pour assurer la validation des valeurs.
     */
    public function setQuantityChangeAttribute($value)
    {
        $this->attributes['quantity_change'] = (int) $value;
    }

    /**
     * Mutateur pour l'ancien stock : mettre à jour avec des valeurs numériques.
     */
    public function setOldStockAttribute($value)
    {
        $this->attributes['old_stock'] = (int) $value;
    }

    /**
     * Mutateur pour le nouveau stock : mettre à jour avec des valeurs numériques.
     */
    public function setNewStockAttribute($value)
    {
        $this->attributes['new_stock'] = (int) $value;
    }
}

