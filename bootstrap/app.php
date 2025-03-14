<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
           // Enregistrement du middleware de route
           $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class, 
        ]);

           // Configuration CORS
    $middleware->appendToGroup('web', [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);

    $middleware->appendToGroup('api', [
        \Illuminate\Http\Middleware\HandleCors::class,
    ]);

  
        
       
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
