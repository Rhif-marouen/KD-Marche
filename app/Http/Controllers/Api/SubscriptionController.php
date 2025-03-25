<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
class SubscriptionController extends Controller
{
    protected $stripe;

    public function __construct()
    {
        // Assurez-vous que la configuration Stripe est définie dans config/services.php ou config/stripe.php
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
        // Validez les données reçues
        $data = $request->validate([
            'paymentMethodId' => 'required|string',
            'userId'          => 'required|integer',
            'amount'          => 'required|integer',
            'currency'        => 'required|string',
        ]);

        try {
            // Exemple : création d'un PaymentIntent pour simuler la souscription
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'payment_method' => $data['paymentMethodId'],
                'confirmation_method' => 'manual',
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'message'       => 'Souscription créée avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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
