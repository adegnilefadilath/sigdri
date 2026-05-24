<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : utilisateurs, jetons_reinitialisation_mdp, sessions
 *
 * Remplace la table générique Laravel « users » par une table métier SIGDRI.
 * Les trois tables sont regroupées ici car elles forment le bloc d'authentification.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Table principale des utilisateurs ────────────────────────────────────
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100)->comment('Nom de famille');
            $table->string('prenom', 100)->comment('Prénom');
            $table->string('email')->unique()->comment('Adresse e-mail — identifiant de connexion');
            $table->timestamp('email_verifie_le')->nullable()->comment('Date de vérification de l\'adresse e-mail');
            $table->string('mot_de_passe')->comment('Hash bcrypt du mot de passe');
            // Rôles métier SIGDRI
            $table->enum('role', [
                'super_admin',  // accès total
                'admin',        // gestion des utilisateurs et paramètres
                'inspecteur',   // consultation et validation des déclarations
                'declarant',    // saisie et soumission des déclarations
            ])->default('declarant')->comment('Niveau d\'accès dans l\'application');
            $table->boolean('actif')->default(true)->comment('Compte actif ou suspendu');
            $table->timestamp('derniere_connexion')->nullable()->comment('Horodatage de la dernière connexion réussie');
            $table->rememberToken();
            $table->timestamps();

            $table->index('role');
            $table->index('actif');
        });

        // ── Jetons de réinitialisation de mot de passe ───────────────────────────
        Schema::create('jetons_reinitialisation_mdp', function (Blueprint $table) {
            $table->string('email')->primary()->comment('E-mail de l\'utilisateur demandant la réinitialisation');
            $table->string('jeton')->comment('Jeton hashé envoyé par e-mail');
            $table->timestamp('created_at')->nullable();
        });

        // ── Sessions HTTP (driver « database » de Laravel) ───────────────────────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            // Référence nullable : sessions anonymes possibles
            $table->foreignId('user_id')->nullable()->index()->comment('ID de l\'utilisateur connecté (null si invité)');
            $table->string('ip_address', 45)->nullable()->comment('Adresse IP du client');
            $table->text('user_agent')->nullable()->comment('En-tête User-Agent du navigateur');
            $table->longText('payload')->comment('Données de session sérialisées');
            $table->integer('last_activity')->index()->comment('Timestamp UNIX de la dernière activité');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('jetons_reinitialisation_mdp');
        Schema::dropIfExists('utilisateurs');
    }
};
