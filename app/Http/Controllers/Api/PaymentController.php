<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
   
    public function handlePayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|string'
        ]);
    
        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $validated['amount'] * 100,
                'currency' => 'eur',
                'payment_method' => $validated['payment_method_id'],
                'confirmation_method' => 'manual',
                'metadata' => [
                    'user_id' => auth()->id,
                ]
            ]);
    
            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'status' => 'requires_confirmation'
            ]);
    
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Payment Error: '.$e->getMessage());
            return response()->json([
                'error' => 'Échec du traitement du paiement'
            ], 500);
        }
    }
}