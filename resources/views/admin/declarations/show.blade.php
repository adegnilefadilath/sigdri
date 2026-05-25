@extends('layouts.app')

@section('titre', $d->numero_declaration)
@section('sous_titre', 'Détail de la déclaration — ' . $d->denomination_unite)

@section('contenu')

@php
    $badgeCss = match ($d->statut) {
        'brouillon'   => 'bg-gray-100 text-gray-500',
        'soumise'     => 'bg-blue-100 text-blue-700',
        'en_revision' => 'bg-yellow-100 text-yellow-700',
        'validee'     => 'bg-green-100 text-green-700',
        'rejetee'     => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-500',
    };
    $badgeTxt = match ($d->statut) {
        'brouillon'   => 'Brouillon',
        'soumise'     => 'Soumise',
        'en_revision' => 'En révision',
        'validee'     => 'Validée',
        'rejetee'     => 'Rejetée',
        default       => ucfirst($d->statut),
    };

    // Libellé "Mois AAAA" du mois déclaré
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $libelleMois = ($nomsM[$d->mois] ?? '?') . ' ' . $d->annee;

    // Totaux calculés depuis les lignes
    $totalValeur    = $lignes->sum('valeur_fcfa');
    $totalMatValeur = $matieres->sum('valeur_fcfa');
@endphp

