@extends('layouts.industriel')

@section('titre', $d->numero_declaration)
@section('sous_titre', 'Détail de votre déclaration — lecture seule')

@section('contenu')

@php
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $libelleMois = ($nomsM[$d->mois] ?? '?') . ' ' . $d->annee;

    $badgeCss = match ($d->statut) {
        'brouillon'   => 'bg-gray-100 text-gray-500',
        'soumise'     => 'bg-blue-100 text-blue-700',
        'en_revision' => 'bg-yellow-100 text-yellow-700',
        'validee'     => 'bg-green-100 text-green-700',
        'rejetee'     => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-500',
    };
    $badgeTxt = match ($d->statut) {
        'brouillon'   => 'Brouillon',   'soumise'   => 'Soumise',
        'en_revision' => 'En révision', 'validee'   => 'Validée',
        'rejetee'     => 'Rejetée',     default     => ucfirst($d->statut),
    };
@endphp

{{-- ── Bannière rejet ────────────────────────────────────────────────────── --}}
@if ($d->statut === 'rejetee')
<div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <svg width="20" height="20" style="flex-shrink:0" class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <div class="flex-1">
        <p class="font-bold">Déclaration rejetée — correction requise</p>
        @if ($d->motif_rejet)
            <p class="text-xs mt-0.5">{{ $d->motif_rejet }}</p>
        @endif
    </div>
    <a href="{{ route('industriel.declarations.edit', $d->id) }}"
       class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:opacity-90"
       style="background-color:#F97316;">
        Corriger
    </a>
</div>
@endif

{{-- ── Bannière validation ───────────────────────────────────────────────── --}}
@if ($d->statut === 'validee')
<div class="mb-5 flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
    <svg width="20" height="20" style="flex-shrink:0" class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p>
        Déclaration validée le {{ \Carbon\Carbon::parse($d->date_validation)->format('d/m/Y') }}.
    </p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ── Colonne principale ──────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- En-tête déclaration --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-5">
                <div>
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1">N° Déclaration</p>
                    <h2 class="font-mono text-xl font-black text-gray-900">{{ $d->numero_declaration }}</h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $libelleMois }}</p>
                </div>
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-bold {{ $badgeCss }}">
                    {{ $badgeTxt }}
                </span>
            </div>
            {{-- grid-cols-1 sur mobile (375px) pour éviter les colonnes trop étroites --}}
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Soumise le</dt>
                    <dd class="mt-1 font-medium text-gray-700">
                        {{ $d->date_soumission ? \Carbon\Carbon::parse($d->date_soumission)->format('d/m/Y à H:i') : '—' }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">CA Total</dt>
                    <dd class="mt-1 font-black text-gray-900">
                        {{ number_format($d->chiffre_affaires_total, 0, ',', ' ') }} FCFA
                    </dd>
                </div>
                @if ($d->observations)
                <div class="col-span-2">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Observations</dt>
                    <dd class="mt-1 text-gray-600 bg-gray-50 rounded-lg p-3 text-sm">{{ $d->observations }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Lignes de production --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Production — {{ $lignes->count() }} produit(s)</h3>
            </div>
            @if ($lignes->isEmpty())
                <div class="py-8 text-center text-gray-400 text-sm">Aucune ligne de production saisie.</div>
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
                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $l->designation }}
                                    <p class="text-xs font-normal text-gray-400">{{ $l->unite_mesure }}</p>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_produite, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_vendue_local, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($l->quantite_exportee, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-right tabular-nums font-bold text-gray-800">{{ number_format($l->valeur_fcfa, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Matières premières --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Matières premières — {{ $matieres->count() }} entrée(s)</h3>
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
                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $m->designation }}
                                    <p class="text-xs font-normal text-gray-400">{{ $m->unite_mesure }}</p>
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold
                                                 {{ $m->origine === 'locale' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $m->origine === 'locale' ? 'Locale' : 'Importée' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums text-gray-700">{{ number_format($m->quantite_consommee, 3, ',', ' ') }}</td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $m->fournisseur ?: '—' }}</td>
                                <td class="px-5 py-3 text-right tabular-nums font-bold text-gray-800">{{ number_format($m->valeur_fcfa, 0, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- ── Colonne latérale ────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        @if ($d->statut === 'rejetee')
        <a href="{{ route('industriel.declarations.edit', $d->id) }}"
           class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-bold text-white transition-all hover:opacity-90"
           style="background-color:#F97316;">
            <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Corriger et resoumettre
        </a>
        @endif

        {{-- Méta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-xs text-gray-400 space-y-2">
            <div class="flex justify-between">
                <span>Mois</span>
                <span class="font-medium text-gray-600">{{ $libelleMois }}</span>
            </div>
            <div class="flex justify-between">
                <span>Créée le</span>
                <span class="font-medium text-gray-600">{{ \Carbon\Carbon::parse($d->created_at)->format('d/m/Y') }}</span>
            </div>
            @if ($d->date_soumission)
            <div class="flex justify-between">
                <span>Soumise le</span>
                <span class="font-medium text-gray-600">{{ \Carbon\Carbon::parse($d->date_soumission)->format('d/m/Y') }}</span>
            </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <p class="text-xs text-gray-400 flex items-center gap-1.5">
                <svg width="20" height="20" style="flex-shrink:0" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Consultation uniquement.
            </p>
        </div>

        <a href="{{ route('industriel.declarations.index') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors px-1">
            <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Mes déclarations
        </a>

    </div>

</div>

@endsection
