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
    }
}
