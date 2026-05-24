<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table rapports
 *
 * Trace les rapports statistiques ou de synthèse générés (PDF, Excel…).
 * Un rapport peut être global (toutes unités) ou ciblé sur une unité et une période.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            // Null = rapport agrégé sur toutes les unités
            $table->foreignId('unite_industrielle_id')
                  ->nullable()
                  ->constrained('unites_industrielles')
                  ->nullOnDelete()
                  ->comment('Unité ciblée (null = rapport global)');
            // Null = rapport non limité à une période précise
            $table->foreignId('periode_id')
                  ->nullable()
                  ->constrained('periodes')
                  ->nullOnDelete()
                  ->comment('Période couverte par le rapport (null = toutes périodes)');
            $table->foreignId('genere_par')
                  ->constrained('utilisateurs')
                  ->comment('Utilisateur ayant demandé la génération');
            $table->string('type_rapport', 100)
                  ->comment('Nature du rapport (ex : synthese_production, bilan_annuel, agrement)');
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf')
                  ->comment('Format du fichier généré');
            $table->string('chemin_fichier', 255)->nullable()
                  ->comment('Chemin du fichier dans storage/ (null si génération en cours)');
            $table->enum('statut', ['en_cours', 'disponible', 'erreur'])->default('en_cours')
                  ->comment('État de la génération du fichier');
            $table->timestamps();

            $table->index('unite_industrielle_id');
            $table->index('periode_id');
            $table->index('statut');
            $table->index('genere_par');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapports');
    }
};
