<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Commande Artisan — Vérification quotidienne des agréments expirés ou expirant
 *
 * Deux actions :
 *  1. Passe au statut « expire » tout agrément dont la date_expiration est
 *     antérieure à aujourd'hui — et notifie l'industriel correspondant.
 *  2. Envoie une alerte préventive pour les agréments expirant dans exactement
 *     30 jours (fenêtre : ±1 jour pour absorber les décalages d'exécution).
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
    protected $description = 'Expire les agréments échus et envoie des alertes pour ceux expirant dans 30 jours';

    public function __construct(private NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->traiterExpires();
        $this->traiterExpirantsBientot();

        return Command::SUCCESS;
    }

    // ── 1. Agréments dont la date est dépassée → statut "expire" ─────────────

    private function traiterExpires(): void
    {
        $aujourd_hui = now()->toDateString();

        $candidats = DB::table('agrements')
            ->whereNotNull('date_expiration')
            ->where('date_expiration', '<', $aujourd_hui)
            ->whereIn('statut', ['valide', 'suspendu'])
            ->get();

        if ($candidats->isEmpty()) {
            $this->info('Aucun agrément à expirer.');
            return;
        }

        $compteur = 0;

        foreach ($candidats as $agrement) {
            // Passage au statut expiré
            DB::table('agrements')
                ->where('id', $agrement->id)
                ->update(['statut' => 'expire', 'updated_at' => now()]);

            // Trace d'audit (utilisateur_id null = action système)
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

            // Notification in-app + email pour tous les industriels de cette unité
            $industriels = DB::table('utilisateurs')
                ->where('unite_industrielle_id', $agrement->unite_industrielle_id)
                ->where('role', 'industriel')
                ->where('actif', true)
                ->get();

            foreach ($industriels as $u) {
                $this->notificationService->notifierAgrementExpire(
                    $u->id,
                    $agrement->numero_agrement,
                );
            }

            $this->line("  → Agrément #{$agrement->id} ({$agrement->numero_agrement}) expiré le {$agrement->date_expiration}.");
            $compteur++;
        }

        $this->info("{$compteur} agrément(s) passé(s) au statut « expiré ».");
    }

    // ── 2. Agréments expirant dans ~30 jours → alerte préventive ─────────────

    private function traiterExpirantsBientot(): void
    {
        // Fenêtre : aujourd'hui + 29 à +30 jours pour éviter les doublons de relance
        $debut = now()->addDays(29)->toDateString();
        $fin   = now()->addDays(30)->toDateString();

        $expirantsBientot = DB::table('agrements')
            ->whereNotNull('date_expiration')
            ->whereBetween('date_expiration', [$debut, $fin])
            ->where('statut', 'valide')
            ->get();

        if ($expirantsBientot->isEmpty()) {
            $this->info('Aucun agrément expirant dans 30 jours.');
            return;
        }

        $compteur = 0;

        foreach ($expirantsBientot as $agrement) {
            $industriels = DB::table('utilisateurs')
                ->where('unite_industrielle_id', $agrement->unite_industrielle_id)
                ->where('role', 'industriel')
                ->where('actif', true)
                ->get();

            $dateFormatee = \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y');

            foreach ($industriels as $u) {
                $this->notificationService->notifierAgrementExpirant(
                    $u->id,
                    $agrement->numero_agrement,
                    $dateFormatee,
                );
            }

            $this->line("  → Alerte 30j envoyée pour agrément #{$agrement->id} ({$agrement->numero_agrement}), expire le {$agrement->date_expiration}.");
            $compteur++;
        }

        $this->info("{$compteur} alerte(s) « expiration dans 30 jours » envoyée(s).");
    }
}
