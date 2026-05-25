<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Ajout des champs de rejet et de chiffre d'affaires à la table declarations
 *
 * motif_rejet      : raison saisie par l'inspecteur lors d'un rejet (distinct du champ
 *                    observations qui sert aux remarques générales).
 * chiffre_affaires_total : CA total de la période saisi par l'industriel (en FCFA).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            $table->text('motif_rejet')->nullable()->after('observations')
                  ->comment('Motif de rejet saisi par l\'inspecteur lors du statut rejetee');

            $table->decimal('chiffre_affaires_total', 18, 2)->default(0)->after('motif_rejet')
                  ->comment('Chiffre d\'affaires total déclaré pour la période en FCFA');
        });
    }

    public function down(): void
    {
        Schema::table('declarations', function (Blueprint $table) {
            $table->dropColumn(['motif_rejet', 'chiffre_affaires_total']);
        });
    }
};
