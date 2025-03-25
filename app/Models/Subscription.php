<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Laravel\Cashier\Subscription as CashierSubscription;

class Subscription extends CashierSubscription
{
    protected $fillable = [
        'stripe_id', 'stripe_status', 'stripe_price', 'quantity',
        'ends_at', 'trial_ends_at', 'user_id'
    ];
}