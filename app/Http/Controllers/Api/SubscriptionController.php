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
        if (!$user->stripe_id) {
            // Create new customer with the provided payment method
            $customer = Customer::create([
                'email' => $user->email,
                'payment_method' => $validated['payment_method'],
                'invoice_settings' => [
                    'default_payment_method' => $validated['payment_method']
                ]
            ]);
            $user->stripe_id = $customer->id;
            $user->save();
        } else {
            // Attach the new payment method to the existing customer
            $paymentMethod = \Stripe\PaymentMethod::retrieve($validated['payment_method']);
            $paymentMethod->attach(['customer' => $user->stripe_id]);

            // Update the customer's default payment method
            Customer::update($user->stripe_id, [
                'invoice_settings' => [
                    'default_payment_method' => $validated['payment_method']
                ]
            ]);
        }

        // Create the subscription with the customer's default payment method
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
        Log::error('Subscription Error: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function handleWebhook(Request $request)
{
    Log::info('[Webhook] Données reçues : ' . json_encode($request->all()));
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
        Log::error('[Webhook] Signature verification failed: ' . $e->getMessage());
        return response()->json(['error' => 'Signature invalide'], 403);
    }

    switch ($event->type) {
        case 'invoice.payment_succeeded':
            Log::info('[Webhook] Invoice Payment Succeeded Event Received');
            $invoice = $event->data->object;
            
            try {
                $subscription = Subscription::retrieve($invoice->subscription);
                Log::info('[Webhook] Subscription Retrieved: ' . $subscription->id);

                $user = User::where('stripe_id', $invoice->customer)->first();
                if (!$user) {
                    Log::error('[Webhook] User not found with stripe_id: ' . $invoice->customer);
                    break;
                }

                // Correction ici : Utilisation de la date de fin réelle de l'abonnement
                $endDate = Carbon::createFromTimestamp($subscription->current_period_end);
                
                $user->update([
                    'is_active' => 1,
                    'subscription_end_at' => $endDate
                ]);
                Log::info('[Webhook] User updated: ' . $user->email);
                $user->refresh(); 
            } catch (\Exception $e) {
                Log::error('[Webhook] Error: ' . $e->getMessage());
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
            $user->refresh(); // Après $user->update(...)
Log::info('User after update', $user->toArray());
            break;
    }

    return response()->json(['status' => 'success']);
}
}