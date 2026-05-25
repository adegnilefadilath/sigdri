<?php

use App\Models\Utilisateur;

return [

    /*
    |--------------------------------------------------------------------------
    | Guard d'authentification par défaut
    |--------------------------------------------------------------------------
    | "web"  → sessions HTTP (back-office, formulaires)
    | "api"  → tokens stateless (routes /api/*)
    */
    'defaults' => [
        'guard'     => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'utilisateurs'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards d'authentification
    |--------------------------------------------------------------------------
    | web : session Laravel standard pour le back-office SIGDRI.
    | api : tokens Sanctum pour les clients SPA / mobile (stateless).
    */
    'guards' => [
        'web' => [
            'driver'   => 'session',
            'provider' => 'utilisateurs',
        ],

        'api' => [
            'driver'   => 'sanctum',
            'provider' => 'utilisateurs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fournisseurs d'utilisateurs
    |--------------------------------------------------------------------------
    | Le provider "utilisateurs" pointe sur le modèle Eloquent Utilisateur
    | et la table "utilisateurs" (colonne mot_de_passe pour l'authentification).
    */
    'providers' => [
        'utilisateurs' => [
            'driver' => 'eloquent',
            'model'  => env('AUTH_MODEL', Utilisateur::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Réinitialisation des mots de passe
    |--------------------------------------------------------------------------
    | La table "jetons_reinitialisation_mdp" remplace la table générique
    | Laravel "password_reset_tokens" (cf. migration 000010).
    */
    'passwords' => [
        'utilisateurs' => [
            'provider' => 'utilisateurs',
            'table'    => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'jetons_reinitialisation_mdp'),
            'expire'   => 60,      // minutes
            'throttle' => 60,      // secondes entre deux demandes
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délai de confirmation de mot de passe
    |--------------------------------------------------------------------------
    | Durée (secondes) avant qu'une confirmation de mot de passe soit redemandée.
    | Par défaut : 3 heures.
    */
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
