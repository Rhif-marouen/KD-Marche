<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,Billable;

    public function createAsStripeCustomer(array $options = [])
    {
        return \Stripe\Customer::create($options, [
            'api_key' => config('services.stripe.secret')
        ]);
    }

    public function isAdmin(): bool
{
    return $this->is_admin;
}

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_admin',
        'stripe_id',
        'subscription_end_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean' ,
        ];
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    // Vérification d'abonnement actif
    public function hasActiveSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('subscription_end_at', '>', now())
            ->exists();
    }

    public function subscribed($subscription = 'default')
{
    return $this->subscriptions()
                ->where('stripe_status', 'active')
                ->where('name', $subscription)
                ->exists();
}

    // Vérification du rôle admin
    public function isAdministrator()
    {
        return $this->is_admin;
    }
}