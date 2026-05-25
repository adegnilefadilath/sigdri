<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : suppression du concept de période déclarative
 *
 * Avant : declarations.periode_id → FK vers periodes.id
 * Après : declarations.mois (1-12) + declarations.annee (ex: 2026)
 *         Contrainte d'unicité sur (unite_industrielle_id, mois, annee)
 */
return new class extends Migration
{
    public function up(): void
    {
        // Étape 1 — retrait de la clé étrangère et de la colonne periode_id
        Schema::table('declarations', function (Blueprint $table) {
            $table->dropForeign(['periode_id']);
            $table->dropIndex(['periode_id']); // index simple créé dans la migration initiale
            $table->dropColumn('periode_id');
        });

        // Étape 2 — ajout de mois, annee et nouvelle contrainte d'unicité
        Schema::table('declarations', function (Blueprint $table) {
            // Mois de la déclaration : 1 = Janvier … 12 = Décembre
            $table->unsignedTinyInteger('mois')
                  ->default(now()->month)
                  ->after('declarant_id')
                  ->comment('Mois de la déclaration (1 = Jan, 12 = Déc)');

            // Année de la déclaration (ex : 2026)
            $table->unsignedSmallInteger('annee')
                  ->default(now()->year)
                  ->after('mois')
                  ->comment('Année de la déclaration');

            // Une seule déclaration par unité industrielle, par mois, par année
            $table->unique(
                ['unite_industrielle_id', 'mois', 'annee'],
                'decl_unite_mois_annee_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            $table->dropUnique('decl_unite_mois_annee_unique');
            $table->dropColumn(['mois', 'annee']);
        });

        Schema::table('declarations', function (Blueprint $table) {
            $table->foreignId('periode_id')
                  ->after('unite_industrielle_id')
                  ->constrained('periodes');
            $table->index('periode_id');
        });
    }
};
