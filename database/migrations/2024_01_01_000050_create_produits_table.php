<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table produits
 *
 * Catalogue des produits finis fabriqués par chaque unité industrielle.
 * Ce référentiel est utilisé pour remplir les lignes de déclaration de production.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unite_industrielle_id')
                  ->constrained('unites_industrielles')
                  ->cascadeOnDelete()
                  ->comment('Unité industrielle qui fabrique ce produit');
            $table->string('designation', 200)->comment('Nom commercial du produit');
            $table->string('code_produit', 50)->nullable()->comment('Code interne ou code SH douanier');
            $table->string('unite_mesure', 30)->comment('Unité de mesure (tonne, litre, m², pièce…)');
            $table->text('description')->nullable()->comment('Description technique ou commerciale du produit');
            $table->boolean('actif')->default(true)->comment('Produit encore fabriqué ou arrêté');
            $table->timestamps();

            $table->index('unite_industrielle_id');
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
