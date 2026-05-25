<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Commande Artisan — Vérification quotidienne des agréments expirés
 *
 * Passe automatiquement au statut « expire » tout agrément dont la
 * date_expiration est antérieure à aujourd'hui et dont le statut est
 * encore « valide » ou « suspendu ».
 *
 * Planification recommandée (routes/console.php) :
 *   Schedule::command('agrement:verifier-expires')->dailyAt('00:05');
 *
 * Exécution manuelle :
 *   php artisan agrement:verifier-expires
 */
class VerifierAgrementsExpires extends Command
{
    protected $signature   = 'agrement:verifier-expires';
    protected $description = 'Passe au statut "expiré" les agréments dont la date d\'expiration est dépassée';

    public function handle(): int
    {
        $aujourd_hui = now()->toDateString();

        // Sélectionner les agréments candidats à l'expiration automatique :
        // – date_expiration renseignée et dépassée
        // – statut pas encore "expire" ni "revoque"
        $candidats = DB::table('agrements')
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<', $aujourd_hui)
            ->whereIn('statut', ['valide', 'suspendu'])
            ->get();

        if ($candidats->isEmpty()) {
            $this->info('Aucun agrément à expirer.');
            return Command::SUCCESS;
        }

        $compteur = 0;

        foreach ($candidats as $agrement) {
            // Mise à jour du statut
            DB::table('agrements')
                ->where('id', $agrement->id)
                ->update(['statut' => 'expire', 'updated_at' => now()]);

            // Trace dans le journal d'audit (utilisateur_id = null → action système)
            DB::table('journaux')->insert([
                'utilisateur_id'    => null,
                'action'            => 'expiration_automatique',
                'table_concernee'   => 'agrements',
                'enregistrement_id' => $agrement->id,
                'anciennes_valeurs' => json_encode(['statut' => $agrement->statut]),
                'nouvelles_valeurs' => json_encode(['statut' => 'expire']),
                'ip_address'        => null,
                'user_agent'        => 'SIGDRI/Scheduler',
                'created_at'        => now(),
            ]);

            $this->line("  → Agrément #{$agrement->id} ({$agrement->numero_agrement}) expiré le {$agrement->date_expiration}.");
            $compteur++;
        }

        $this->info("{$compteur} agrément(s) passé(s) au statut « expiré ».");

        return Command::SUCCESS;
    }
}
