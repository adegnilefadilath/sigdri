@extends('layouts.industriel')

@section('titre', 'Nouvelle déclaration')
@section('sous_titre', 'Saisie de la déclaration de production mensuelle')

@section('contenu')

@php
    // Noms des mois pour le sélecteur
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    // Classe CSS commune pour les inputs de tableau
    $tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white
           focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
@endphp

@if ($errors->any())
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                <input type="text" name="produits[0][designation]" placeholder="Nom du produit *"
                                       class="{{ $tc }}">
                            </td>
                            <td class="px-4 py-2.5">
                                <input type="text" name="produits[0][unite_mesure]" placeholder="tonne, litre…"
                                       class="{{ $tc }}">
                            </td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_produite]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_vendue_local]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][quantite_exportee]" min="0" step="0.001" placeholder="0" class="{{ $tc }} text-right"></td>
                            <td class="px-4 py-2.5"><input type="number" name="produits[0][valeur_fcfa]" min="0" step="1" placeholder="0" class="{{ $tc }} text-right" oninput="majTotalCA()"></td>
                            <td class="px-4 py-2.5 text-center">
                                <button type="button" onclick="supprimerLigne(this, 'corps-produits')"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
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
                        <td class="px-4 py-2.5"><input type="text" name="matieres[0][designation]" placeholder="ex : Farine de maïs" class="{{ $tc }}"></td>
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
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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

    {{-- ── Boutons d'action ──────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <button type="submit" name="action" value="soumettre"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white
                       shadow-sm transition-all hover:opacity-90"
                style="background-color:#F97316;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Soumettre la déclaration
        </button>
        <button type="submit" name="action" value="brouillon"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold
                       border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Enregistrer brouillon
        </button>
        <a href="{{ route('industriel.declarations.index') }}"
           class="px-5 py-2.5 rounded-xl text-sm text-gray-400 hover:text-gray-600 transition-colors">
            Annuler
        </a>
    </div>

</form>

@push('scripts')
<script>
    // Compteurs d'index pour les nouvelles lignes dynamiques
    let idxProduit = {{ $produits->isNotEmpty() ? $produits->count() : 1 }};
    let idxMatiere = 1;

    // ── Ajout d'une ligne produit ─────────────────────────────────────────────
    function ajouterProduit() {
        const n  = idxProduit++;
        const tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-2.5"><input type="text" name="produits[${n}][designation]" placeholder="Nom du produit *" class="${tc}"></td>
            <td class="px-4 py-2.5"><input type="text" name="produits[${n}][unite_mesure]" placeholder="tonne, litre…" class="${tc}"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_produite]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_vendue_local]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][quantite_exportee]" min="0" step="0.001" placeholder="0" class="${tc} text-right"></td>
            <td class="px-4 py-2.5"><input type="number" name="produits[${n}][valeur_fcfa]" min="0" step="1" placeholder="0" class="${tc} text-right" oninput="majTotalCA()"></td>
            <td class="px-4 py-2.5 text-center">
                <button type="button" onclick="supprimerLigne(this, 'corps-produits')"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </td>`;
        document.getElementById('corps-produits').appendChild(tr);
        tr.querySelector('input[type="text"]').focus();
    }

    // ── Ajout d'une ligne matière première ────────────────────────────────────
    function ajouterMatiere() {
        const n  = idxMatiere++;
        const tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-2.5"><input type="text" name="matieres[${n}][designation]" placeholder="ex : Farine de maïs" class="${tc}"></td>
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
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
</script>
@endpush

@endsection
