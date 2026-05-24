<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table lignes_declaration
 *
 * Détail des quantités produites par produit au sein d'une déclaration.
 * Chaque ligne correspond à un produit déclaré pour la période concernée.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lignes_declaration', function (Blueprint $table) {
            $table->id();
            $table->foreignId('declaration_id')
                  ->constrained('declarations')
                  ->cascadeOnDelete()
                  ->comment('Déclaration parente');
            $table->foreignId('produit_id')
                  ->constrained('produits')
                  ->comment('Produit concerné par cette ligne');
            // Quantités exprimées dans l'unité de mesure du produit
            $table->decimal('quantite_produite', 15, 3)->default(0)
                  ->comment('Volume total produit sur la période');
            $table->decimal('quantite_exportee', 15, 3)->default(0)
                  ->comment('Part exportée de la production');
            $table->decimal('quantite_vendue_local', 15, 3)->default(0)
                  ->comment('Part vendue sur le marché local');
            // Valeur en Francs CFA (FCFA)
            $table->decimal('valeur_fcfa', 18, 2)->default(0)
                  ->comment('Valeur totale de la production en FCFA');
            $table->text('observations')->nullable()->comment('Remarques spécifiques à cette ligne');
            $table->timestamps();

            // Une déclaration ne peut contenir qu'une seule ligne par produit
            $table->unique(['declaration_id', 'produit_id']);
            $table->index('declaration_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lignes_declaration');
    }
};
