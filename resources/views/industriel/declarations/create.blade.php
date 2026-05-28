@extends('layouts.industriel')

@section('titre', 'Nouvelle déclaration')
@section('sous_titre', 'Saisie de la déclaration de production mensuelle')

{{-- Styles spécifiques au formulaire de déclaration --}}
@push('styles')
<style>
    /* Overlay modal scanner */
    #modal-scanner { backdrop-filter: blur(4px); }
</style>
@endpush

@section('contenu')

@php
    // Noms des mois pour le sélecteur
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    // Classe CSS commune pour les inputs de tableau
    $tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white
           focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
@endphp

{{-- Erreur doublon (déclaration déjà soumise pour ce mois) — bloc proéminent --}}
@if ($errors->has('doublon'))
<div class="mb-5 flex items-start gap-3 px-5 py-4 bg-red-50 border-2 border-red-300 rounded-xl text-sm text-red-800">
    <svg width="20" height="20" style="flex-shrink:0" class="w-5 h-5 shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
    </svg>
    <div>
        <p class="font-semibold mb-0.5">Déclaration déjà existante</p>
        <p>{{ $errors->first('doublon') }}</p>
    </div>
</div>
@endif

{{-- Erreurs de validation des champs --}}
@if ($errors->hasAny(['mois', 'annee', 'chiffre_affaires_total', 'observations']))
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg width="20" height="20" style="flex-shrink:0" class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <ul class="list-disc list-inside space-y-0.5">
        @foreach ($errors->except('doublon') as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('industriel.declarations.store') }}" id="form-declaration">
    @csrf

    {{-- ════════════════════════════════════════════════════════════════
        SECTION 0 — Sélection du mois et de l'année
    ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
        <h2 class="text-sm font-bold text-gray-800 mb-4">Mois de la déclaration</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

            {{-- Mois --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Mois <span class="text-red-500">*</span>
                </label>
                <select name="mois"
                        class="w-full px-3 py-2.5 text-sm rounded-lg border
                               {{ $errors->has('mois') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                               focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                    @foreach ($nomsM as $num => $nom)
                        @if ($num > 0)
                        <option value="{{ $num }}" {{ old('mois', $moisDefaut) == $num ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Année --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Année <span class="text-red-500">*</span>
                </label>
                <input type="number" name="annee"
                       value="{{ old('annee', $anneeDefaut) }}"
                       min="2020" max="2100"
                       class="w-full px-3 py-2.5 text-sm rounded-lg border
                              {{ $errors->has('annee') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                              focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
            </div>

        </div>
        <p class="text-xs text-gray-400 mt-3">
            Une seule déclaration est autorisée par mois et par unité industrielle.
        </p>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
        SECTION 1 — Tableau de production par produit
    ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-800">Production du mois</h2>
            <button type="button" onclick="ajouterProduit()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border transition-colors hover:bg-orange-50"
                    style="border-color:#F97316; color:#F97316;">
                <svg width="20" height="20" style="flex-shrink:0" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un produit
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="table-produits">
                <thead class="bg-gray-50">
                    <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                        <th class="text-left px-4 py-3 min-w-[180px]">Désignation du produit *</th>
                        <th class="text-left px-4 py-3 w-28">Unité mesure</th>
                        <th class="text-right px-4 py-3 w-32">Qté produite</th>
                        <th class="text-right px-4 py-3 w-32">Ventes locales</th>
                        <th class="text-right px-4 py-3 w-32">Exportations</th>
                        <th class="text-right px-4 py-3 w-36">Valeur (FCFA)</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="corps-produits">
                    {{-- Produits existants du catalogue --}}
                    @if ($produits->isNotEmpty())
                        @foreach ($produits as $i => $prod)
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2.5">
                                <input type="hidden" name="produits[{{ $i }}][produit_id]" value="{{ $prod->id }}">
                                <input type="hidden" name="produits[{{ $i }}][designation]" value="{{ $prod->designation }}">
                                <input type="text" value="{{ $prod->designation }}" readonly
                                       class="w-full px-2.5 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-600 cursor-default">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="hidden" name="produits[{{ $i }}][unite_mesure]" value="{{ $prod->unite_mesure }}">
                                <input type="text" value="{{ $prod->unite_mesure }}" readonly
                                       class="w-full px-2.5 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-600 cursor-default">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" name="produits[{{ $i }}][quantite_produite]" min="0" step="0.001"
                                       placeholder="0" class="{{ $tc }} text-right">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" name="produits[{{ $i }}][quantite_vendue_local]" min="0" step="0.001"
                                       placeholder="0" class="{{ $tc }} text-right">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" name="produits[{{ $i }}][quantite_exportee]" min="0" step="0.001"
                                       placeholder="0" class="{{ $tc }} text-right">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="number" name="produits[{{ $i }}][valeur_fcfa]" min="0" step="1"
                                       placeholder="0" class="{{ $tc }} text-right"
                                       oninput="majTotalCA()">
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <button type="button" onclick="supprimerLigne(this, 'corps-produits')"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                                    <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        {{-- Aucun produit catalogue : une ligne vide par défaut --}}
                        <tr class="border-t border-gray-100">
                            <td class="px-4 py-2.5">
                                {{-- Champ désignation + bouton scanner ── --}}
                                <div class="flex gap-1.5">
                                    <input type="text" name="produits[0][designation]"
                                           id="produit-desig-0"
                                           placeholder="Nom du produit *"
                                           class="{{ $tc }} flex-1">
                                    {{-- Bouton Scanner orange — ouvre la caméra ZXing pour identifier le produit --}}
                                    <button type="button"
                                            onclick="SigdriScanner.lancerScan(document.getElementById('produit-desig-0'), document.getElementById('produit-um-0'))"
                                            title="Scanner le code-barres du produit"
                                            class="shrink-0 px-2 py-1 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                                            style="background-color:#F97316;"
                                            id="btn-scanner-p0">
                                        📷 Scanner
                                    </button>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="text" name="produits[0][unite_mesure]"
                                       id="produit-um-0"
                                       placeholder="tonne, litre…"
                                       class="{{ $tc }}">
                            </td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_produite]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_vendue_local]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_exportee]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][valeur_fcfa]" min="0" step="1" placeholder="0" class="{{ $tc }} text-right" oninput="majTotalCA()"></td>
                            <td class="px-4 py-2.5 text-center">
                                <button type="button" onclick="supprimerLigne(this, 'corps-produits')"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                                    <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
        SECTION 2 — Matières premières consommées
    ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-800">Matières premières consommées</h2>
            <button type="button" onclick="ajouterMatiere()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border transition-colors hover:bg-orange-50"
                    style="border-color:#F97316; color:#F97316;">
                <svg width="20" height="20" style="flex-shrink:0" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter une matière
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="table-matieres">
                <thead class="bg-gray-50">
                    <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                        <th class="text-left px-4 py-3 min-w-[160px]">Désignation *</th>
                        <th class="text-center px-4 py-3 w-28">Origine</th>
                        <th class="text-left px-4 py-3 w-24">Unité</th>
                        <th class="text-right px-4 py-3 w-32">Qté consommée</th>
                        <th class="text-right px-4 py-3 w-36">Valeur (FCFA)</th>
                        <th class="text-left px-4 py-3 min-w-[140px]">Fournisseur</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="corps-matieres">
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2.5">
                            <div class="flex gap-1.5">
                                <input type="text" name="matieres[0][designation]"
                                       id="matiere-desig-0"
                                       placeholder="ex : Farine de maïs"
                                       class="{{ $tc }} flex-1">
                                {{-- Bouton Scanner orange — ouvre la caméra ZXing pour identifier la matière --}}
                                <button type="button"
                                        onclick="SigdriScanner.lancerScan(document.getElementById('matiere-desig-0'), null)"
                                        title="Scanner le code-barres"
                                        class="shrink-0 px-2 py-1 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                                        style="background-color:#F97316;">
                                    📷 Scanner
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-2.5">
                            <select name="matieres[0][origine]" class="{{ $tc }}">
                                <option value="locale">Locale</option>
                                <option value="importee">Importée</option>
                            </select>
                        </td>
                        <td class="px-4 py-2.5"><input type="text" name="matieres[0][unite_mesure]" placeholder="kg, tonne…" class="{{ $tc }}"></td>
                        <td class="px-4 py-2.5"><input type="number" name="matieres[0][quantite_consommee]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="number" name="matieres[0][valeur_fcfa]" min="0" step="1" placeholder="0" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="text" name="matieres[0][fournisseur]" placeholder="Optionnel" class="{{ $tc }}"></td>
                        <td class="px-4 py-2.5 text-center">
                            <button type="button" onclick="supprimerLigne(this, 'corps-matieres')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                                <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
        SECTION 3 — Chiffre d'affaires et observations
    ════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-4">Informations complémentaires</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Chiffre d'affaires total (FCFA)
                </label>
                <div class="relative">
                    <input type="number" name="chiffre_affaires_total" id="ca-total"
                           value="{{ old('chiffre_affaires_total', 0) }}" min="0" step="1"
                           placeholder="Calculé automatiquement"
                           class="w-full px-4 py-2.5 pr-14 text-sm rounded-lg border border-gray-300 bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">FCFA</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Recalculé automatiquement d'après la valeur des produits.</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Observations <span class="font-normal text-gray-400 normal-case">(optionnel)</span>
                </label>
                <textarea name="observations" rows="3"
                          placeholder="Remarques, événements exceptionnels du mois…"
                          class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 resize-none
                                 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">{{ old('observations') }}</textarea>
            </div>

        </div>
    </div>

    {{-- ── Boutons d'action — empilés sur mobile, alignés sur sm+ ────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        {{-- Pleine largeur sur mobile --}}
        <button type="submit" name="action" value="soumettre"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 sm:py-2.5 rounded-xl text-sm font-bold text-white
                       shadow-sm transition-all hover:opacity-90"
                style="background-color:#F97316;">
            <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Soumettre la déclaration
        </button>
        <button type="submit" name="action" value="brouillon"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 sm:py-2.5 rounded-xl text-sm font-semibold
                       border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
            <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Enregistrer brouillon
        </button>
        <a href="{{ route('industriel.declarations.index') }}"
           class="w-full sm:w-auto text-center px-5 py-2.5 rounded-xl text-sm text-gray-400 hover:text-gray-600 transition-colors">
            Annuler
        </a>
    </div>

</form>

{{-- ════════════════════════════════════════════════════════════════════════
    MODAL SCANNER — 3 modes gérés par scanner.js :
      Mode A : BarcodeDetector auto       (Chrome/Edge/Safari iOS 17+)
      Mode B : caméra + saisie manuelle   (Safari iOS 14-16, Firefox)
      Mode C : saisie seule               (caméra refusée / indisponible)

    IMPORTANT — Positionnement 100 % en style= inline (pas de classes Tailwind
    pour position/dimension) afin d'éviter les bugs iOS Safari avec fixed+inset.
════════════════════════════════════════════════════════════════════════ --}}
{{-- display:none en inline garantit que le modal est invisible au chargement,
     indépendamment de Tailwind. ouvrirModal() / fermer() dans scanner.js
     gèrent la visibilité via style.display — ne pas retirer ce display:none. --}}
<div id="modal-scanner"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%;
            z-index:9999; background:rgba(0,0,0,0.95); overflow:hidden;">

    {{-- ── Bouton Fermer — haut droite, toujours visible ──────────────────── --}}
    <button type="button"
            onclick="SigdriScanner.fermer()"
            aria-label="Fermer le scanner"
            style="position:absolute; top:16px; right:16px; z-index:10002;
                   display:inline-flex; align-items:center; gap:8px;
                   padding:8px 16px; border-radius:12px; border:none; cursor:pointer;
                   background:rgba(255,255,255,0.18); color:#fff;
                   font-size:14px; font-weight:600;
                   -webkit-backdrop-filter:blur(6px); backdrop-filter:blur(6px);">
        <svg width="18" height="18" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        Fermer
    </button>

    {{-- ── Titre — haut gauche ─────────────────────────────────────────────── --}}
    <div style="position:absolute; top:18px; left:16px; z-index:10002;
                display:flex; align-items:center; gap:8px;">
        <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span style="color:#fff; font-size:14px; font-weight:700;">Scanner un code-barres</span>
    </div>

    {{-- ── Flux vidéo plein écran (Modes A et B) ───────────────────────────── --}}
    {{-- playsinline obligatoire sur iOS pour éviter le passage en plein écran natif --}}
    <video id="scanner-video"
           muted playsinline
           style="position:absolute; top:0; left:0; width:100%; height:100%;
                  object-fit:cover; display:none;">
    </video>

    {{-- Canvas caché — buffer pour capture de frames si besoin ── --}}
    <canvas id="scanner-canvas" style="display:none;"></canvas>

    {{-- ── Cadre de visée orange (Mode A uniquement) ───────────────────────── --}}
    <div id="scanner-viseur"
         style="position:absolute; top:0; left:0; width:100%; height:100%;
                display:none; align-items:center; justify-content:center;
                pointer-events:none; z-index:10000;">
        <div style="position:relative; width:240px; height:240px;">
            {{-- Coins --}}
            <div style="position:absolute; top:0; left:0; width:32px; height:32px;
                        border-top:4px solid #F97316; border-left:4px solid #F97316;
                        border-radius:4px 0 0 0;"></div>
            <div style="position:absolute; top:0; right:0; width:32px; height:32px;
                        border-top:4px solid #F97316; border-right:4px solid #F97316;
                        border-radius:0 4px 0 0;"></div>
            <div style="position:absolute; bottom:0; left:0; width:32px; height:32px;
                        border-bottom:4px solid #F97316; border-left:4px solid #F97316;
                        border-radius:0 0 0 4px;"></div>
            <div style="position:absolute; bottom:0; right:0; width:32px; height:32px;
                        border-bottom:4px solid #F97316; border-right:4px solid #F97316;
                        border-radius:0 0 4px 0;"></div>
            {{-- Ligne de scan animée --}}
            <div style="position:absolute; left:16px; right:16px; top:50%; height:2px;
                        transform:translateY(-50%); background:#F97316; opacity:0.9;"
                 class="animate-pulse"></div>
        </div>
    </div>

    {{-- ── Overlay saisie sur vidéo (Mode B — iOS 14-16, Firefox) ─────────── --}}
    {{-- font-size:16px sur l'input empêche le zoom automatique de Safari iOS ── --}}
    <div id="scanner-overlay-saisie"
         style="position:absolute; bottom:0; left:0; right:0; display:none;
                padding:20px 16px 40px; z-index:10001;
                background:linear-gradient(to top, rgba(0,0,0,0.96) 0%, rgba(0,0,0,0.65) 65%, transparent 100%);">
        <p style="color:rgba(255,255,255,0.85); font-size:13px; font-weight:500;
                  text-align:center; margin:0 0 10px;">
            📷 Cadrez le code-barres, puis saisissez-le :
        </p>
        <div style="display:flex; gap:8px; align-items:stretch;">
            <input type="text"
                   id="scanner-code-video"
                   autocomplete="off" autocorrect="off"
                   autocapitalize="none" spellcheck="false"
                   placeholder="Ex : 3017620422003"
                   style="flex:1; min-width:0; padding:14px 12px;
                          border-radius:12px; border:2px solid rgba(249,115,22,0.5);
                          background:#fff; color:#111; font-size:16px; font-weight:500;
                          outline:none; -webkit-appearance:none; appearance:none;">
            <button id="scanner-btn-video"
                    type="button"
                    style="flex-shrink:0; padding:14px 20px;
                           background:#F97316; color:#fff; border:none; cursor:pointer;
                           border-radius:12px; font-size:15px; font-weight:700;
                           white-space:nowrap;">
                OK
            </button>
        </div>
    </div>

    {{-- ── Barre de statut inférieure (Mode A + phase de recherche) ───────── --}}
    <div id="scanner-statut-bar"
         style="position:absolute; bottom:0; left:0; right:0; display:none;
                padding:16px 20px 32px; text-align:center;
                pointer-events:none; z-index:10001;
                background:linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 100%);">
        <p id="scanner-status"
           style="color:#fff; font-size:14px; font-weight:500; margin:0 0 4px;">
            Initialisation…
        </p>
        <p id="scanner-error"
           style="display:none; font-size:12px; color:#fca5a5; margin:0 0 4px;
                  padding:6px 12px; border-radius:8px;
                  background:rgba(220,38,38,0.30);"></p>
        <p style="color:rgba(255,255,255,0.4); font-size:11px; margin:0;">
            EAN-13 · QR Code · Code 128 · UPC
        </p>
    </div>

    {{-- ── Section solo — carte blanche centrée (Mode C) ──────────────────── --}}
    <div id="scanner-sec-solo"
         style="position:absolute; top:0; left:0; width:100%; height:100%;
                display:none; align-items:center; justify-content:center;
                padding:24px; box-sizing:border-box; z-index:10001;">

        <div style="background:#fff; border-radius:20px; padding:24px;
                    width:100%; max-width:380px;
                    box-shadow:0 20px 60px rgba(0,0,0,0.45);">

            {{-- Icône --}}
            <div style="text-align:center; margin-bottom:20px;">
                <div style="width:48px; height:48px; border-radius:14px;
                            background:rgba(249,115,22,0.10);
                            display:inline-flex; align-items:center; justify-content:center;
                            margin-bottom:12px;">
                    {{-- Icône loupe — neutre, convient à la saisie directe (iOS) et au fallback --}}
                    <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                {{-- Titre mis à jour dynamiquement par modeSolo() selon le contexte --}}
                <p id="scanner-solo-titre"
                   style="font-size:15px; font-weight:700; color:#1f2937; margin:0 0 6px;">
                    Saisir le code produit
                </p>
                {{-- Sous-titre mis à jour dynamiquement par modeSolo() --}}
                <p id="scanner-solo-sous" style="font-size:13px; color:#6b7280; margin:0;">
                    Entrez le code-barres affiché sur le produit.
                </p>
            </div>

            {{-- Champ de saisie — font-size 16px pour bloquer le zoom iOS ── --}}
            <label style="display:block; font-size:12px; font-weight:600;
                          color:#374151; text-transform:uppercase;
                          letter-spacing:0.05em; margin-bottom:8px;">
                Code-barres / référence
            </label>
            <input type="text"
                   id="scanner-code-solo"
                   autocomplete="off" autocorrect="off"
                   autocapitalize="none" spellcheck="false"
                   placeholder="Ex : 3017620422003"
                   style="display:block; width:100%; box-sizing:border-box;
                          padding:14px 12px; border-radius:12px;
                          border:1.5px solid #e5e7eb; background:#f9fafb;
                          color:#111; font-size:16px; font-weight:500;
                          outline:none; -webkit-appearance:none; appearance:none;">
            <p style="font-size:12px; color:#9ca3af; margin:6px 0 16px;">
                Appuyez sur Entrée ou cliquez Valider.
            </p>

            {{-- Boutons --}}
            <div style="display:flex; gap:10px;">
                <button id="scanner-btn-solo"
                        type="button"
                        style="flex:1; padding:14px; background:#F97316; color:#fff;
                               border:none; cursor:pointer; border-radius:12px;
                               font-size:15px; font-weight:700;">
                    Valider
                </button>
                <button type="button"
                        onclick="SigdriScanner.fermer()"
                        style="padding:14px 18px; border:1.5px solid #e5e7eb;
                               background:#fff; color:#6b7280; cursor:pointer;
                               border-radius:12px; font-size:14px; font-weight:500;">
                    Annuler
                </button>
            </div>

        </div>
    </div>

</div>

@push('scripts')
{{-- scanner.js utilise l'API native BarcodeDetector (aucun CDN requis) ── --}}
<script src="/js/scanner.js"></script>
<script>
    // Affichage/masquage du bouton scanner selon la disponibilité de la caméra
    document.addEventListener('DOMContentLoaded', function () {
        // Les boutons scanner sont visibles par défaut.
        // Si le navigateur ne supporte pas getUserMedia, on les masque.
        if (!navigator.mediaDevices?.getUserMedia) {
            document.querySelectorAll('[title="Scanner le code-barres du produit"], [title="Scanner le code-barres"]')
                .forEach((btn) => btn.classList.add('hidden'));
        }

    });

    // Compteurs d'index pour les nouvelles lignes dynamiques
    let idxProduit = {{ $produits->isNotEmpty() ? $produits->count() : 1 }};
    let idxMatiere = 1;

    // ── Ajout d'une ligne produit ─────────────────────────────────────────────
    function ajouterProduit() {
        const n      = idxProduit++;
        const tc     = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
        const idDesig = `produit-desig-${n}`;
        const idUm    = `produit-um-${n}`;
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-2.5">
                <div class="flex gap-1.5">
                    <input type="text" name="produits[${n}][designation]" id="${idDesig}" placeholder="Nom du produit *" class="${tc} flex-1">
                    <button type="button" title="Scanner le code-barres du produit"
                            onclick="SigdriScanner.lancerScan(document.getElementById('${idDesig}'), document.getElementById('${idUm}'))"
                            class="shrink-0 px-2 py-1 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                            style="background-color:#F97316;">📷 Scanner</button>
                </div>
            </td>
            <td class="px-4 py-2.5"><input type="text" name="produits[${n}][unite_mesure]" id="${idUm}" placeholder="tonne, litre…" class="${tc}"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_produite]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_vendue_local]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_exportee]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][valeur_fcfa]" min="0" step="1" placeholder="0" class="${tc} text-right" oninput="majTotalCA()"></td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" onclick="supprimerLigne(this, 'corps-produits')"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                    <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>`;
        document.getElementById('corps-produits').appendChild(tr);
        tr.querySelector('input[type="text"]').focus();
    }

    // ── Ajout d'une ligne matière première ────────────────────────────────────
    function ajouterMatiere() {
        const n      = idxMatiere++;
        const tc     = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
        const idDesig = `matiere-desig-${n}`;
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-2.5">
                <div class="flex gap-1.5">
                    <input type="text" name="matieres[${n}][designation]" id="${idDesig}" placeholder="ex : Farine de maïs" class="${tc} flex-1">
                    <button type="button" title="Scanner le code-barres"
                            onclick="SigdriScanner.lancerScan(document.getElementById('${idDesig}'), null)"
                            class="shrink-0 px-2 py-1 rounded-lg text-sm font-medium text-white transition-opacity hover:opacity-90"
                            style="background-color:#F97316;">📷 Scanner</button>
                </div>
            </td>
            <td class="px-4 py-2.5">
                <select name="matieres[${n}][origine]" class="${tc}">
                    <option value="locale">Locale</option>
                    <option value="importee">Importée</option>
                </select>
            </td>
            <td class="px-4 py-2.5"><input type="text" name="matieres[${n}][unite_mesure]" placeholder="kg, tonne…" class="${tc}"></td>
            <td class="px-4 py-2.5"><input type="number" name="matieres[${n}][quantite_consommee]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="matieres[${n}][valeur_fcfa]" min="0" step="1" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="text" name="matieres[${n}][fournisseur]" placeholder="Optionnel" class="${tc}"></td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" onclick="supprimerLigne(this, 'corps-matieres')"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                    <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>`;
        document.getElementById('corps-matieres').appendChild(tr);
        tr.querySelector('input[type="text"]').focus();
    }

    // ── Suppression d'une ligne (garde toujours au moins 1 ligne) ────────────
    function supprimerLigne(btn, tbodyId) {
        const tbody = document.getElementById(tbodyId);
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
            if (tbodyId === 'corps-produits') majTotalCA();
        }
    }

    // ── Calcul automatique du CA total depuis les valeurs produits ────────────
    function majTotalCA() {
        let total = 0;
        document.querySelectorAll('#corps-produits input[name$="[valeur_fcfa]"]').forEach(inp => {
            total += parseFloat(inp.value.replace(',', '.')) || 0;
        });
        document.getElementById('ca-total').value = Math.round(total);
    }

    /* ════════════════════════════════════════════════════════════════════════
       MODULE 9 — Interception du formulaire en mode hors-ligne
       Si le navigateur est offline au moment du submit :
         1. Sérialise le FormData
         2. Le stocke dans IndexedDB via SigdriOfflineDB
         3. Enregistre un Background Sync si disponible
         4. Affiche une confirmation à l'utilisateur
    ════════════════════════════════════════════════════════════════════════ */
    document.getElementById('form-declaration').addEventListener('submit', async function (event) {
        // Si connecté → soumission normale (pas d'interception)
        if (navigator.onLine) return;

        // Hors-ligne : on intercepte
        event.preventDefault();

        const formData = new FormData(this);

        try {
            const id = await SigdriOfflineDB.sauvegarder(formData);

            // Enregistrement du tag Background Sync (Chrome/Edge uniquement)
            if ('serviceWorker' in navigator && 'sync' in ServiceWorkerRegistration.prototype) {
                const swReg = await navigator.serviceWorker.ready;
                await swReg.sync.register('sync-declarations');
            }

            // Remplacement du formulaire par un message de confirmation
            const formEl = document.getElementById('form-declaration');
            // Icône de confirmation : 24px max pour le responsive
            formEl.innerHTML = `
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center">
                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-4"
                         style="background: rgba(249,115,22,0.1);">
                        <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="#F97316" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-800 mb-2">Déclaration sauvegardée hors-ligne</h3>
                    <p class="text-sm text-gray-500 mb-6">
                        Votre déclaration a été conservée localement (référence #${id}).<br>
                        Elle sera transmise automatiquement au serveur à la prochaine connexion internet.
                    </p>
                    <a href="{{ route('industriel.declarations.index') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white"
                       style="background-color: #F97316;">
                        Retour à mes déclarations
                    </a>
                </div>`;

        } catch (err) {
            console.error('[SIGDRI Offline] Erreur sauvegarde :', err);
            alert('Impossible de sauvegarder la déclaration hors-ligne. Erreur : ' + err.message);
        }
    });
</script>
@endpush

@endsection
