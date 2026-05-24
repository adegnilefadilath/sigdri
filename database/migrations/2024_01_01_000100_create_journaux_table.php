<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table journaux
 *
 * Journal d'audit applicatif : chaque action significative (création, modification,
 * suppression, connexion…) est tracée avec les valeurs avant et après changement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journaux', function (Blueprint $table) {
            $table->id();
            // Null si l'action est déclenchée par le système (tâche cron, migration…)
            $table->foreignId('utilisateur_id')
                  ->nullable()
                  ->constrained('utilisateurs')
                  ->nullOnDelete()
                  ->comment('Utilisateur à l\'origine de l\'action (null = système)');
            $table->string('action', 80)
                  ->comment('Verbe de l\'action effectuée (ex : creation, modification, suppression, connexion)');
            $table->string('table_concernee', 80)->nullable()
                  ->comment('Nom de la table Eloquent modifiée');
            $table->unsignedBigInteger('enregistrement_id')->nullable()
                  ->comment('ID de la ligne modifiée dans la table concernée');
            // Snapshot JSON de l\'état avant modification (null pour une création)
            $table->json('anciennes_valeurs')->nullable()
                  ->comment('Valeurs de l\'enregistrement avant la modification');
            // Snapshot JSON de l\'état après modification (null pour une suppression)
            $table->json('nouvelles_valeurs')->nullable()
                  ->comment('Valeurs de l\'enregistrement après la modification');
            $table->string('ip_address', 45)->nullable()
                  ->comment('Adresse IP du client ayant déclenché l\'action');
            $table->text('user_agent')->nullable()
                  ->comment('En-tête User-Agent pour identifier le navigateur/client');
            // Champ created_at uniquement — un journal ne se modifie pas
            $table->timestamp('created_at')->useCurrent();

            $table->index('utilisateur_id');
            $table->index('action');
            $table->index(['table_concernee', 'enregistrement_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journaux');
    }
};
