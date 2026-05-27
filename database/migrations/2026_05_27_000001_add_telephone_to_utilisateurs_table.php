<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Ajout d'un numéro de téléphone optionnel sur les utilisateurs
 *
 * Utilisé dans le profil de l'espace industriel pour le contact direct.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->string('telephone', 30)
                  ->nullable()
                  ->after('email')
                  ->comment('Numéro de téléphone (optionnel)');
        });
    }

    public function down(): void
    {
        Schema::table('utilisateurs', function (Blueprint $table) {
            $table->dropColumn('telephone');
        });
    }
};
