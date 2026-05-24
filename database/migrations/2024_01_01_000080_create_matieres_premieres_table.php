<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table matieres_premieres
 *
 * Consommation de matières premières déclarée par période.
 * Chaque entrée représente une matière première utilisée dans le cadre d'une déclaration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matieres_premieres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')
                  ->constrained('declarations')
                  ->cascadeOnDelete()
                  ->comment('Déclaration à laquelle se rattache cette consommation');
            $table->string('designation', 200)->comment('Nom de la matière première');
            $table->string('code_douanier', 20)->nullable()
                  ->comment('Code SH (système harmonisé) pour les matières importées');
            $table->enum('origine', ['locale', 'importee'])
                  ->comment('Provenance de la matière première');
            $table->string('unite_mesure', 30)->comment('Unité de mesure (kg, tonne, litre, m³…)');
            $table->decimal('quantite_consommee', 15, 3)->default(0)
                  ->comment('Quantité totale consommée sur la période');
            // Valeur en Francs CFA pour le calcul des intrants
            $table->decimal('valeur_fcfa', 18, 2)->default(0)
                  ->comment('Valeur d\'achat en FCFA');
            $table->string('fournisseur', 200)->nullable()
                  ->comment('Nom du fournisseur principal');
            $table->timestamps();

            $table->index('declaration_id');
            $table->index('origine');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matieres_premieres');
    }
};
