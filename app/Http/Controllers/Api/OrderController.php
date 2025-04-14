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
use App\Mail\OrderDeliveredMail;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function store(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.id' => 'required|exists:products,id',
        'items.*.quantity' => 'required|integer|min:1',
        'phone' => 'required|string|max:20', // Ajouté
        'address' => 'required|json'
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
           'phone' => $request->phone, 
           'address' => $request->address,
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

public function index()
{
    return Order::with(['user', 'items.product'])
        ->select(['id', 'user_id', 'total', 'status','address','phone', 'delivery_status', 'created_at'])
        ->whereHas('user')
        ->paginate(12);
}
public function updateDeliveryStatus(Order $order, Request $request)
    {
        $request->validate([
            'delivery_status' => 'required|in:pending,delivered,canceled'
        ]);

        if($order->status !== 'paid') {
            return response()->json(['error' => 'La commande doit être payée'], 400);
        }

        $previousStatus = $order->delivery_status;
        $order->update(['delivery_status' => $request->delivery_status]);

        // Envoi d'email uniquement si le statut passe à "delivered"
        if ($request->delivery_status === 'delivered' && $previousStatus !== 'delivered') {
            try {
                Mail::to($order->user->email)
                    ->send(new OrderDeliveredMail($order));
            } catch (\Exception $e) {
                Log::error('Erreur envoi email livraison: '.$e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Statut de livraison mis à jour'.($request->delivery_status === 'delivered' ? ' et email envoyé' : ''),
            'order' => new OrderResource($order->load('user', 'items.product'))
        ]);
    }
}
