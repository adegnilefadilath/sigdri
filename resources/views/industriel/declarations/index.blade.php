@extends('layouts.industriel')

@section('titre', 'Mes déclarations')
@section('sous_titre', 'Historique de vos déclarations de production')

@section('contenu')

@php
    // Noms des mois pour l'affichage
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    $badgeCss = fn (string $s) => match ($s) {
        'brouillon'   => 'bg-gray-100 text-gray-500',
        'soumise'     => 'bg-blue-100 text-blue-700',
        'en_revision' => 'bg-yellow-100 text-yellow-700',
        'validee'     => 'bg-green-100 text-green-700',
        'rejetee'     => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-500',
    };
    $badgeTxt = fn (string $s) => match ($s) {
        'brouillon'   => 'Brouillon',
        'soumise'     => 'Soumise',
        'en_revision' => 'En révision',
        'validee'     => 'Validée',
        'rejetee'     => 'Rejetée',
        default       => ucfirst($s),
    };
@endphp

{{-- ── Bouton nouvelle déclaration — empilé sur mobile, aligné sur sm+ ── --}}
<div class="mb-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
    <p class="text-sm text-gray-500">
        Vous pouvez soumettre une déclaration pour n'importe quel mois tant que votre agrément est valide.
    </p>
    {{-- Pleine largeur sur mobile, auto sur sm+ --}}
    <a href="{{ route('industriel.declarations.create') }}"
       class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-bold text-white transition-all hover:opacity-90 sm:shrink-0"
       style="background-color:#F97316;">
        <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle déclaration
    </a>
</div>

{{-- ── Historique ───────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-800">Historique ({{ $declarations->total() }})</h3>
    </div>

    @if ($declarations->isEmpty())
        <div class="py-16 flex flex-col items-center text-center">
            {{-- Icône état vide : 24px max --}}
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-3"
                 style="background: rgba(26,35,126,0.07);">
                <svg width="20" height="20" style="flex-shrink:0" class="text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-gray-600">Aucune déclaration</p>
            <p class="text-xs text-gray-400 mt-1">Vos déclarations apparaîtront ici.</p>
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach ($declarations as $decl)
            <div class="flex items-center gap-4 px-5 py-4 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="font-mono text-sm font-semibold text-gray-700">{{ $decl->numero_declaration }}</p>
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold {{ $badgeCss($decl->statut) }}">
                            {{ $badgeTxt($decl->statut) }}
                        </span>
                        @if ($decl->statut === 'rejetee')
                            <span class="text-xs text-red-500 font-semibold">— Correction requise</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $nomsM[$decl->mois] ?? '?' }} {{ $decl->annee }}
                        @if ($decl->date_soumission)
                            — soumise le {{ \Carbon\Carbon::parse($decl->date_soumission)->format('d/m/Y') }}
                        @endif
                    </p>
                    @if ($decl->chiffre_affaires_total > 0)
                        <p class="text-xs text-gray-400 mt-0.5">
                            CA : {{ number_format($decl->chiffre_affaires_total, 0, ',', ' ') }} FCFA
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if ($decl->statut === 'rejetee')
                        <a href="{{ route('industriel.declarations.edit', $decl->id) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-bold border transition-all hover:opacity-90 text-white"
                           style="background-color:#F97316; border-color:#F97316;">
                            Corriger
                        </a>
                    @endif
                    <a href="{{ route('industriel.declarations.show', $decl->id) }}"
                       class="text-xs font-semibold hover:underline" style="color:#1a237e;">
                        Voir →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @if ($declarations->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $declarations->links() }}</div>
        @endif
    @endif
</div>

@endsection
