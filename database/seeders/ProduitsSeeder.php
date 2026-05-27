<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder — Catalogue de produits industriels typiques du Bénin
 *
 * Insère 10 produits finis représentatifs du tissu industriel béninois,
 * tous rattachés à l'unité industrielle de démonstration créée par IndustrielSeeder.
 */
class ProduitsSeeder extends Seeder
{
    public function run(): void
    {
        // Récupération de l'unité industrielle de démonstration
        $unite = DB::table('unites_industrielles')
            ->where('numero_immatriculation', 'RB/COT/23-A-7412')
            ->first();

        // Arrêt silencieux si l'unité de démo n'existe pas encore
        if (! $unite) {
            $this->command->warn('ProduitsSeeder : unité de démonstration introuvable — lancer d\'abord IndustrielSeeder.');
            return;
        }

        $produits = [
            [
                'designation'  => 'Huile de palme raffinée',
                'code_produit' => 'SH 1511.90',
                'unite_mesure' => 'litre',
                'description'  => 'Huile végétale issue du palmier à huile (Elaeis guineensis), raffinée et conditionnée pour la consommation.',
            ],
            [
                'designation'  => 'Coton fibre égrené',
                'code_produit' => 'SH 5201.00',
                'unite_mesure' => 'tonne',
                'description'  => 'Fibre de coton issue de l\'égrenage, prête pour l\'exportation ou la filature locale.',
            ],
            [
                'designation'  => 'Farine de maïs',
                'code_produit' => 'SH 1102.20',
                'unite_mesure' => 'kg',
                'description'  => 'Farine issue de la mouture de maïs jaune ou blanc, destinée à l\'alimentation humaine.',
            ],
            [
                'designation'  => 'Ciment Portland (CEM II / 32,5)',
                'code_produit' => 'SH 2523.29',
                'unite_mesure' => 'tonne',
                'description'  => 'Ciment gris de classe 32,5 MPa, utilisé dans la construction civile.',
            ],
            [
                'designation'  => 'Savon de ménage 400 g',
                'code_produit' => 'SH 3401.11',
                'unite_mesure' => 'pièce',
                'description'  => 'Savon de toilette ou ménager fabriqué à partir d\'huiles végétales locales.',
            ],
            [
                'designation'  => 'Bière locale (bouteille 65 cl)',
                'code_produit' => 'SH 2203.00',
                'unite_mesure' => 'bouteille',
                'description'  => 'Boisson fermentée à base de malt d\'orge et de maïs, conditionnée en bouteille verre consignée.',
            ],
            [
                'designation'  => 'Tissu imprimé (wax 6 yards)',
                'code_produit' => 'SH 5208.52',
                'unite_mesure' => 'pièce',
                'description'  => 'Tissu de coton imprimé par procédé batik industriel (wax), 6 yards par pièce.',
            ],
            [
                'designation'  => 'Eau minérale en bouteille (1,5 L)',
                'code_produit' => 'SH 2201.10',
                'unite_mesure' => 'bouteille',
                'description'  => 'Eau de source ou eau purifiée conditionnée en bouteille plastique PET de 1,5 litre.',
            ],
            [
                'designation'  => 'Briques de terre comprimée (BTC)',
                'code_produit' => 'SH 6901.00',
                'unite_mesure' => 'pièce',
                'description'  => 'Brique de construction fabriquée par compression de terre latéritique, sans cuisson.',
            ],
            [
                'designation'  => 'Concentré de tomate (boîte 140 g)',
                'code_produit' => 'SH 2002.90',
                'unite_mesure' => 'boîte',
                'description'  => 'Pâte de tomate double concentrée conditionnée en boîte métallique de 140 g.',
            ],
        ];

        $maintenant = now();

        foreach ($produits as $p) {
            DB::table('produits')->insertOrIgnore([
                'unite_industrielle_id' => $unite->id,
                'designation'           => $p['designation'],
                'code_produit'          => $p['code_produit'],
                'unite_mesure'          => $p['unite_mesure'],
                'description'           => $p['description'],
                'actif'                 => true,
                'created_at'            => $maintenant,
                'updated_at'            => $maintenant,
            ]);
        }

        $this->command->info('ProduitsSeeder : 10 produits industriels béninois insérés.');
    }
}
