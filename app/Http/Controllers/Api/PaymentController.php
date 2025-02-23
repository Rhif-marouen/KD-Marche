<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function handlePayment(Request $request)
    {
        $stripe = new StripeClient(config('stripe.secret'));
        
        $paymentIntent = $stripe->paymentIntents->create([
            'amount' => $request->amount * 100,
            'currency' => 'eur',
            'payment_method' => $request->payment_method_id,
            'confirmation_method' => 'manual',
        ]);

        return response()->json([
            'client_secret' => $paymentIntent->client_secret
        ]);
    }

    public function confirmPayPalPayment(Request $request)
    {
        // Logique PayPal
    }
}