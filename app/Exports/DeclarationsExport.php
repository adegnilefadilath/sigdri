<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel — Feuille « Déclarations »
 *
 * Génère une feuille Excel avec la liste complète des déclarations filtrées.
 * Utilisable seule ou en tant que feuille au sein de StatistiquesExport.
 */
class DeclarationsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    /** Noms des mois en français pour la colonne "Mois" */
    private const MOIS = [
        '', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre',
    ];

    public function __construct(
        private Collection $declarations,
        private string     $titre = 'Déclarations'
    ) {}

    // ── Source de données ─────────────────────────────────────────────────────
    public function collection(): Collection
    {
        return $this->declarations;
    }

    // ── En-têtes des colonnes ─────────────────────────────────────────────────
    public function headings(): array
    {
        return [
            'N° Déclaration',
            'Unité industrielle',
            'Département',
            'Secteur d\'activité',
            'Mois',
            'Année',
            'Statut',
            'CA Total (FCFA)',
            'Date soumission',
        ];
    }

    // ── Transformation de chaque ligne ────────────────────────────────────────
    public function map($row): array
    {
        return [
            $row->numero_declaration,
            $row->denomination_unite,
            $row->departement_unite,
            $row->secteur_activite,
            self::MOIS[$row->mois] ?? $row->mois,
            $row->annee,
            $row->statut,
            (float) $row->chiffre_affaires_total,
            $row->date_soumission ? date('d/m/Y H:i', strtotime($row->date_soumission)) : '',
        ];
    }

    // ── Nom de la feuille ─────────────────────────────────────────────────────
    public function title(): string
    {
        return $this->titre;
    }

    // ── Mise en forme : ligne d'en-tête colorée en bleu SIGDRI ───────────────
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A237E'],
                ],
            ],
        ];
    }
}
