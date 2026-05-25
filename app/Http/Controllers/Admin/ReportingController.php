<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 4 : Reporting et statistiques
 *
 * Fournit une page de tableau de bord analytique avec graphiques (Chart.js),
 * et trois formats d'export : PDF (DomPDF), Excel (Maatwebsite) et CSV natif.
 * Tous les calculs respectent les filtres passés en GET (département, secteur,
 * mois, année, statut).
 */
class ReportingController extends Controller
{
    // ── Page principale avec stats + graphiques ───────────────────────────────
    public function index(Request $request): View
    {
        $filtres = $this->filtresDepuisRequete($request);

        // Cartes statistiques
        $stats = $this->calculerStats($filtres);

        // Données pour les trois graphiques Chart.js
        $chartSecteur    = $this->donneesParSecteur($filtres);
        $chartEvolution  = $this->donneesEvolution($filtres);
        $chartDepartement = $this->donneesDepartement($filtres);

        // Valeurs distinctes pour peupler les selects des filtres
        $departements = DB::table('unites_industrielles')
            ->distinct()->orderBy('departement')->pluck('departement');
        $secteurs = DB::table('unites_industrielles')
            ->distinct()->orderBy('secteur_activite')->pluck('secteur_activite');
        $annees = DB::table('declarations')
            ->distinct()->orderByDesc('annee')->pluck('annee');

        return view('admin.reporting.index', compact(
            'filtres', 'stats',
            'chartSecteur', 'chartEvolution', 'chartDepartement',
            'departements', 'secteurs', 'annees'
        ));
    }

    // ── Endpoint JSON pour Chart.js (rechargement dynamique sans reload) ──────
    public function statistiques(Request $request): JsonResponse
    {
        $filtres = $this->filtresDepuisRequete($request);

        return response()->json([
            'stats'           => $this->calculerStats($filtres),
            'par_secteur'     => $this->donneesParSecteur($filtres),
            'evolution'       => $this->donneesEvolution($filtres),
            'par_departement' => $this->donneesDepartement($filtres),
        ]);
    }

    // ── Export PDF — rapport officiel avec en-tête Ministère ─────────────────
    public function exportPDF(Request $request): Response
    {
        $filtres = $this->filtresDepuisRequete($request);

        // Déclarations filtrées (limitées à 500 lignes pour la lisibilité du PDF)
        $declarations = $this->requeteDeclarations($filtres)->limit(500)->get();

        $stats      = $this->calculerStats($filtres);
        $parSecteur = $this->donneesParSecteur($filtres);
        $genereeLe  = now()->format('d/m/Y à H:i');

        $pdf = Pdf::loadView('pdf.rapport', compact(
            'declarations', 'stats', 'parSecteur', 'filtres', 'genereeLe'
        ));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('rapport-sigdri-' . date('Y-m-d') . '.pdf');
    }

