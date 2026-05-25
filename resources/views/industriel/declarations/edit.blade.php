@extends('layouts.industriel')

@section('titre', 'Corriger la déclaration')
@section('sous_titre', $d->numero_declaration . ' — Re-soumission après correction')

@section('contenu')

@php
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $libelleMois = ($nomsM[$d->mois] ?? '?') . ' ' . $d->annee;

    $tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';
@endphp

{{-- ── Bandeau motif de rejet ───────────────────────────────────────────── --}}
@if ($d->motif_rejet)
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="font-bold">Motif de rejet</p>
        <p class="text-xs mt-0.5">{{ $d->motif_rejet }}</p>
    </div>
</div>
@endif

{{-- ── Bandeau mois déclaré ──────────────────────────────────────────────── --}}
<div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
     style="background:#eff3ff; border-color:#c7d2fe; color:#3730a3;">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p>Mois déclaré : <strong>{{ $libelleMois }}</strong></p>
</div>

@if ($errors->any())
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <ul class="list-disc list-inside space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('industriel.declarations.update', $d->id) }}" id="form-correction">
    @csrf
    @method('PUT')

    {{-- ── Tableau de production ────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-800">Production — {{ $libelleMois }}</h2>
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
                        <th class="text-left px-4 py-3 min-w-[180px]">Désignation *</th>
                        <th class="text-left px-4 py-3 w-28">Unité</th>
                        <th class="text-right px-4 py-3 w-32">Qté produite</th>
                        <th class="text-right px-4 py-3 w-32">Ventes locales</th>
                        <th class="text-right px-4 py-3 w-32">Exportations</th>
                        <th class="text-right px-4 py-3 w-36">Valeur (FCFA)</th>
                        <th class="px-4 py-3 w-10"></th>
                    </tr>
                </thead>
                <tbody id="corps-produits">
                    @foreach ($lignes as $i => $l)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2.5">
                            <input type="hidden" name="produits[{{ $i }}][produit_id]" value="{{ $l->produit_id }}">
                            <input type="hidden" name="produits[{{ $i }}][designation]" value="{{ $l->designation }}">
                            <input type="text" value="{{ $l->designation }}" readonly
                                   class="w-full px-2.5 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-600 cursor-default">
                        </td>
                        <td class="px-4 py-2.5">
                            <input type="hidden" name="produits[{{ $i }}][unite_mesure]" value="{{ $l->unite_mesure }}">
                            <input type="text" value="{{ $l->unite_mesure }}" readonly
                                   class="w-full px-2.5 py-2 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-600 cursor-default">
                        </td>
                        <td class="px-4 py-2.5"><input type="number" name="produits[{{ $i }}][quantite_produite]" value="{{ $l->quantite_produite }}" min="0" step="0.001" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="number" name="produits[{{ $i }}][quantite_vendue_local]" value="{{ $l->quantite_vendue_local }}" min="0" step="0.001" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="number" name="produits[{{ $i }}][quantite_exportee]" value="{{ $l->quantite_exportee }}" min="0" step="0.001" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="number" name="produits[{{ $i }}][valeur_fcfa]" value="{{ $l->valeur_fcfa }}" min="0" step="1" class="{{ $tc }} text-right" oninput="majTotalCA()"></td>
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
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Matières premières ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-800">Matières premières</h2>
            <button type="button" onclick="ajouterMatiere()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold border transition-colors hover:bg-orange-50"
                    style="border-color:#F97316; color:#F97316;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter
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
                    @foreach ($matieres as $i => $m)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2.5"><input type="text" name="matieres[{{ $i }}][designation]" value="{{ $m->designation }}" class="{{ $tc }}"></td>
                        <td class="px-4 py-2.5">
                            <select name="matieres[{{ $i }}][origine]" class="{{ $tc }}">
                                <option value="locale"   {{ $m->origine === 'locale'   ? 'selected' : '' }}>Locale</option>
                                <option value="importee" {{ $m->origine === 'importee' ? 'selected' : '' }}>Importée</option>
                            </select>
                        </td>
                        <td class="px-4 py-2.5"><input type="text" name="matieres[{{ $i }}][unite_mesure]" value="{{ $m->unite_mesure }}" class="{{ $tc }}"></td>
                        <td class="px-4 py-2.5"><input type="number" name="matieres[{{ $i }}][quantite_consommee]" value="{{ $m->quantite_consommee }}" min="0" step="0.001" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="number" name="matieres[{{ $i }}][valeur_fcfa]" value="{{ $m->valeur_fcfa }}" min="0" step="1" class="{{ $tc }} text-right"></td>
                        <td class="px-4 py-2.5"><input type="text" name="matieres[{{ $i }}][fournisseur]" value="{{ $m->fournisseur }}" placeholder="Optionnel" class="{{ $tc }}"></td>
                        <td class="px-4 py-2.5 text-center">
                            <button type="button" onclick="supprimerLigne(this, 'corps-matieres')"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors mx-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Informations complémentaires ─────────────────────────────────── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-4">Informations complémentaires</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Chiffre d'affaires total (FCFA)
                </label>
                <div class="relative">
                    <input type="number" name="chiffre_affaires_total" id="ca-total"
                           value="{{ old('chiffre_affaires_total', $d->chiffre_affaires_total) }}"
                           min="0" step="1"
                           class="w-full px-4 py-2.5 pr-14 text-sm rounded-lg border border-gray-300 bg-gray-50
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">FCFA</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Observations</label>
                <textarea name="observations" rows="3"
                          class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 resize-none
                                 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">{{ old('observations', $d->observations) }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── Actions ──────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3">
        <button type="submit" name="action" value="soumettre"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white shadow-sm transition-all hover:opacity-90"
                style="background-color:#F97316;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Resoumettre la déclaration
        </button>
        <button type="submit" name="action" value="brouillon"
                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
            Enregistrer brouillon
        </button>
        <a href="{{ route('industriel.declarations.show', $d->id) }}"
           class="px-5 py-2.5 rounded-xl text-sm text-gray-400 hover:text-gray-600 transition-colors">
            Annuler
        </a>
    </div>

</form>

@push('scripts')
<script>
    let idxProduit = {{ $lignes->count() }};
    let idxMatiere = {{ $matieres->count() }};
    const tc = 'w-full px-2.5 py-2 text-sm rounded-lg border border-gray-300 bg-white focus:outline-none focus:ring-1 focus:ring-[#1a237e] focus:border-[#1a237e] transition-colors';

    function ajouterProduit() {
        const n  = idxProduit++;
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

    function ajouterMatiere() {
        const n  = idxMatiere++;
        const tr = document.createElement('tr');
        tr.className = 'border-t border-gray-100';
        tr.innerHTML = `
            <td class="px-4 py-2.5"><input type="text" name="matieres[${n}][designation]" placeholder="ex : Farine de maïs" class="${tc}"></td>
            <td class="px-4 py-2.5"><select name="matieres[${n}][origine]" class="${tc}"><option value="locale">Locale</option><option value="importee">Importée</option></select></td>
            <td class="px-4 py-2.5"><input type="text" name="matieres[${n}][unite_mesure]" placeholder="kg…" class="${tc}"></td>
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

    function supprimerLigne(btn, tbodyId) {
        const tbody = document.getElementById(tbodyId);
        if (tbody.querySelectorAll('tr').length > 1) {
            btn.closest('tr').remove();
            majTotalCA();
        }
    }

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
