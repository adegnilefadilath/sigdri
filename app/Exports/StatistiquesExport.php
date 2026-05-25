<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Excel multi-feuilles — Rapport SIGDRI
 *
 * Feuille 1 : liste des déclarations filtrées (via DeclarationsExport)
 * Feuille 2 : statistiques agrégées par secteur d'activité
 * Feuille 3 : statistiques agrégées par département
 */
class StatistiquesExport implements WithMultipleSheets
{
    public function __construct(
        private Collection $declarations,
        private Collection $parSecteur,
        private Collection $parDepartement
    ) {}

    // ── Définition des feuilles du classeur ───────────────────────────────────
    public function sheets(): array
    {
        return [
            // Feuille 1 : déclarations complètes
            new DeclarationsExport($this->declarations, 'Déclarations'),

            // Feuille 2 : CA et nombre de déclarations par secteur
            new SecteurSheet($this->parSecteur),

            // Feuille 3 : répartition des déclarations par département
            new DepartementSheet($this->parDepartement),
        ];
    }
}

// ── Feuille « Par secteur » ──────────────────────────────────────────────────
// Définie dans le même fichier car elle n'est utilisée que par StatistiquesExport.
class SecteurSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(private Collection $data) {}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Secteur d\'activité', 'Nb déclarations', 'CA Total (FCFA)'];
    }

    public function map($row): array
    {
        return [
            $row->secteur_activite,
            (int)   $row->nb_declarations,
            (float) $row->ca_total,
        ];
    }

    public function title(): string
    {
        return 'Par secteur';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF97316']],
            ],
        ];
    }
}

// ── Feuille « Par département » ──────────────────────────────────────────────
class DepartementSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles
{
    public function __construct(private Collection $data) {}

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return ['Département', 'Nb déclarations', 'CA Total (FCFA)'];
    }

    public function map($row): array
    {
        return [
            $row->departement,
            (int)   $row->nb_declarations,
            (float) $row->ca_total,
        ];
    }

    public function title(): string
    {
        return 'Par département';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A237E']],
            ],
        ];
    }
}
