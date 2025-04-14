<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function createSubscription(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate([
            'payment_method' => 'required|string',
        ]);

        try {
            // Création du client Stripe si inexistant
            if (!$user->stripe_id) {
                $customer = Customer::create([
                    'email' => $user->email,
                    'payment_method' => $validated['payment_method'],
                    'invoice_settings' => [
                        'default_payment_method' => $validated['payment_method']]
                ]);
                $user->stripe_id = $customer->id;
                $user->save();
            }

            // Création de l'abonnement
            $subscription = Subscription::create([
                'customer' => $user->stripe_id,
                'items' => [[
                    'price' => env('STRIPE_PRICE_ID'),
                ]],
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => ['save_default_payment_method' => 'on_subscription'],
                'expand' => ['latest_invoice.payment_intent'],
            ]);

            return response()->json([
                'subscription_id' => $subscription->id,
                'client_secret' => $subscription->latest_invoice->payment_intent->client_secret
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur de souscription : ' . $e->getMessage());
            return response()->json(['error' => 'Échec de la création de l\'abonnement'], 500);
        }
    }

    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Signature invalide'], 403);
        }

        switch ($event->type) {
            case 'invoice.payment_succeeded':
                $subscriptionId = $event->data->object->subscription;
                $subscription = Subscription::retrieve($subscriptionId);
                
                $user = User::where('stripe_id', $subscription->customer)->first();
                if ($user) {
                    $user->update([
                        'is_active' => true,
                        'subscription_end_at' => Carbon::createFromTimestamp($subscription->current_period_end)
                    ]);
                }
                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $user = User::where('stripe_id', $subscription->customer)->first();
                
                if ($user) {
                    $user->update([
                        'is_active' => false,
                        'subscription_end_at' => null
                    ]);
                }
                break;
        }

        return response()->json(['status' => 'success']);
    }
}