    // ── Export Excel multi-feuilles — format SpreadsheetML (Excel 2003 XML)
    // Génération sans dépendance externe : ZipArchive est désactivé dans XAMPP
    // par défaut. Le format .xls XML est ouvert nativement par Excel et LibreOffice.
    public function exportExcel(Request $request): Response
    {
        $filtres  = $this->filtresDepuisRequete($request);
        $decls    = $this->requeteDeclarations($filtres)->get();
        $secteurs = $this->donneesParSecteur($filtres);
        $depts    = $this->donneesDepartement($filtres);
        $fichier  = 'rapport-sigdri-' . date('Y-m-d') . '.xls';

        $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                  'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        // ── Lignes des feuilles ───────────────────────────────────────────
        $lignesDecl = [];
        foreach ($decls as $d) {
            $lignesDecl[] = [
                $d->numero_declaration,
                $d->denomination_unite,
                $d->departement_unite,
                $d->secteur_activite,
                $nomsM[$d->mois] ?? $d->mois,
                $d->annee,
                $d->statut,
                (float) $d->chiffre_affaires_total,
                $d->date_soumission ? date('d/m/Y H:i', strtotime($d->date_soumission)) : '',
            ];
        }

        $lignesSect = [];
        foreach ($secteurs as $s) {
            $lignesSect[] = [$s->secteur_activite, (int)$s->nb_declarations, (float)$s->ca_total];
        }

        $lignesDept = [];
        foreach ($depts as $d) {
            $lignesDept[] = [$d->departement, (int)$d->nb_declarations, (float)$d->ca_total];
        }

        $xml = $this->spreadsheetML([
            [
                'titre'    => 'Déclarations',
                'entetes'  => ['N° Déclaration','Unité industrielle','Département',
                               'Secteur d\'activité','Mois','Année','Statut',
                               'CA Total (FCFA)','Date soumission'],
                'lignes'   => $lignesDecl,
                'couleur'  => '#1A237E',
            ],
            [
                'titre'    => 'Par secteur',
                'entetes'  => ['Secteur d\'activité','Nb déclarations','CA Total (FCFA)'],
                'lignes'   => $lignesSect,
                'couleur'  => '#F97316',
            ],
            [
                'titre'    => 'Par département',
                'entetes'  => ['Département','Nb déclarations','CA Total (FCFA)'],
                'lignes'   => $lignesDept,
                'couleur'  => '#1A237E',
            ],
        ]);

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fichier . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ── Génère un classeur SpreadsheetML (Excel 2003 XML, multi-feuilles) ──────
    // Chaque feuille : ['titre', 'entetes', 'lignes', 'couleur']
    private function spreadsheetML(array $feuilles): string
    {
        $esc = fn (string $v): string => htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // Styles : un par couleur d'en-tête + le style par défaut
        $stylesXml = '<Styles>';
        $stylesXml .= '<Style ss:ID="Default" ss:Name="Normal">'
            . '<Alignment ss:Vertical="Bottom"/>'
            . '<Font ss:FontName="Calibri" ss:Size="11"/>'
            . '</Style>';

        $styleIds  = [];
        $seenCols  = [];
        foreach ($feuilles as $f) {
            $col = $f['couleur'];
            if (isset($seenCols[$col])) {
                $styleIds[$col] = $seenCols[$col];
                continue;
            }
            $id = 'h' . count($seenCols);
            $seenCols[$col] = $id;
            $styleIds[$col] = $id;
            $stylesXml .= '<Style ss:ID="' . $id . '">'
                . '<Font ss:Bold="1" ss:Color="#FFFFFF" ss:FontName="Calibri" ss:Size="11"/>'
                . '<Interior ss:Color="' . $esc($col) . '" ss:Pattern="Solid"/>'
                . '</Style>';
        }
        $stylesXml .= '</Styles>';

        // Feuilles
        $feuillesXml = '';
        foreach ($feuilles as $f) {
            $feuillesXml .= '<Worksheet ss:Name="' . $esc($f['titre']) . '">';
            $feuillesXml .= '<Table>';

            // Ligne d'en-tête
            $feuillesXml .= '<Row>';
            foreach ($f['entetes'] as $h) {
                $feuillesXml .= '<Cell ss:StyleID="' . $styleIds[$f['couleur']] . '">'
                    . '<Data ss:Type="String">' . $esc((string)$h) . '</Data></Cell>';
            }
            $feuillesXml .= '</Row>';

            // Lignes de données
            foreach ($f['lignes'] as $ligne) {
                $feuillesXml .= '<Row>';
                foreach ($ligne as $val) {
                    if (is_int($val) || is_float($val)) {
                        $feuillesXml .= '<Cell><Data ss:Type="Number">' . $val . '</Data></Cell>';
                    } else {
                        $feuillesXml .= '<Cell><Data ss:Type="String">' . $esc((string)($val ?? '')) . '</Data></Cell>';
                    }
                }
                $feuillesXml .= '</Row>';
            }

            $feuillesXml .= '</Table>';
            $feuillesXml .= '<WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">'
                . '<Selected/></WorksheetOptions>';
            $feuillesXml .= '</Worksheet>';
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<?mso-application progid="Excel.Sheet"?>' . "\n"
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:o="urn:schemas-microsoft-com:office:office"'
            . ' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            . ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            . ' xmlns:html="http://www.w3.org/TR/REC-html40">'
            . $stylesXml
            . $feuillesXml
            . '</Workbook>';
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ══════════════════════════════════════════════════════════════════════════

    // ── Extraction et nettoyage des filtres depuis la requête GET ─────────────
    private function filtresDepuisRequete(Request $request): array
    {
        return [
            'departement' => (string) $request->get('departement', ''),
            'secteur'     => (string) $request->get('secteur', ''),
            'annee'       => (string) $request->get('annee', ''),
            'mois'        => (string) $request->get('mois', ''),
            'statut'      => (string) $request->get('statut', ''),
        ];
    }

    // ── Construction de la requête de base avec filtres appliqués ─────────────
    private function requeteDeclarations(array $filtres): \Illuminate\Database\Query\Builder
    {
        return DB::table('declarations')
            ->join('unites_industrielles',
                'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->select(
                'declarations.id',
                'declarations.numero_declaration',
                'declarations.statut',
                'declarations.mois',
                'declarations.annee',
                'declarations.date_soumission',
                'declarations.chiffre_affaires_total',
                'unites_industrielles.denomination as denomination_unite',
                'unites_industrielles.departement as departement_unite',
                'unites_industrielles.secteur_activite'
            )
            ->when($filtres['departement'],
                fn ($q) => $q->where('unites_industrielles.departement', $filtres['departement']))
            ->when($filtres['secteur'],
                fn ($q) => $q->where('unites_industrielles.secteur_activite', $filtres['secteur']))
            ->when($filtres['annee'],
                fn ($q) => $q->where('declarations.annee', (int) $filtres['annee']))
            ->when($filtres['mois'],
                fn ($q) => $q->where('declarations.mois', (int) $filtres['mois']))
            ->when($filtres['statut'],
                fn ($q) => $q->where('declarations.statut', $filtres['statut']))
            ->orderByDesc('declarations.annee')
            ->orderByDesc('declarations.mois');
    }

    // ── Calcul des quatre cartes statistiques ─────────────────────────────────
    private function calculerStats(array $filtres): array
    {
        // Fabrique de requête : clone logique via une closure pour éviter
        // la mutation entre les différents appels d'agrégat.
        $q = fn () => DB::table('declarations')
            ->join('unites_industrielles',
                'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->when($filtres['departement'],
                fn ($r) => $r->where('unites_industrielles.departement', $filtres['departement']))
            ->when($filtres['secteur'],
                fn ($r) => $r->where('unites_industrielles.secteur_activite', $filtres['secteur']))
            ->when($filtres['annee'],
                fn ($r) => $r->where('declarations.annee', (int) $filtres['annee']))
            ->when($filtres['mois'],
                fn ($r) => $r->where('declarations.mois', (int) $filtres['mois']))
            ->when($filtres['statut'],
                fn ($r) => $r->where('declarations.statut', $filtres['statut']));

        return [
            'total_declarations'    => $q()->count(),
            'ca_total'              => (float) $q()->sum('declarations.chiffre_affaires_total'),
            'unites_declarantes'    => $q()->distinct()->count('declarations.unite_industrielle_id'),
            'declarations_validees' => $q()->where('declarations.statut', 'validee')->count(),
        ];
    }

    // ── Données pour le graphique barres : CA et nombre de déclarations par secteur
    private function donneesParSecteur(array $filtres): Collection
    {
        return DB::table('declarations')
            ->join('unites_industrielles',
                'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->when($filtres['departement'],
                fn ($q) => $q->where('unites_industrielles.departement', $filtres['departement']))
            ->when($filtres['annee'],
                fn ($q) => $q->where('declarations.annee', (int) $filtres['annee']))
            ->when($filtres['mois'],
                fn ($q) => $q->where('declarations.mois', (int) $filtres['mois']))
            ->when($filtres['statut'],
                fn ($q) => $q->where('declarations.statut', $filtres['statut']))
            ->groupBy('unites_industrielles.secteur_activite')
            ->select(
                'unites_industrielles.secteur_activite',
                DB::raw('COUNT(*) as nb_declarations'),
                DB::raw('SUM(declarations.chiffre_affaires_total) as ca_total')
            )
            ->orderByDesc('ca_total')
            ->get();
    }

    // ── Données pour le graphique courbe : évolution mensuelle des déclarations
    private function donneesEvolution(array $filtres): Collection
    {
        return DB::table('declarations')
            ->join('unites_industrielles',
                'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->when($filtres['departement'],
                fn ($q) => $q->where('unites_industrielles.departement', $filtres['departement']))
            ->when($filtres['secteur'],
                fn ($q) => $q->where('unites_industrielles.secteur_activite', $filtres['secteur']))
            ->when($filtres['annee'],
                fn ($q) => $q->where('declarations.annee', (int) $filtres['annee']))
            ->when($filtres['statut'],
                fn ($q) => $q->where('declarations.statut', $filtres['statut']))
            ->groupBy('declarations.annee', 'declarations.mois')
            ->select(
                'declarations.annee',
                'declarations.mois',
                DB::raw('COUNT(*) as nb_declarations'),
                DB::raw('SUM(declarations.chiffre_affaires_total) as ca_total')
            )
            ->orderBy('declarations.annee')
            ->orderBy('declarations.mois')
            ->get();
    }

    // ── Données pour le camembert : répartition des déclarations par département
    private function donneesDepartement(array $filtres): Collection
    {
        return DB::table('declarations')
            ->join('unites_industrielles',
                'declarations.unite_industrielle_id', '=', 'unites_industrielles.id')
            ->when($filtres['secteur'],
                fn ($q) => $q->where('unites_industrielles.secteur_activite', $filtres['secteur']))
            ->when($filtres['annee'],
                fn ($q) => $q->where('declarations.annee', (int) $filtres['annee']))
            ->when($filtres['mois'],
                fn ($q) => $q->where('declarations.mois', (int) $filtres['mois']))
            ->when($filtres['statut'],
                fn ($q) => $q->where('declarations.statut', $filtres['statut']))
            ->groupBy('unites_industrielles.departement')
            ->select(
                'unites_industrielles.departement',
                DB::raw('COUNT(*) as nb_declarations'),
                DB::raw('SUM(declarations.chiffre_affaires_total) as ca_total')
            )
            ->orderByDesc('nb_declarations')
            ->get();
    }
}
