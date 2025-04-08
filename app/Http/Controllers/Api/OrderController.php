<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
{
    $order = DB::transaction(function () use ($request) {
        // Calcul du total côté serveur
        $total = 0;
        $items = [];

        foreach ($request->items as $item) {
            $product = Product::where('id', $item['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($product->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'stock' => ["Stock insuffisant pour {$product->name}"]
                ]);
            }

            $total += $product->price * $item['quantity'];
            $product->decrement('stock', $item['quantity']);
            
            $items[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ];
        }

        $order = $request->user()->orders()->create([
            'total' => $total,
            'status' => 'pending'
        ]);

        $order->items()->createMany($items);

        return $order;
    });

    return response()->json(
        new OrderResource($order->load('items')),
        201
    );
}
}