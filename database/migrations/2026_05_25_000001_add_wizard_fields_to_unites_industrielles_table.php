<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration — Ajout des champs du formulaire wizard à la table unites_industrielles
 *
 * Aligne la structure de la table avec le formulaire officiel
 * d'Autorisation d'Installation Industrielle du portail service-public.bj.
 *
 * Commande : php artisan migrate
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            // ── Étape 1 — Identification de l'entreprise ────────────────────
            $table->unsignedBigInteger('capital_social')->nullable()
                  ->comment('Capital social en FCFA');

            $table->text('objet_social')->nullable()
                  ->comment('Objet social de l\'entreprise (activité principale)');

            // Prénom(s) du promoteur (le nom est déjà dans responsable_nom)
            $table->string('prenom_promoteur', 150)->nullable()
                  ->comment('Prénom(s) du promoteur / dirigeant');

            // Adresse personnelle du promoteur (distincte du siège social)
            $table->text('adresse_promoteur')->nullable()
                  ->comment('Adresse personnelle du promoteur');

            // ── Étape 2 — Informations du projet ────────────────────────────
            $table->string('quartier_village', 150)->nullable()
                  ->comment('Quartier ou village d\'implantation du site');

            $table->string('coordonnees_geographiques', 100)->nullable()
                  ->comment('Coordonnées GPS du site industriel (lat, lng)');

            $table->text('matieres_premieres')->nullable()
                  ->comment('Principales matières premières utilisées');

            // JSON : [{nom, capacite_mensuelle, capacite_annuelle}]
            $table->json('produits_prevus')->nullable()
                  ->comment('Liste des produits prévus avec capacités de production');

            // ── Étape 3 — Informations financières ──────────────────────────
            $table->string('type_investissement', 150)->nullable()
                  ->comment('Nature de l\'investissement (création, extension, réhabilitation…)');

            $table->unsignedBigInteger('montant_investissements')->nullable()
                  ->comment('Montant total des investissements en FCFA');

            $table->decimal('pourcentage_fonds_propres', 5, 2)->nullable()
                  ->comment('Part des fonds propres en pourcentage');

            $table->decimal('pourcentage_emprunt', 5, 2)->nullable()
                  ->comment('Part financée par emprunt en pourcentage');

            $table->unsignedInteger('nombre_employes')->nullable()
                  ->comment('Nombre d\'emplois prévus à la création');

            $table->string('numero_certificat_env', 100)->nullable()
                  ->comment('Numéro du certificat de conformité environnementale');

            // ── Étape 4 — Pièces justificatives ─────────────────────────────
            // JSON : {extrait_rccm: "path", titre_propriete: "path", ...}
            $table->json('documents')->nullable()
                  ->comment('Chemins des pièces justificatives téléversées dans storage/public/unites/');
        });
    }

    public function down(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            $table->dropColumn([
                'capital_social',
                'objet_social',
                'prenom_promoteur',
                'adresse_promoteur',
                'quartier_village',
                'coordonnees_geographiques',
                'matieres_premieres',
                'produits_prevus',
                'type_investissement',
                'montant_investissements',
                'pourcentage_fonds_propres',
                'pourcentage_emprunt',
                'nombre_employes',
                'numero_certificat_env',
                'documents',
            ]);
        });
    }
};
