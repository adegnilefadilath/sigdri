<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table periodes
 *
 * Définit les fenêtres de déclaration (annuelles, trimestrielles ou mensuelles).
 * Une déclaration est toujours rattachée à une période ouverte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodes', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('annee')->comment('Année de la période (ex : 2024)');
            // Trimestre : 1 à 4, null si période annuelle ou mensuelle
            $table->tinyInteger('trimestre')->unsigned()->nullable()->comment('Numéro du trimestre (1-4), null si inapplicable');
            // Mois : 1 à 12, null si période annuelle ou trimestrielle
            $table->tinyInteger('mois')->unsigned()->nullable()->comment('Numéro du mois (1-12), null si inapplicable');
            $table->enum('type', ['annuelle', 'trimestrielle', 'mensuelle'])
                  ->comment('Fréquence de la période de déclaration');
            $table->date('date_debut')->comment('Premier jour de la période');
            $table->date('date_fin')->comment('Dernier jour de la période');
            // Seule une période ouverte accepte de nouvelles déclarations
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte')
                  ->comment('Statut de collecte : ouverte = déclarations acceptées');
            $table->timestamps();

            $table->index(['annee', 'type']);
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodes');
    }
};
