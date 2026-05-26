@extends('layouts.app')

@section('titre', 'Cartographie')
@section('sous_titre', 'Localisation des unités industrielles actives')

{{-- ══ RÈGLE 1 — CSS Leaflet injecté dans le <head> via @stack('styles') ═════
     Doit impérativement être dans le <head> ; charger le CSS Leaflet en fin de
     <body> empêche le calcul correct des dimensions des tuiles OSM.
════════════════════════════════════════════════════════════════════════════ --}}
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css"/>
<style>
    /* Tailwind reset : max-width:100% sur les <img> casse les tuiles Leaflet */
    .leaflet-container img {
        max-width: none !important;
        max-height: none !important;
    }
</style>
@endpush

@section('contenu')

{{-- ── En-tête ───────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Cartographie industrielle</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            <span id="compteur-unites" class="font-semibold" style="color:#1a237e;">
                {{ $totalUnites }}
            </span>
            unité(s) affichée(s)
        </p>
    </div>
    <a href="{{ route('admin.unites.index') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white"
       style="background-color:#1a237e;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1"/>
        </svg>
        Gérer les unités
    </a>
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Département</label>
            <select id="filtre-departement"
                    class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Tous les départements</option>
                @foreach ($departements as $dep)
                    <option value="{{ $dep }}">{{ $dep }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Secteur d'activité</label>
            <select id="filtre-secteur"
                    class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Tous les secteurs</option>
                @foreach ($secteurs as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Statut agrément</label>
            <select id="filtre-statut"
                    class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Tous les statuts</option>
                <option value="valide">Valide</option>
                <option value="expire">Expiré</option>
                <option value="suspendu">Suspendu</option>
                <option value="aucun">Sans agrément</option>
            </select>
        </div>
        <div class="flex items-end">
            <button id="btn-reinitialiser"
                    class="w-full px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                Réinitialiser
            </button>
        </div>
    </div>
</div>

{{-- ── Conteneur de la carte ─────────────────────────────────────────────────
     Pas de overflow-hidden : les popups Leaflet dépassent volontairement du div.
────────────────────────────────────────────────────────────────────────────── --}}
<div class="relative bg-white rounded-xl shadow-sm border border-gray-100">

    {{-- ══ RÈGLE 3 — div carte avec height, width et z-index explicites ════ --}}
    <div id="carte" style="height:550px; width:100%; z-index:1;"></div>

    {{-- Légende superposée (z-index supérieur aux tuiles Leaflet = 400+) ───── --}}
    <div style="position:absolute; bottom:30px; right:10px; z-index:999;
                background:#fff; border-radius:10px; padding:10px 14px;
                box-shadow:0 2px 8px rgba(0,0,0,0.15); border:1px solid #e2e8f0;
                font-family:sans-serif; font-size:12px; pointer-events:none;">
        <p style="font-weight:600; color:#374151; margin:0 0 7px;">Légende</p>
        @foreach ([
            ['#38a169', 'Agrément valide'],
            ['#F97316', 'Expire bientôt / Suspendu'],
            ['#e53e3e', 'Agrément expiré'],
            ['#718096', 'Sans agrément'],
        ] as [$couleur, $libelle])
        <div style="display:flex; align-items:center; gap:7px; margin-bottom:4px;">
            <span style="width:12px; height:12px; border-radius:50%;
                         background:{{ $couleur }}; border:2px solid #fff;
                         box-shadow:0 0 0 1px #ccc; display:inline-block; flex-shrink:0;"></span>
            <span style="color:#4b5563;">{{ $libelle }}</span>
        </div>
        @endforeach
    </div>

    {{-- Indicateur de chargement ────────────────────────────────────────────── --}}
    <div id="chargement"
         style="display:none; position:absolute; inset:0; z-index:500;
                align-items:center; justify-content:center;
                background:rgba(255,255,255,0.65); border-radius:12px;">
        <span style="color:#1a237e; font-size:13px; font-weight:500;">
            Chargement des unités…
        </span>
    </div>

</div>

@endsection

{{-- ══ RÈGLE 2 — JS Leaflet injecté avant </body> via @stack('scripts') ═════
     La balise <script src> est en premier dans ce bloc pour que la variable
     globale "L" soit disponible quand le script d'initialisation s'exécute.
════════════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>

{{-- ══ RÈGLE 4 — initialisation dans DOMContentLoaded ════════════════════════
     "loading" = la page n'est pas encore parsée (cas rare ici car @push place
     ce script en fin de <body>). Le else garantit l'exécution si l'événement
     a déjà été émis au moment où ce script est évalué.
════════════════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    // ── Données PHP passées au JS ────────────────────────────────────────────
    const URL_DONNEES = "{{ route('admin.cartographie.donnees') }}";

    // ── Couleurs selon le statut de l'agrément ───────────────────────────────
    const COULEURS = {
        vert:   '#38a169',
        orange: '#F97316',
        rouge:  '#e53e3e',
        gris:   '#718096',
    };

    // ── Fabrique d'icônes circulaires CSS (sans image externe) ───────────────
    function creerIcone(couleur) {
        const hex = COULEURS[couleur] ?? COULEURS.gris;
        return L.divIcon({
            className: '',
            html: '<div style="background:' + hex + ';width:14px;height:14px;'
                + 'border-radius:50%;border:2px solid #fff;'
                + 'box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>',
            iconSize:    [14, 14],
            iconAnchor:  [7, 7],
            popupAnchor: [0, -10],
        });
    }

    // ── HTML du popup ────────────────────────────────────────────────────────
    function popup(u) {
        var badges = {
            valide:   'background:#c6f6d5;color:#276749;',
            expire:   'background:#fed7d7;color:#c53030;',
            suspendu: 'background:#fefcbf;color:#744210;',
            aucun:    'background:#e2e8f0;color:#4a5568;',
        };
        var style   = badges[u.statut_agrement] || badges.aucun;
        var libelle = { valide:'Valide', expire:'Expiré', suspendu:'Suspendu', aucun:'Sans agrément' }[u.statut_agrement] || '—';
        var exp = u.date_expiration
            ? '<tr><td style="color:#718096;padding:2px 8px 2px 0;">Expiration</td>'
              + '<td><strong>' + u.date_expiration + '</strong></td></tr>'
            : '';
        return '<div style="font-family:sans-serif;font-size:13px;min-width:220px;">'
            + '<p style="font-weight:700;font-size:14px;margin:0 0 8px;color:#1a237e;line-height:1.3;">' + u.nom + '</p>'
            + '<table style="border-collapse:collapse;width:100%;">'
            + '<tr><td style="color:#718096;padding:2px 8px 2px 0;">Secteur</td><td><strong>' + u.secteur + '</strong></td></tr>'
            + '<tr><td style="color:#718096;padding:2px 8px 2px 0;">Département</td><td><strong>' + u.departement + '</strong></td></tr>'
            + '<tr><td style="color:#718096;padding:2px 8px 2px 0;">Commune</td><td><strong>' + (u.commune || '—') + '</strong></td></tr>'
            + '<tr><td style="color:#718096;padding:2px 8px 2px 0;">N° agrément</td><td><strong>' + u.numero_agrement + '</strong></td></tr>'
            + '<tr><td style="color:#718096;padding:2px 8px 2px 0;">Statut</td>'
            + '<td><span style="' + style + 'padding:1px 7px;border-radius:3px;font-size:11px;font-weight:600;">' + libelle + '</span></td></tr>'
            + exp
            + '</table></div>';
    }

    // ── Fonction principale : création de la carte et chargement des données ─
    function initialiserCarte() {
        // Initialisation centrée sur le Bénin (RÈGLE 3)
        var carte = L.map('carte', {
            center: [9.3077, 2.3158],
            zoom: 7,
        });

        // Tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(carte);

        var couche = L.layerGroup().addTo(carte);

        // ── Chargement des marqueurs ─────────────────────────────────────────
        function charger() {
            var spinner = document.getElementById('chargement');
            spinner.style.display = 'flex';

            var params = new URLSearchParams();
            var dept   = document.getElementById('filtre-departement').value;
            var sect   = document.getElementById('filtre-secteur').value;
            var statut = document.getElementById('filtre-statut').value;
            if (dept)   params.set('departement',     dept);
            if (sect)   params.set('secteur',         sect);
            if (statut) params.set('statut_agrement', statut);

            fetch(URL_DONNEES + '?' + params.toString())
                .then(function (r) {
                    if (!r.ok) throw new Error('HTTP ' + r.status);
                    return r.json();
                })
                .then(function (unites) {
                    couche.clearLayers();
                    unites.forEach(function (u) {
                        var m = L.marker([u.lat, u.lng], { icon: creerIcone(u.couleur) });
                        m.bindPopup(popup(u), { maxWidth: 280 });
                        couche.addLayer(m);
                    });
                    document.getElementById('compteur-unites').textContent = unites.length;
                })
                .catch(function (e) { console.error('Cartographie :', e); })
                .finally(function () { spinner.style.display = 'none'; });
        }

        // Filtres : recharge à chaque changement
        ['filtre-departement', 'filtre-secteur', 'filtre-statut'].forEach(function (id) {
            document.getElementById(id).addEventListener('change', charger);
        });

        document.getElementById('btn-reinitialiser').addEventListener('click', function () {
            document.getElementById('filtre-departement').value = '';
            document.getElementById('filtre-secteur').value     = '';
            document.getElementById('filtre-statut').value      = '';
            charger();
        });

        // Chargement initial
        charger();

        // Force le recalcul des dimensions (utile dans les layouts avec scroll)
        setTimeout(function () { carte.invalidateSize(); }, 150);
    }

    // ── Démarrage selon l'état du DOM ────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiserCarte);
    } else {
        // DOMContentLoaded déjà émis — appel direct (cas normal en fin de <body>)
        initialiserCarte();
    }
}());
</script>
@endpush
