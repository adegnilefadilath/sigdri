<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',       // Routes API préfixées /api
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Force toutes les réponses du groupe API en JSON
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        // Alias de middleware utilisables dans les routes et les groupes
        $middleware->alias([
            // Protège les routes du back-office : authentification + compte actif
            'admin.auth'      => \App\Http\Middleware\AdminAuth::class,
            // Protège l'espace industriel : authentification + rôle 'industriel' + compte actif
            'industriel.auth' => \App\Http\Middleware\IndustrielAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
