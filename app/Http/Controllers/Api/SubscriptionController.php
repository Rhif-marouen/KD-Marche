<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; 

use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;

class SubscriptionController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        // Assurez-vous que la configuration Stripe est définie dans config/services.php ou config/stripe.php
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Crée une souscription en utilisant un PaymentMethod et les informations de l'utilisateur.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSubscription(Request $request)
    
{   

    /**
 * @var \App\Models\User|null $user
 */
    $user = Auth::user();
    Stripe::setApiKey(env('STRIPE_SECRET'));
    
    $validated = $request->validate([
        'payment_method' => 'required|string',
    ]);

    try {
        
        Log::info('Création d\'abonnement pour utilisateur:', [
            'user_id' => $user->id,
            'email' => $user->email,
            'payment_method' => $validated['payment_method'],
        ]);

        if (!$user->stripe_id) {
            $customer = Customer::create([
                'email' => $user->email,
                'payment_method' => $validated['payment_method'],
                'invoice_settings' => [
                    'default_payment_method' => $validated['payment_method']
                ]
            ]);

            $user->stripe_id = $customer->id;
            $user->subscription_end_at = Carbon::now()->addYear(); 
            echo $user->subscription_end_at;
            $user->save();
            
        }

        // Créer l'abonnement
        $subscription = Subscription::create([
            'customer' => $user->stripe_id,
            'items' => [[
                'price' => env('STRIPE_PRICE_ID'), // Prix configuré dans le dashboard Stripe
            ]],
            'payment_behavior' => 'default_incomplete',
            'expand' => ['latest_invoice.payment_intent']
        ]);

        return response()->json([
            'subscription_id' => $subscription->id,
            'client_secret' => $subscription->latest_invoice->payment_intent->client_secret
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur lors de la création de l’abonnement Stripe:', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
        ]);
    }
}

    /**
     * Gère les webhooks envoyés par Stripe.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleWebhook(Request $request)
    {
        // Vous pouvez vérifier la signature du webhook ici si besoin

        // Par exemple, enregistrez l'événement dans les logs pour déboguer
        Log::info('Stripe Webhook Event:', $request->all());

        // Répondre à Stripe pour confirmer la réception du webhook
        return response()->json(['message' => 'Webhook reçu'], 200);
    }
}