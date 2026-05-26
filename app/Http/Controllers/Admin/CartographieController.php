<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Contrôleur admin — Module 5 : Cartographie des unités industrielles
 *
 * Fournit la page carte (Leaflet.js + OpenStreetMap) et l'endpoint JSON
 * consommé par le client pour charger et filtrer les marqueurs.
 *
 * Les unités sans coordonnées GPS saisies sont positionnées sur le centroïde
 * de leur département (table de correspondance ci-dessous), avec un léger
 * décalage déterministe basé sur leur identifiant pour éviter la superposition.
 */
class CartographieController extends Controller
{
    // ── Centroïdes approximatifs des 12 départements du Bénin (WGS-84) ────────
    private const COORDS_DEPARTEMENTS = [
        'Alibori'    => [11.22,  2.76],
        'Atacora'    => [10.63,  1.65],
        'Atlantique' => [ 6.62,  2.37],
        'Borgou'     => [ 9.73,  2.65],
        'Collines'   => [ 8.10,  2.30],
        'Couffo'     => [ 6.92,  1.75],
        'Donga'      => [ 9.72,  1.72],
        'Littoral'   => [ 6.37,  2.42],
        'Mono'       => [ 6.82,  1.73],
        'Ouémé'      => [ 6.56,  2.61],
        'Plateau'    => [ 7.34,  2.60],
        'Zou'        => [ 7.52,  2.25],
    ];

    // Coordonnées de repli si le département est inconnu (centre géographique du Bénin)
    private const BENIN_CENTRE = [9.30, 2.30];

    // ── Page principale — carte avec toutes les unités actives ────────────────
    public function index(): View
    {
        // Valeurs pour les selects de filtres
        $departements = DB::table('unites_industrielles')
            ->where('actif', true)
            ->distinct()->orderBy('departement')->pluck('departement');

        $secteurs = DB::table('unites_industrielles')
            ->where('actif', true)
            ->distinct()->orderBy('secteur_activite')->pluck('secteur_activite');

        // Compteur global affiché dans la barre d'en-tête
        $totalUnites = DB::table('unites_industrielles')->where('actif', true)->count();

        return view('admin.cartographie.index', compact('departements', 'secteurs', 'totalUnites'));
    }

    // ── Endpoint JSON pour Leaflet — retourne les marqueurs filtrés ───────────
    public function donnees(Request $request): JsonResponse
    {
        $unites = $this->requeteUnites($request)->get();

        $marqueurs = $unites->map(function ($u) {
            [$lat, $lng] = $this->coordonnees($u);

            return [
                'id'              => $u->id,
                'lat'             => $lat,
                'lng'             => $lng,
                'couleur'         => $this->couleurStatut($u),
                'nom'             => $u->denomination,
                'secteur'         => $u->secteur_activite,
                'departement'     => $u->departement,
                'commune'         => $u->commune,
                'statut_agrement' => $u->statut_agrement ?? 'aucun',
                'numero_agrement' => $u->numero_agrement  ?? '—',
                'type_agrement'   => $u->type_agrement    ?? '—',
                'date_expiration' => $u->date_expiration,
            ];
        });

        return response()->json($marqueurs);
    }

    // ── Filtre via formulaire (délègue à donnees() — même logique, GET) ───────
    public function filtrer(Request $request): JsonResponse
    {
        return $this->donnees($request);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // Méthodes privées
    // ══════════════════════════════════════════════════════════════════════════

    // ── Construction de la requête principale avec jointure sur le dernier agrément
    // Pour chaque unité, on retient l'agrément non révoqué le plus récent
    // (MAX(id)) via une sous-requête groupée.
    private function requeteUnites(Request $request): \Illuminate\Database\Query\Builder
    {
        // Sous-requête : dernier agrément (hors révoqué) par unité
        $dernierAgrement = DB::table('agrements')
            ->select(DB::raw('MAX(id) as id'), 'unite_industrielle_id')
            ->where('statut', '!=', 'revoque')
            ->groupBy('unite_industrielle_id');

        $query = DB::table('unites_industrielles as u')
            ->leftJoinSub($dernierAgrement, 'da', 'da.unite_industrielle_id', '=', 'u.id')
            ->leftJoin('agrements as a', 'a.id', '=', 'da.id')
            ->select(
                'u.id',
                'u.denomination',
                'u.departement',
                'u.commune',
                'u.secteur_activite',
                'u.latitude',
                'u.longitude',
                'a.numero_agrement',
                'a.type_agrement',
                'a.statut as statut_agrement',
                'a.date_expiration'
            )
            ->where('u.actif', true);

        // ── Filtres optionnels ──────────────────────────────────────────────
        $query->when($request->filled('departement'),
            fn ($q) => $q->where('u.departement', $request->departement));

        $query->when($request->filled('secteur'),
            fn ($q) => $q->where('u.secteur_activite', $request->secteur));

        // Le filtre "statut_agrement" peut valoir : valide, expire, suspendu, aucun
        if ($request->filled('statut_agrement')) {
            if ($request->statut_agrement === 'aucun') {
                $query->whereNull('a.statut');
            } else {
                $query->where('a.statut', $request->statut_agrement);
            }
        }

        return $query->orderBy('u.denomination');
    }

    // ── Calcul des coordonnées : GPS saisi sinon centroïde du département
    // Un décalage déterministe (sin/cos de l'ID) évite la superposition de
    // marqueurs partageant le même centroïde départemental.
    private function coordonnees(object $u): array
    {
        if ($u->latitude !== null && $u->longitude !== null) {
            return [(float) $u->latitude, (float) $u->longitude];
        }

        // Récupération du centroïde ou repli national
        [$latBase, $lngBase] = self::COORDS_DEPARTEMENTS[$u->departement] ?? self::BENIN_CENTRE;

        // Décalage pseudo-aléatoire mais reproductible — rayon ~5 km max
        $decalage = 0.045;
        $angle    = $u->id * 2.399963; // pas d'angle ≈ nombre d'or × 2π
        $lat = round($latBase + $decalage * sin($angle), 6);
        $lng = round($lngBase + $decalage * cos($angle), 6);

        return [$lat, $lng];
    }

    // ── Couleur du marqueur selon le statut de l'agrément ─────────────────────
    // vert   = agrément valide et non proche de l'expiration
    // orange = expire dans ≤ 30 jours OU suspendu
    // rouge  = expiré
    // gris   = aucun agrément enregistré
    private function couleurStatut(object $u): string
    {
        if ($u->statut_agrement === null) {
            return 'gris';
        }

        if ($u->statut_agrement === 'expire') {
            return 'rouge';
        }

        if ($u->statut_agrement === 'suspendu') {
            return 'orange';
        }

        if ($u->statut_agrement === 'valide') {
            // Agrément valide mais expirant dans moins de 30 jours
            if ($u->date_expiration !== null) {
                $joursRestants = (int) floor(
                    (strtotime($u->date_expiration) - time()) / 86400
                );
                if ($joursRestants <= 30) {
                    return 'orange';
                }
            }
            return 'vert';
        }

        return 'gris';
    }
}
