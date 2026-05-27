<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Service — Notifications in-app
 *
 * Centralise la création des notifications en base de données.
 * Chaque méthode publique cible le ou les bons destinataires selon le contexte :
 *   - déclaration validée / rejetée    → l'industriel déclarant uniquement
 *   - déclaration soumise              → tous les agents admin (super_admin, admin, agent_mic, decideur)
 *   - agrément expirant / expiré       → l'industriel de l'unité + tous les agents admin
 */
class NotificationService
{
    // ── Déclaration validée → notifie l'industriel ────────────────────────────

    public function notifierDeclarationValidee(int $declarantId, string $numeroDeclaration): void
    {
        $this->inserer(
            $declarantId,
            'Déclaration validée',
            "Votre déclaration {$numeroDeclaration} a été validée par l'administration.",
            'declaration_validee',
        );
    }

    // ── Déclaration rejetée → notifie l'industriel ────────────────────────────

    public function notifierDeclarationRejetee(int $declarantId, string $numeroDeclaration, string $motif): void
    {
        $this->inserer(
            $declarantId,
            'Déclaration rejetée',
            "Votre déclaration {$numeroDeclaration} a été rejetée. Motif : {$motif}",
            'declaration_rejetee',
        );
    }

    // ── Nouvelle déclaration soumise → notifie tous les agents admin ──────────

    public function notifierNouvelleDeclaration(string $numeroDeclaration, string $denominationUnite): void
    {
        $titre   = 'Nouvelle déclaration à traiter';
        $message = "L'unité « {$denominationUnite} » a soumis la déclaration {$numeroDeclaration}. Elle est en attente de validation.";

        $this->insererPourAdmins($titre, $message, 'declaration_soumise');
    }

    // ── Agrément expirant dans 30 jours → notifie l'industriel + les admins ───

    public function notifierAgrementExpirant(int $utilisateurId, string $numeroAgrement, string $dateExpiration): void
    {
        $titre   = 'Agrément bientôt expiré';
        $message = "L'agrément {$numeroAgrement} expire le {$dateExpiration}. Des démarches de renouvellement sont nécessaires.";

        // Notification pour l'industriel concerné
        $this->inserer($utilisateurId, $titre, $message, 'agrement_expirant');

        // Notification pour tous les agents admin (visibilité proactive)
        $this->insererPourAdmins(
            "Agrément expirant : {$numeroAgrement}",
            "L'agrément {$numeroAgrement} expire le {$dateExpiration}.",
            'agrement_expirant',
        );
    }

    // ── Agrément expiré → notifie l'industriel + les admins ──────────────────

    public function notifierAgrementExpire(int $utilisateurId, string $numeroAgrement): void
    {
        $titre   = 'Agrément expiré';
        $message = "L'agrément {$numeroAgrement} a expiré. La soumission de déclarations est suspendue jusqu'à obtention d'un nouvel agrément.";

        // Notification pour l'industriel concerné
        $this->inserer($utilisateurId, $titre, $message, 'agrement_expire');

        // Notification pour tous les agents admin
        $this->insererPourAdmins(
            "Agrément expiré : {$numeroAgrement}",
            "L'agrément {$numeroAgrement} a expiré. L'unité ne peut plus soumettre de déclarations.",
            'agrement_expire',
        );
    }

    // ── Helpers privés ────────────────────────────────────────────────────────

    /**
     * Insère une notification pour un utilisateur donné.
     */
    private function inserer(int $utilisateurId, string $titre, string $message, string $type): void
    {
        DB::table('notifications')->insert([
            'utilisateur_id' => $utilisateurId,
            'titre'          => $titre,
            'message'        => $message,
            'type'           => $type,
            'lu'             => false,
            'created_at'     => now(),
        ]);
    }

    /**
     * Insère une notification pour chaque agent ayant accès au back-office admin.
     * Exclut les comptes inactifs et le rôle 'industriel'.
     */
    private function insererPourAdmins(string $titre, string $message, string $type): void
    {
        $admins = DB::table('utilisateurs')
            ->whereIn('role', ['super_admin', 'admin', 'agent_mic', 'decideur'])
            ->where('actif', true)
            ->pluck('id');

        $now  = now();
        $rows = $admins->map(fn ($id) => [
            'utilisateur_id' => $id,
            'titre'          => $titre,
            'message'        => $message,
            'type'           => $type,
            'lu'             => false,
            'created_at'     => $now,
        ])->all();

        if ($rows) {
            DB::table('notifications')->insert($rows);
        }
    }
}
