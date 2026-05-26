<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table des paramètres système de SIGDRI
 *
 * Stocke les préférences configurables par l'administrateur sous forme de
 * paires clé/valeur. Les valeurs par défaut sont définies dans ParametresController
 * et s'appliquent quand aucune entrée n'existe encore en base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {
            // Clé technique unique, ex : 'email_contact_ministere'
            $table->string('cle', 100)->primary()->comment('Identifiant technique du paramètre');

            // Valeur saisie par l'administrateur (texte libre ou numérique)
            $table->text('valeur')->nullable()->comment('Valeur enregistrée par l\'administrateur');

            // Libellé lisible affiché dans l'interface de gestion
            $table->string('libelle', 200)->comment('Libellé humain du paramètre');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
};
