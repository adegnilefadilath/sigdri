<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal — SIGDRI
 *
 * Orchestre l'exécution des seeders dans l'ordre correct.
 * Lancer avec : php artisan db:seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Compte super-administrateur par défaut (admin@sigdri.bj)
        $this->call(AdminSeeder::class);

        // Données de test pour l'espace industriel
        // Crée une unité, un compte industriel et un agrément de démonstration
        $this->call(IndustrielSeeder::class);
    }
}
