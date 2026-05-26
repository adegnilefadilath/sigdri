<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 6 : Paramètres système
 *
 * Permet à l'administrateur de configurer les réglages globaux de SIGDRI
 * sans passer par un déploiement. Chaque paramètre est identifié par une clé
 * technique et stocké en base sous forme de paire clé/valeur.
 *
 * Si une clé n'existe pas encore en base, la valeur par défaut définie dans
 * DEFINITIONS est utilisée à l'affichage.
 */
class ParametresController extends Controller
{
    // ── Définition de tous les paramètres gérables ───────────────────────────
    // 'type' est utilisé pour l'input HTML (text, email, number, textarea).
    private const DEFINITIONS = [
        'nom_plateforme' => [
            'libelle' => 'Nom de la plateforme',
            'defaut'  => 'SIGDRI',
            'type'    => 'text',
            'aide'    => 'Nom affiché dans l\'onglet du navigateur et les en-têtes de documents.',
        ],
        'nom_ministere' => [
            'libelle' => 'Nom du ministère',
            'defaut'  => 'Ministère de l\'Industrie et du Commerce',
            'type'    => 'text',
            'aide'    => 'Apparaît dans les en-têtes des rapports PDF officiels.',
        ],
        'direction_generale' => [
            'libelle' => 'Direction générale',
            'defaut'  => 'Direction Générale de l\'Industrie',
            'type'    => 'text',
            'aide'    => 'Apparaît sous le nom du ministère dans les documents officiels.',
        ],
        'email_contact_ministere' => [
            'libelle' => 'E-mail de contact du ministère',
            'defaut'  => 'contact@industrie.gouv.bj',
            'type'    => 'email',
            'aide'    => 'Adresse affichée dans les communications envoyées aux industriels.',
        ],
        'delai_validation_declarations' => [
            'libelle' => 'Délai de validation des déclarations (jours)',
            'defaut'  => '7',
            'type'    => 'number',
            'aide'    => 'Nombre de jours au-delà duquel une déclaration soumise déclenche une alerte.',
        ],
    ];

    // ── Affichage de la page des paramètres ─────────────────────────────────
    public function index(): View
    {
        // Charge les valeurs enregistrées en base (clé → valeur)
        $valeursSauvees = DB::table('parametres')->pluck('valeur', 'cle');

        // Fusionne avec les définitions statiques pour garantir une valeur
        // par défaut même si aucune entrée n'existe encore en base
        $parametres = collect(self::DEFINITIONS)->map(function ($def, $cle) use ($valeursSauvees) {
            return (object) [
                'cle'     => $cle,
                'libelle' => $def['libelle'],
                'type'    => $def['type'],
                'aide'    => $def['aide'],
                'valeur'  => $valeursSauvees->get($cle, $def['defaut']),
            ];
        });

        return view('admin.parametres.index', compact('parametres'));
    }

    // ── Enregistrement des modifications ────────────────────────────────────
    public function update(Request $request): RedirectResponse
    {
        $clesAutorisees = array_keys(self::DEFINITIONS);

        $request->validate([
            'parametres'                                 => ['required', 'array'],
            'parametres.*'                               => ['nullable', 'string', 'max:500'],
            'parametres.email_contact_ministere'         => ['nullable', 'email', 'max:200'],
            'parametres.delai_validation_declarations'   => ['nullable', 'integer', 'min:1', 'max:365'],
        ], [
            'parametres.email_contact_ministere.email'       => 'L\'adresse e-mail de contact est invalide.',
            'parametres.delai_validation_declarations.min'   => 'Le délai minimum est de 1 jour.',
            'parametres.delai_validation_declarations.max'   => 'Le délai maximum est de 365 jours.',
            'parametres.delai_validation_declarations.integer' => 'Le délai doit être un nombre entier.',
        ]);

        // Enregistre uniquement les clés explicitement définies dans DEFINITIONS
        foreach ($request->input('parametres', []) as $cle => $valeur) {
            if (! in_array($cle, $clesAutorisees, true)) {
                continue; // Ignore les clés inconnues — protection contre l'injection de données
            }

            DB::table('parametres')->updateOrInsert(
                ['cle' => $cle],
                [
                    'valeur'     => $valeur ?? '',
                    'libelle'    => self::DEFINITIONS[$cle]['libelle'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return redirect()->route('admin.parametres.index')
            ->with('statut', 'Paramètres système enregistrés avec succès.');
    }
}
