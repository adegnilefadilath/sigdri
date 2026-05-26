<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Service centralisé de journalisation des actions SIGDRI
 *
 * Remplace les méthodes privées journaliser() dupliquées dans chaque contrôleur
 * par un service injectable unique. Chaque action significative de l'application
 * (connexion, création, modification, validation, export…) doit être tracée ici.
 *
 * Utilisation dans un contrôleur :
 *
 *   public function __construct(private JournalService $journal) {}
 *
 *   $this->journal->log('creation', 'Création de l\'unité « XYZ »', null, [
 *       'table' => 'unites_industrielles',
 *       'id'    => $id,
 *       'apres' => $donnees,
 *   ]);
 */
class JournalService
{
    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string   $action        Verbe technique de l'action (connexion, creation, modification…)
     * @param string   $description   Message lisible en français décrivant l'action
     * @param int|null $utilisateurId ID de l'auteur ; null = utilise Auth::id(), 0 = action système
     * @param array    $donnees       Données contextuelles optionnelles :
     *                                  'table' => nom de la table concernée
     *                                  'id'    => identifiant de l'enregistrement
     *                                  'avant' => snapshot avant modification (array)
     *                                  'apres' => snapshot après modification (array)
     */
    public function log(
        string $action,
        string $description   = '',
        ?int   $utilisateurId = null,
        array  $donnees       = []
    ): void {
        // Si aucun utilisateur passé en paramètre, on prend le connecté
        $auteurId = $utilisateurId ?? Auth::id();

        DB::table('journaux')->insert([
            'utilisateur_id'    => $auteurId,
            'action'            => $action,
            'description'       => $description ?: null,
            'table_concernee'   => $donnees['table'] ?? null,
            'enregistrement_id' => isset($donnees['id']) ? (int) $donnees['id'] : null,
            'anciennes_valeurs' => isset($donnees['avant']) ? json_encode($donnees['avant']) : null,
            'nouvelles_valeurs' => isset($donnees['apres']) ? json_encode($donnees['apres'])  : null,
            'ip_address'        => request()->ip(),
            'user_agent'        => request()->userAgent(),
            'created_at'        => now(),
        ]);
    }

    /**
     * Raccourci pour les événements d'authentification (connexion / déconnexion).
     * Ces événements n'ont pas de table/enregistrement associé.
     */
    public function logAuth(string $action, string $description, int $utilisateurId): void
    {
        $this->log($action, $description, $utilisateurId);
    }
}
