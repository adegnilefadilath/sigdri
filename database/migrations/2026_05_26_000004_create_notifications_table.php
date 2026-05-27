<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Table des notifications in-app
 *
 * Stocke les notifications destinées aux industriels :
 * validations/rejets de déclarations et alertes sur les agréments.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilisateur_id');
            $table->string('titre', 255);
            $table->text('message');
            $table->enum('type', [
                'declaration_validee',
                'declaration_rejetee',
                'agrement_expirant',
                'agrement_expire',
                'alerte_systeme',
            ])->default('alerte_systeme');
            $table->boolean('lu')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('utilisateur_id')
                  ->references('id')
                  ->on('utilisateurs')
                  ->onDelete('cascade');

            $table->index(['utilisateur_id', 'lu']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
