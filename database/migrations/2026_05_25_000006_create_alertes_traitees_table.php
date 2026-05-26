<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table alertes_traitees
 *
 * Trace les alertes que l'admin a marquées comme traitées.
 * Chaque entrée identifie une alerte par son type et l'ID de l'enregistrement
 * source (agrément ou déclaration). La contrainte d'unicité empêche les
 * doublons en cas de double-clic sur le bouton "Traiter".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertes_traitees', function (Blueprint $table) {
            $table->id();

            // Type de l'alerte : ce que l'on suit
            $table->string('type', 60)
                  ->comment('agrement_expirant | agrement_expire | declaration_en_attente');

            // ID de l'enregistrement source (agrément.id ou déclarations.id)
            $table->unsignedBigInteger('reference_id')
                  ->comment('Identifiant de l\'agrément ou de la déclaration concernée');

            // Qui a traité l'alerte
            $table->string('traitee_par', 150)->nullable()
                  ->comment('Nom de l\'agent ayant traité l\'alerte');

            $table->timestamp('created_at')->useCurrent()
                  ->comment('Horodatage du traitement');

            // Une même alerte ne peut être marquée traitée qu'une seule fois
            $table->unique(['type', 'reference_id'], 'alerte_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertes_traitees');
    }
};
