<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration — Ajout du type 'declaration_soumise' dans la colonne type des notifications
 *
 * Ce type est utilisé pour notifier les agents admin lorsqu'un industriel
 * soumet une nouvelle déclaration en attente de validation.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE notifications
            MODIFY COLUMN type
            ENUM(
                'declaration_validee',
                'declaration_rejetee',
                'declaration_soumise',
                'agrement_expirant',
                'agrement_expire',
                'alerte_systeme'
            ) NOT NULL DEFAULT 'alerte_systeme'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE notifications
            MODIFY COLUMN type
            ENUM(
                'declaration_validee',
                'declaration_rejetee',
                'agrement_expirant',
                'agrement_expire',
                'alerte_systeme'
            ) NOT NULL DEFAULT 'alerte_systeme'
        ");
    }
};
