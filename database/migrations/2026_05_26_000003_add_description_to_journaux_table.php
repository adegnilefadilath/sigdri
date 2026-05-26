<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : ajout de la colonne 'description' à la table journaux
 *
 * Permet au JournalService d'enregistrer un message lisible décrivant
 * l'action en français (ex : « Connexion de SIGDRI Admin »).
 * Colonne nullable pour ne pas invalider les enregistrements existants.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journaux', function (Blueprint $table) {
            // Placée après 'action' pour suivre le flux logique de lecture
            $table->string('description', 255)
                  ->nullable()
                  ->after('action')
                  ->comment('Description lisible de l\'action (ex : « Connexion de SIGDRI Admin »)');
        });
    }

    public function down(): void
    {
        Schema::table('journaux', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
