<?php

namespace App\Models;

use Hamcrest\Description;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'category_id',
        'description',
        'image_url',
        'quality'
    ];
    // Dans App\Models\Product
public function scopeFilter($query, array $filters)
{
    $query->when($filters['search'] ?? null, function ($query, $search) {
        $query->where('name', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%");
    })->when($filters['category'] ?? null, function ($query, $category) {
        $query->whereHas('category', function ($q) use ($category) {
            $q->where('name', $category);
        });
    });
}

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot('quantity');
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class, 'cart_items')
            ->withPivot('quantity', 'price');
    }

    public function stockHistory()
    {
        return $this->hasMany(StockHistory::class);
    }
   
}