<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : extension de la table utilisateurs pour l'espace industriel
 *
 * - Ajoute la valeur 'industriel' à l'enum 'role' (MySQL ENUM ne supporte pas
 *   la syntaxe Blueprint standard, on passe par une instruction SQL directe)
 * - Ajoute la colonne unite_industrielle_id (FK nullable) pour lier un compte
 *   industriel à son unité de production
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Ajout de la valeur 'industriel' à l'enum role ────────────────
        // La clause MODIFY COLUMN sur un ENUM nécessite de re-déclarer toutes
        // les valeurs existantes pour ne pas perdre les données.
        DB::statement("
            ALTER TABLE utilisateurs
            MODIFY COLUMN role
            ENUM('super_admin','admin','inspecteur','declarant','industriel')
            NOT NULL DEFAULT 'declarant'
            COMMENT 'Niveau d''accès dans l''application'
        ");

        // ── 2. Colonne de liaison vers l'unité industrielle ──────────────────
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->foreignId('unite_industrielle_id')
                  ->nullable()
                  ->after('role')
                  ->constrained('unites_industrielles')
                  ->nullOnDelete()
                  ->comment('Unité industrielle gérée par ce compte (null pour les rôles admin/inspecteur)');
        });
    }

    public function down(): void
    {
        // Supprime d'abord la FK avant de toucher à l'enum
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropForeign(['unite_industrielle_id']);
            $table->dropColumn('unite_industrielle_id');
        });

        // Retire 'industriel' de l'enum en re-déclarant les valeurs d'origine
        DB::statement("
            ALTER TABLE utilisateurs
            MODIFY COLUMN role
            ENUM('super_admin','admin','inspecteur','declarant')
            NOT NULL DEFAULT 'declarant'
            COMMENT 'Niveau d''accès dans l''application'
        ");
    }
};
