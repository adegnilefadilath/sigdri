<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration : mise à jour de l'enum 'role' dans la table utilisateurs
 *
 * Renomme les rôles génériques en rôles métier SIGDRI :
 *   inspecteur → agent_mic   (agent du MIC chargé des vérifications)
 *   declarant  → decideur    (décideur politique, accès lecture seule)
 *
 * La syntaxe MODIFY COLUMN est obligatoire pour MySQL/MariaDB car ALTER TABLE
 * ADD/DROP ne supporte pas les valeurs d'un ENUM ; toutes les valeurs doivent
 * être re-déclarées dans leur intégralité.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Migration des données existantes ──────────────────────────────
        // Renomme les anciennes valeurs avant de modifier la définition de l'enum
        // (MySQL ne peut pas renommer une valeur d'enum directement ; on passe
        //  par un UPDATE avant de redéfinir le type)
        DB::statement("UPDATE utilisateurs SET role = 'agent_mic' WHERE role = 'inspecteur'");
        DB::statement("UPDATE utilisateurs SET role = 'decideur'  WHERE role = 'declarant'");

        // ── 2. Redéfinition de l'enum avec les nouveaux libellés ─────────────
        DB::statement("
            ALTER TABLE utilisateurs
            MODIFY COLUMN role
            ENUM('super_admin','admin','agent_mic','decideur','industriel')
            NOT NULL DEFAULT 'agent_mic'
            COMMENT 'Niveau d''accès dans l''application'
        ");
    }

    public function down(): void
    {
        // Rétablit les anciennes valeurs de l'enum
        DB::statement("UPDATE utilisateurs SET role = 'inspecteur' WHERE role = 'agent_mic'");
        DB::statement("UPDATE utilisateurs SET role = 'declarant'  WHERE role = 'decideur'");

        DB::statement("
            ALTER TABLE utilisateurs
            MODIFY COLUMN role
            ENUM('super_admin','admin','inspecteur','declarant','industriel')
            NOT NULL DEFAULT 'declarant'
            COMMENT 'Niveau d''accès dans l''application'
        ");
    }
};
