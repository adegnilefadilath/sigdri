<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration : ajout des coordonnées GPS aux unités industrielles
 *
 * latitude / longitude sont nullables : une unité sans coordonnées saisies
 * sera positionnée par le contrôleur de cartographie sur le centroïde
 * de son département (coordonnées approximatives du Bénin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            $table->decimal('latitude',  10, 7)->nullable()->after('actif')
                  ->comment('Latitude GPS de l\'unité (WGS-84) — null si non renseignée');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude')
                  ->comment('Longitude GPS de l\'unité (WGS-84) — null si non renseignée');
        });
    }

    public function down(): void
    {
        Schema::table('unites_industrielles', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
