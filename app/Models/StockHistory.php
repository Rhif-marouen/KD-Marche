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
        'old_stock',
        'new_stock',
        'quantity' // 👈 Ajouter cette ligne
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
        $this->attributes['quantity'] = (int) $value;
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

