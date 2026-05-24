<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table declarations
 *
 * Enregistrement principal de chaque déclaration industrielle soumise via le SIGDRI.
 * Une déclaration lie une unité industrielle à une période et contient les lignes de
 * production (produits) et les consommations de matières premières.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('declarations', function (Blueprint $table) {
            $table->id();
            $table->string('numero_declaration', 30)->unique()
                  ->comment('Numéro de référence généré automatiquement (ex : DECL-2024-00042)');
            $table->foreignId('unite_industrielle_id')
                  ->constrained('unites_industrielles')
                  ->comment('Unité industrielle ayant soumis la déclaration');
            $table->foreignId('periode_id')
                  ->constrained('periodes')
                  ->comment('Période de déclaration concernée');
            // Utilisateur ayant créé/soumis la déclaration
            $table->foreignId('declarant_id')
                  ->constrained('utilisateurs')
                  ->comment('Compte utilisateur ayant saisi la déclaration');
            // Inspecteur ayant validé ou rejeté (nullable tant que non traité)
            $table->foreignId('validateur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete()
                  ->comment('Inspecteur ayant statué sur la déclaration');
            $table->enum('statut', ['brouillon', 'soumise', 'en_revision', 'validee', 'rejetee'])
                  ->default('brouillon')
                  ->comment('État d\'avancement de la déclaration dans le circuit de validation');
            $table->timestamp('date_soumission')->nullable()->comment('Date et heure de soumission officielle');
            $table->timestamp('date_validation')->nullable()->comment('Date et heure de validation ou rejet');
            $table->text('observations')->nullable()->comment('Remarques du déclarant ou de l\'inspecteur');
            $table->timestamps();

            $table->index('unite_industrielle_id');
            $table->index('periode_id');
            $table->index('statut');
            $table->index('date_soumission');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('declarations');
    }
};
