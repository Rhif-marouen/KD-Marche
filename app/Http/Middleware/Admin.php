<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // <– Ajoutez cette ligne

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Utilisez Auth::check() et Auth::user()
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Accès non autorisé');
        }

        return $next($request);
    }
}  