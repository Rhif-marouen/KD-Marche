<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Resources\PublicProductResource;

class PublicProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')
            ->where('stock', '>', 0)
            ->paginate(12);

        return PublicProductResource::collection($products);
    }

    public function show(Product $product)
    {
        return new PublicProductResource($product);
    }
}