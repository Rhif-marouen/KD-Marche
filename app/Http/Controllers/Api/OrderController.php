<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\StockHistory;
class OrderController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1'
    ]);

    try {
        Stripe::setApiKey(config('services.stripe.secret'));

        $total = collect($request->items)->sum(function ($item) {
            $product = Product::findOrFail($item['id']);
            return $product->price * $item['quantity'];
        });

        $paymentIntent = PaymentIntent::create([
            'amount' => $total * 100,
            'currency' => 'eur',
            'payment_method' => $request->paymentMethodId,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never' // Désactive les redirections
            ],
        ]);

        DB::beginTransaction();

        $order = Order::create([
           'user_id' => Auth::id(),
            'total' => $total,
            'status' => 'paid',
            'stripe_payment_id' => $paymentIntent->id
        ]);

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['id']);
            
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);

            DB::transaction(function() use ($product, $item) {
                $oldStock = $product->stock;
                $product->decrement('stock', $item['quantity']);
                
                StockHistory::create([
                    'product_id' => $product->id,
                    'old_stock' => $oldStock,
                    'new_stock' => $product->stock,
                    'quantity' => $item['quantity'], 
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            });
        }

        DB::commit();

        return response()->json($order);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Order error: '.$e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}
}