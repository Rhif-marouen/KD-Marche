<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
{
    $user = $request->user();
    
    if (!$user || !$user->is_active || $user->subscription_end_at < now()) {
        return response()->json([
            'error' => 'Abonnement actif requis'
        ], 403);
    }

    return $next($request);
}
}