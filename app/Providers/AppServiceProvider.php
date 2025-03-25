<?php

namespace App\Providers;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate; 
use App\Models\User;
use App\Models\Product;
use App\Policies\ProductPolicy;
use App\Policies\UserPolicy;
use App\Observers\ProductObserver;
use App\Models\StockHistory; 
use Illuminate\Http\Resources\Json\JsonResource;
use Stripe\Stripe;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Product::observe(ProductObserver::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        // Dans AppServiceProvider.php
        JsonResource::withoutWrapping();
        Stripe::setApiKey(config('stripe.secret'));
       
    }    

    // ProductObserver.php
public function updated(Product $product)
{
    if ($product->isDirty('stock')) {
        $diff = $product->stock - $product->getOriginal('stock');
        $type = $diff > 0 ? 'in' : 'out';
        
        StockHistory::create([
            'product_id' => $product->id,
            'quantity' => abs($diff),
            'type' => $type
        ]);
    }
    
}



}