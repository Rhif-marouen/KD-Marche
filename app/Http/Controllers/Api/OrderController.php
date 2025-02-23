<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            $order = $request->user()->orders()->create([
                'total' => $request->total,
                'status' => 'pending'
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
                
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price
                ]);
            }

            return $order;
        });

        return new OrderResource($order->load('items'), 201);
    }
}