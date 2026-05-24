<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table unites_industrielles
 *
 * Référentiel des entreprises/unités industrielles déclarantes suivies par le SIGDRI.
 * Chaque unité est identifiée par un numéro d'immatriculation unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unites_industrielles', function (Blueprint $table) {
            $table->id();
            $table->string('denomination', 200)->comment('Raison sociale ou dénomination officielle');
            $table->string('numero_immatriculation', 50)->unique()
                  ->comment('Numéro d\'immatriculation au registre de commerce (unique)');
            $table->string('secteur_activite', 150)->comment('Secteur industriel (ex : agroalimentaire, textile…)');
            // Régime douanier ou fiscal applicable à l'unité
            $table->string('regime', 100)->nullable()->comment('Régime applicable (ex : zone franche, droit commun)');
            $table->string('adresse', 255)->comment('Adresse physique du siège');
            $table->string('commune', 100)->comment('Commune d\'implantation');
            $table->string('departement', 100)->comment('Département d\'implantation');
            $table->string('telephone', 20)->nullable()->comment('Numéro de téléphone principal');
            $table->string('email', 150)->nullable()->comment('Adresse e-mail de contact de l\'unité');
            $table->string('responsable_nom', 150)->nullable()->comment('Nom du responsable / directeur');
            $table->string('responsable_fonction', 100)->nullable()->comment('Fonction du responsable');
            $table->boolean('actif')->default(true)->comment('Unité active ou radiée/suspendue');
            $table->timestamps();

            $table->index('secteur_activite');
            $table->index('departement');
            $table->index('actif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unites_industrielles');
    }
};
