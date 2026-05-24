<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : table agrements
 *
 * Stocke les agréments et autorisations d'exploitation délivrés aux unités industrielles.
 * Une unité peut posséder plusieurs agréments de types différents.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agrements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unite_industrielle_id')
                  ->constrained('unites_industrielles')
                  ->cascadeOnDelete()
                  ->comment('Unité industrielle titulaire de l\'agrément');
            $table->string('numero_agrement', 80)->unique()
                  ->comment('Numéro officiel de l\'agrément (unique)');
            $table->string('type_agrement', 150)
                  ->comment('Nature de l\'agrément (ex : exploitation, exportation, zone franche)');
            $table->date('date_delivrance')->comment('Date d\'émission de l\'agrément');
            $table->date('date_expiration')->nullable()->comment('Date d\'expiration (null = durée indéterminée)');
            $table->enum('statut', ['valide', 'expire', 'suspendu', 'revoque'])
                  ->default('valide')
                  ->comment('État courant de l\'agrément');
            // Chemin relatif vers le fichier numérisé (PDF ou image)
            $table->string('chemin_document', 255)->nullable()
                  ->comment('Chemin du document scanné stocké dans storage/');
            $table->text('observations')->nullable()->comment('Remarques ou conditions particulières');
            $table->timestamps();

            $table->index('unite_industrielle_id');
            $table->index('statut');
            $table->index('date_expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agrements');
    }
};