{{-- ── Bannière rejet ────────────────────────────────────────────────────── --}}
@if ($d->statut === 'rejetee' && $d->motif_rejet)
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div>
        <p class="font-bold">Déclaration rejetée</p>
        <p class="mt-0.5 text-xs">{{ $d->motif_rejet }}</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Colonne principale ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Fiche déclaration --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">Numéro de déclaration</p>
                    <h2 class="font-mono text-xl font-black text-gray-900">{{ $d->numero_declaration }}</h2>
                </div>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold {{ $badgeCss }}">
                    {{ $badgeTxt }}
                </span>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Unité industrielle</dt>
                    <dd class="mt-1">
                        <a href="{{ route('admin.unites.show', $d->unite_industrielle_id) }}"
                           class="font-semibold hover:underline" style="color:#1a237e;">
                            {{ $d->denomination_unite }}
                        </a>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $d->commune_unite }}, {{ $d->departement_unite }}</p>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Mois déclaré</dt>
                    <dd class="mt-1 font-semibold text-gray-800">{{ $libelleMois }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Déclarant</dt>
                    <dd class="mt-1 font-medium text-gray-700">{{ trim($d->declarant_nom) ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date de soumission</dt>
                    <dd class="mt-1 font-medium text-gray-700">
                        {{ $d->date_soumission ? \Carbon\Carbon::parse($d->date_soumission)->format('d/m/Y à H:i') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">CA Total déclaré (FCFA)</dt>
                    <dd class="mt-1 font-black text-gray-900 text-base">
                        {{ number_format($d->chiffre_affaires_total, 0, ',', ' ') }} FCFA
                    </dd>
                </div>
                @if ($d->validateur_nom && trim($d->validateur_nom))
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        {{ $d->statut === 'validee' ? 'Validé par' : 'Traité par' }}
                    </dt>
                    <dd class="mt-1 font-medium text-gray-700">{{ trim($d->validateur_nom) }}</dd>
                </div>
                @endif
                @if ($d->observations)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Observations</dt>
                    <dd class="mt-1 text-gray-600 bg-gray-50 rounded-lg p-3 text-sm">{{ $d->observations }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Lignes de production --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800">
                    Production déclarée — {{ $lignes->count() }} produit(s)
                </h3>
                <span class="text-xs text-gray-400">
                    Valeur totale : <strong class="text-gray-700">{{ number_format($totalValeur, 0, ',', ' ') }} FCFA</strong>
                </span>
            </div>
            @if ($lignes->isEmpty())
                <div class="py-8 text-center text-gray-400 text-sm">Aucune ligne de production.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                <th class="text-left px-5 py-3">Produit</th>
                                <th class="text-right px-5 py-3">Qté produite</th>
                                <th class="text-right px-5 py-3">Ventes locales</th>
                                <th class="text-right px-5 py-3">Exportations</th>
                                <th class="text-right px-5 py-3">Valeur (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($lignes as $l)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-gray-800">{{ $l->designation }}</p>
                                    <p class="text-xs text-gray-400">{{ $l->unite_mesure }}
                                        @if ($l->code_produit) — {{ $l->code_produit }} @endif
                                    </p>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_produite, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_vendue_local, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_exportee, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums font-semibold text-gray-800">{{ number_format($l->valeur_fcfa, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Matières premières --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800">
                    Matières premières — {{ $matieres->count() }} entrée(s)
                </h3>
                <span class="text-xs text-gray-400">
                    Valeur intrants : <strong class="text-gray-700">{{ number_format($totalMatValeur, 0, ',', ' ') }} FCFA</strong>
                </span>
            </div>
            @if ($matieres->isEmpty())
                <div class="py-8 text-center text-gray-400 text-sm">Aucune matière première déclarée.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                <th class="text-left px-5 py-3">Désignation</th>
                                <th class="text-center px-5 py-3">Origine</th>
                                <th class="text-right px-5 py-3">Qté consommée</th>
                                <th class="text-left px-5 py-3">Fournisseur</th>
                                <th class="text-right px-5 py-3">Valeur (FCFA)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($matieres as $m)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-semibold text-gray-800">{{ $m->designation }}</p>
                                    <p class="text-xs text-gray-400">{{ $m->unite_mesure }}</p>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                                                 {{ $m->origine === 'locale' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $m->origine === 'locale' ? 'Locale' : 'Importée' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($m->quantite_consommee, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $m->fournisseur ?: '—' }}</td>
                                <td class="px-5 py-3 text-right tabular-nums font-semibold text-gray-800">{{ number_format($m->valeur_fcfa, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Formulaire de rejet (visible si soumise ou en_revision) --}}
        @if (in_array($d->statut, ['soumise', 'en_revision']))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Rejeter cette déclaration
            </h3>
            <div id="form-rejet" class="hidden">
                <form method="POST" action="{{ route('admin.declarations.rejeter', $d->id) }}">
                    @csrf
                    @error('motif_rejet')
                        <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
                    @enderror
                    <textarea name="motif_rejet" rows="3"
                              placeholder="Motif de rejet (obligatoire, minimum 10 caractères)…"
                              class="w-full px-4 py-2.5 text-sm rounded-lg border resize-none mb-3
                                     {{ $errors->has('motif_rejet') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                     focus:outline-none focus:ring-2 focus:ring-red-400 focus:border-transparent">{{ old('motif_rejet') }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-bold rounded-lg text-white bg-red-500 hover:bg-red-600 transition-colors">
                            Confirmer le rejet
                        </button>
                        <button type="button" onclick="toggleRejet()"
                                class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
            <div id="btn-rejet">
                <button type="button" onclick="toggleRejet()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-red-300 text-red-600 bg-red-50 hover:bg-red-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Saisir le motif et rejeter
                </button>
            </div>
        </div>
        @endif

    </div>{{-- /col principale --}}

    {{-- ── Colonne actions ─────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Actions</h3>
            <div class="space-y-2">
                @if (in_array($d->statut, ['soumise', 'en_revision']))
                <form method="POST" action="{{ route('admin.declarations.valider', $d->id) }}"
                      onsubmit="return confirm('Valider la déclaration {{ $d->numero_declaration }} ?')">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
                            style="background-color:#059669;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Valider
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Méta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-xs text-gray-400 space-y-2">
            <div class="flex justify-between">
                <span>Mois déclaré</span>
                <span class="font-medium text-gray-600">{{ $libelleMois }}</span>
            </div>
            <div class="flex justify-between">
                <span>Secteur</span>
                <span class="font-medium text-gray-600">{{ $d->secteur_activite }}</span>
            </div>
            <div class="flex justify-between">
                <span>Créée le</span>
                <span class="font-medium text-gray-600">{{ \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') }}</span>
            </div>
            @if ($d->date_validation)
            <div class="flex justify-between">
                <span>{{ $d->statut === 'validee' ? 'Validée le' : 'Traitée le' }}</span>
                <span class="font-medium text-gray-600">{{ \Carbon\Carbon::parse($d->date_validation)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>

        <a href="{{ route('admin.declarations.index') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors px-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste
        </a>

    </div>{{-- /col actions --}}

</div>

@push('scripts')
<script>
    function toggleRejet() {
        document.getElementById('form-rejet').classList.toggle('hidden');
        document.getElementById('btn-rejet').classList.toggle('hidden');
    }
    @if ($errors->has('motif_rejet'))
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('form-rejet').classList.remove('hidden');
            document.getElementById('btn-rejet').classList.add('hidden');
        });
    @endif
</script>
@endpush

@endsection
