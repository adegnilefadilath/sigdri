<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder : création du super-administrateur par défaut
 *
 * Insère le premier compte de connexion au back-office SIGDRI.
 * Identifiants : admin@sigdri.bj / Admin1234
 * ⚠ Changer le mot de passe immédiatement après le premier déploiement.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Évite les doublons si le seeder est rejoué (idempotent)
        if (Utilisateur::where('email', 'admin@sigdri.bj')->exists()) {
            $this->command->info('Super-administrateur déjà présent — insertion ignorée.');
            return;
        }

        Utilisateur::create([
            'nom'                => 'Admin',
            'prenom'             => 'SIGDRI',
            'email'              => 'admin@sigdri.bj',
            // Le cast "hashed" du modèle applique bcrypt automatiquement
            'mot_de_passe'       => 'Admin1234',
            'role'               => 'super_admin',
            'actif'              => true,
            'derniere_connexion'  => null,
        ]);

        $this->command->info('Super-administrateur créé : admin@sigdri.bj');
    }
}
