<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            // Limite les soumissions de déclarations : 10 par heure par industriel
            'rate.declarations' => \App\Http\Middleware\RateLimitDeclarations::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ── Erreur 404 — Ressource introuvable ───────────────────────────────
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['erreur' => 'Ressource introuvable.'], 404);
            }
            return response()->view('errors.404', ['message' => $e->getMessage()], 404);
        });

        // ── Autres erreurs HTTP (403, 500, etc.) ─────────────────────────────
        $exceptions->render(function (HttpException $e, Request $request) {
            $code = $e->getStatusCode();
            if ($request->expectsJson()) {
                return response()->json([
                    'erreur' => 'Erreur ' . $code . '.',
                    'detail' => $e->getMessage() ?: null,
                ], $code);
            }
            // Vue personnalisée si elle existe, sinon vue Laravel par défaut
            $vue = 'errors.' . $code;
            if (view()->exists($vue)) {
                return response()->view($vue, ['message' => $e->getMessage()], $code);
            }
        });
    })->create();
