<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Ajout du champ capacité de production installée
 *
 * Stocke la capacité de production globale de l'unité sous forme de texte libre
 * (ex : "500 tonnes/an", "200 000 unités/mois") pour le formulaire simplifié.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            $table->string('capacite_production', 255)->nullable()->after('nombre_employes')
                  ->comment('Capacité de production installée, saisie en texte libre (ex : 500 t/an)');
        });
    }

    public function down(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            $table->dropColumn('capacite_production');
        });
    }
};
