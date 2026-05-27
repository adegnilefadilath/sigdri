<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder : données de test pour l'espace industriel
 *
 * Crée :
 *  1. Une unité industrielle de test (SARL BENIN AGRO)
 *  2. Le compte utilisateur industriel lié à cette unité
 *     email : adegnilefadilath@gmail.com / mot de passe : Industriel1234
 *
 * ⚠ Ces données sont destinées au développement uniquement.
 */
class IndustrielSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Unité industrielle de test ─────────────────────────────────────
        $uniteExiste = DB::table('unites_industrielles')
            ->where('numero_immatriculation', 'RB-COT-2024-00001')
            ->exists();

        if (! $uniteExiste) {
            $uniteId = DB::table('unites_industrielles')->insertGetId([
                'denomination'          => 'SARL BENIN AGRO INDUSTRIE',
                'numero_immatriculation' => 'RB-COT-2024-00001',
                'secteur_activite'      => 'Agroalimentaire',
                'regime'                => 'Droit commun',
                'adresse'               => 'Zone industrielle de Cotonou, lot 42',
                'commune'               => 'Cotonou',
                'departement'           => 'Littoral',
                'telephone'             => '+229 21 30 00 00',
                'email'                 => 'contact@benin-agro.bj',
                'responsable_nom'       => 'ADEGNIKA Raoul',
                'responsable_fonction'  => 'Directeur Général',
                'actif'                 => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
            $this->command->info('Unité industrielle de test créée : SARL BENIN AGRO INDUSTRIE');
        } else {
            $uniteId = DB::table('unites_industrielles')
                ->where('numero_immatriculation', 'RB-COT-2024-00001')
                ->value('id');
            $this->command->info('Unité industrielle déjà présente — récupération de l\'ID.');
        }

        // ── 2. Compte utilisateur industriel ─────────────────────────────────
        $utilisateurExiste = DB::table('utilisateurs')
            ->where('email', 'adegnilefadilath@gmail.com')
            ->exists();

        if (! $utilisateurExiste) {
            DB::table('utilisateurs')->insert([
                'nom'                    => 'Test',
                'prenom'                 => 'Industriel',
                'email'                  => 'adegnilefadilath@gmail.com',
                // Le cast 'hashed' du modèle Utilisateur n'est pas actif ici
                // (on utilise DB::table), donc on hash manuellement.
                'mot_de_passe'           => bcrypt('Industriel1234'),
                'role'                   => 'industriel',
                'unite_industrielle_id'  => $uniteId,
                'actif'                  => true,
                'derniere_connexion'     => null,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
            $this->command->info('Industriel de test créé : adegnilefadilath@gmail.com / Industriel1234');
        } else {
            $this->command->info('Industriel de test déjà présent — insertion ignorée.');
        }

        // ── 3. Agrément de test pour l'unité ─────────────────────────────────
        $agrementExiste = DB::table('agrements')
            ->where('numero_agrement', 'AGR-2024-001')
            ->exists();

        if (! $agrementExiste) {
            DB::table('agrements')->insert([
                'unite_industrielle_id' => $uniteId,
                'numero_agrement'       => 'AGR-2024-001',
                'type_agrement'         => 'Exploitation industrielle',
                'date_delivrance'       => '2024-01-15',
                'date_expiration'       => '2027-01-14',
                'statut'                => 'valide',
                'observations'          => 'Agrément délivré suite à inspection du site en janvier 2024.',
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
            $this->command->info('Agrément de test créé : AGR-2024-LIT-001');
        }
    }
}
