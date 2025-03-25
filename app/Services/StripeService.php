<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeService
{
    protected $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient([
            'api_key' => config('services.stripe.secret'),
            'stripe_version' => '2023-08-16'
        ]);
    }

    public function createCustomer($userData)
    {
        return $this->stripe->customers->create([
            'email' => $userData['email'],
            'name' => $userData['name']
        ]);
    }
}