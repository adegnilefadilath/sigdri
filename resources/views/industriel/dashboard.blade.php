@extends('layouts.industriel')

@section('titre', 'Mon tableau de bord')
@section('sous_titre', 'Vue d\'ensemble de votre activité industrielle')

@section('contenu')

{{-- ═══════════════════════════════════════════════════════════════════════════
    CARTES STATISTIQUES — 4 cartes
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- Carte 1 : Déclarations soumises (bleu) --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full"
             style="background: rgba(255,255,255,0.08);"></div>
        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Mes déclarations
                </p>
                <p class="text-4xl font-black mt-1 leading-none">{{ $totalDeclarations }}</p>
                <p class="text-xs mt-2 opacity-75">
                    <span class="font-semibold text-yellow-300">
                        {{ $declarationsParStatut['soumise'] ?? 0 }}
                    </span>
                    en attente · <span class="font-semibold text-green-300">
                        {{ $declarationsParStatut['validee'] ?? 0 }}
                    </span>
                    validée(s)
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Carte 2 : Statut de l'agrément --}}
    @php
        // Couleurs et libellés selon le statut de l'agrément
        $statutAgrement = $agrement->statut ?? 'aucun';
        $couleurAgrement = match($statutAgrement) {
            'valide'    => ['135deg, #059669 0%, #047857 100%', 'Valide', 'text-green-200'],
            'expire'    => ['135deg, #dc2626 0%, #b91c1c 100%', 'Expiré', 'text-red-200'],
            'suspendu'  => ['135deg, #d97706 0%, #b45309 100%', 'Suspendu', 'text-yellow-200'],
            'revoque'   => ['135deg, #7c3aed 0%, #6d28d9 100%', 'Révoqué', 'text-purple-200'],
            default     => ['135deg, #6b7280 0%, #4b5563 100%', 'Non renseigné', 'text-gray-200'],
        };
    @endphp
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient({{ $couleurAgrement[0] }});">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full"
             style="background: rgba(255,255,255,0.08);"></div>
        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">Mon agrément</p>
                <p class="text-2xl font-black mt-1 leading-tight">{{ $couleurAgrement[1] }}</p>
                @if ($agrement)
                    <p class="text-xs mt-2 opacity-75">
                        N° {{ $agrement->numero_agrement }}
                    </p>
                    @if ($agrement->date_expiration)
                        <p class="text-xs {{ $couleurAgrement[2] }}">
                            Exp. {{ \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y') }}
                        </p>
                    @endif
                @else
                    <p class="text-xs mt-2 opacity-60">Aucun agrément enregistré</p>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Carte 3 : Période déclarative en cours (orange) --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #F97316 0%, #ea580c 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full"
             style="background: rgba(255,255,255,0.08);"></div>
        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Période en cours
                </p>
                @if ($periodeCourante)
                    <p class="text-lg font-black mt-1 leading-tight">
                        {{ ucfirst($periodeCourante->type) }} {{ $periodeCourante->annee }}
                    </p>
                    <p class="text-xs mt-2 opacity-75">
                        Du {{ \Carbon\Carbon::parse($periodeCourante->date_debut)->format('d/m') }}
                        au {{ \Carbon\Carbon::parse($periodeCourante->date_fin)->format('d/m/Y') }}
                    </p>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold bg-white/20">
                        Ouverte
                    </span>
                @else
                    <p class="text-lg font-black mt-1">Aucune</p>
                    <p class="text-xs mt-2 opacity-75">Pas de période ouverte</p>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Carte 4 : Produits déclarés (gris foncé) --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #374151 0%, #1f2937 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full"
             style="background: rgba(255,255,255,0.08);"></div>
        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Mes produits
                </p>
                <p class="text-4xl font-black mt-1 leading-none">{{ $totalProduits }}</p>
                <p class="text-xs mt-2 opacity-75">Produits enregistrés</p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
        </div>
    </div>

</div>{{-- /cartes --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
    LIGNE INFÉRIEURE : Dernière déclaration + Fiche unité industrielle
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Panneau : Dernière déclaration --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Dernière déclaration
        </h3>

        @if ($derniereDeclaration)
            {{-- Statut coloré --}}
            @php
                $couleurStatut = match($derniereDeclaration->statut) {
                    'validee'     => 'bg-green-100 text-green-700',
                    'soumise'     => 'bg-yellow-100 text-yellow-700',
                    'en_revision' => 'bg-blue-100 text-blue-700',
                    'rejetee'     => 'bg-red-100 text-red-700',
                    default       => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Numéro</dt>
                    <dd class="font-mono font-semibold text-gray-800 text-xs">
                        {{ $derniereDeclaration->numero_declaration }}
                    </dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $couleurStatut }}">
                            {{ ucfirst(str_replace('_', ' ', $derniereDeclaration->statut)) }}
                        </span>
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Date de création</dt>
                    <dd class="font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($derniereDeclaration->created_at)->format('d/m/Y') }}
                    </dd>
                </div>
                @if ($derniereDeclaration->date_soumission)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date de soumission</dt>
                        <dd class="font-medium text-gray-800">
                            {{ \Carbon\Carbon::parse($derniereDeclaration->date_soumission)->format('d/m/Y') }}
                        </dd>
                    </div>
                @endif
                @if ($derniereDeclaration->observations)
                    <div class="pt-2 border-t border-gray-100">
                        <dt class="text-gray-500 mb-1">Observations</dt>
                        <dd class="text-gray-700 text-xs bg-gray-50 rounded-lg p-2">
                            {{ $derniereDeclaration->observations }}
                        </dd>
                    </div>
                @endif
            </dl>
            <div class="mt-4">
                <a href="#"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
                   style="background-color: rgba(249,115,22,0.1); color: #F97316;">
                    Voir toutes mes déclarations →
                </a>
            </div>
        @else
            {{-- État vide --}}
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-400">Aucune déclaration pour l'instant.</p>
                <a href="#"
                   class="mt-3 inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition-colors"
                   style="background-color: #F97316;">
                    + Créer ma première déclaration
                </a>
            </div>
        @endif
    </div>

    {{-- Panneau : Fiche de l'unité industrielle --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Mon unité industrielle
        </h3>

        @if ($unite)
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Dénomination</dt>
                    <dd class="font-semibold text-gray-800 text-right max-w-xs truncate">{{ $unite->denomination }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">N° immatriculation</dt>
                    <dd class="font-mono text-xs font-semibold text-gray-800">{{ $unite->numero_immatriculation }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Secteur d'activité</dt>
                    <dd class="font-medium text-gray-800">{{ $unite->secteur_activite }}</dd>
                </div>
                @if ($unite->regime)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Régime</dt>
                        <dd class="font-medium text-gray-800">{{ $unite->regime }}</dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Localisation</dt>
                    <dd class="font-medium text-gray-800">{{ $unite->commune }}, {{ $unite->departement }}</dd>
                </div>
                @if ($unite->responsable_nom)
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Responsable</dt>
                        <dd class="font-medium text-gray-800">
                            {{ $unite->responsable_nom }}
                            @if ($unite->responsable_fonction)
                                <span class="text-gray-400 text-xs">({{ $unite->responsable_fonction }})</span>
                            @endif
                        </dd>
                    </div>
                @endif
                {{-- Badge actif/inactif --}}
                <div class="flex justify-between items-center pt-1">
                    <dt class="text-gray-500">Statut</dt>
                    <dd>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                     {{ $unite->actif ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $unite->actif ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            {{ $unite->actif ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
            </dl>
        @else
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                </svg>
                <p class="text-sm text-gray-400">Aucune unité industrielle associée.</p>
                <p class="text-xs text-gray-300 mt-1">Contactez l'administration pour lier votre compte.</p>
            </div>
        @endif
    </div>

</div>{{-- /ligne inférieure --}}

@endsection
