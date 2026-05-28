@extends('layouts.industriel')

@section('titre', 'Mon tableau de bord')
@section('sous_titre', 'Vue d\'ensemble de votre activité industrielle')

@section('contenu')

{{-- ═══════════════════════════════════════════════════════════════════════════
    CARTES STATISTIQUES
    2 colonnes sur mobile (375 px), 4 sur desktop.
    Aucun SVG : texte uniquement pour garantir le rendu sur tous les écrans.
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">

    {{-- Carte 1 : Déclarations (bleu nuit) --}}
    <div class="rounded-xl p-4 text-white"
         style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%);">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
            Déclarations
        </p>
        <p class="text-3xl font-black mt-1 leading-none">{{ $totalDeclarations }}</p>
        <p class="text-xs mt-2 opacity-70">
            {{ $declarationsParStatut['soumise'] ?? 0 }} en attente
            &middot; {{ $declarationsParStatut['validee'] ?? 0 }} validée(s)
        </p>
    </div>

    {{-- Carte 2 : Agrément — couleur dépend du statut --}}
    @php
        // Dégradé et libellé selon le statut de l'agrément
        $statutAgrement  = $agrement->statut ?? 'aucun';
        $couleurAgrement = match($statutAgrement) {
            'valide'   => ['135deg, #059669 0%, #047857 100%', 'Valide'],
            'expire'   => ['135deg, #dc2626 0%, #b91c1c 100%', 'Expiré'],
            'suspendu' => ['135deg, #d97706 0%, #b45309 100%', 'Suspendu'],
            'revoque'  => ['135deg, #7c3aed 0%, #6d28d9 100%', 'Révoqué'],
            default    => ['135deg, #6b7280 0%, #4b5563 100%', '—'],
        };
    @endphp
    <div class="rounded-xl p-4 text-white"
         style="background: linear-gradient({{ $couleurAgrement[0] }});">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
            Agrément
        </p>
        <p class="text-xl font-black mt-1 leading-tight">{{ $couleurAgrement[1] }}</p>
        @if ($agrement)
            <p class="text-xs mt-2 opacity-70 truncate">
                N°&nbsp;{{ $agrement->numero_agrement }}
            </p>
            @if ($agrement->date_expiration)
                <p class="text-xs opacity-60">
                    Exp.&nbsp;{{ \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y') }}
                </p>
            @endif
        @else
            <p class="text-xs mt-2 opacity-60">Non renseigné</p>
        @endif
    </div>

    {{-- Carte 3 : Période déclarative (orange) --}}
    <div class="rounded-xl p-4 text-white"
         style="background: linear-gradient(135deg, #F97316 0%, #ea580c 100%);">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
            Période
        </p>
        @if ($periodeCourante)
            <p class="text-lg font-black mt-1 leading-tight">
                {{ ucfirst($periodeCourante->type) }}&nbsp;{{ $periodeCourante->annee }}
            </p>
            <p class="text-xs mt-2 opacity-70">
                {{ \Carbon\Carbon::parse($periodeCourante->date_debut)->format('d/m') }}
                → {{ \Carbon\Carbon::parse($periodeCourante->date_fin)->format('d/m/Y') }}
            </p>
            <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-bold"
                  style="background: rgba(255,255,255,0.2);">Ouverte</span>
        @else
            <p class="text-xl font-black mt-1">Aucune</p>
            <p class="text-xs mt-2 opacity-70">Pas de période ouverte</p>
        @endif
    </div>

    {{-- Carte 4 : Produits enregistrés (gris foncé) --}}
    <div class="rounded-xl p-4 text-white"
         style="background: linear-gradient(135deg, #374151 0%, #1f2937 100%);">
        <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
            Produits
        </p>
        <p class="text-3xl font-black mt-1 leading-none">{{ $totalProduits }}</p>
        <p class="text-xs mt-2 opacity-70">Produits enregistrés</p>
    </div>

</div>{{-- /cartes --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
    LIGNE INFÉRIEURE : Dernière déclaration + Fiche unité industrielle
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Panneau : Dernière déclaration --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                {{-- Lien vers la liste complète des déclarations --}}
                <a href="{{ route('industriel.declarations.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-2 rounded-lg transition-colors"
                   style="background-color: rgba(249,115,22,0.1); color: #F97316;">
                    Voir toutes mes déclarations →
                </a>
            </div>
        @else
            {{-- État vide : icône max 24px pour ne pas déborder sur mobile --}}
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg width="20" height="20" style="flex-shrink:0" class="text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-400">Aucune déclaration pour l'instant.</p>
                {{-- Bouton pleine largeur sur mobile --}}
                <a href="{{ route('industriel.declarations.create') }}"
                   class="mt-3 w-full sm:w-auto inline-flex items-center justify-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition-colors"
                   style="background-color: #F97316;">
                    + Créer ma première déclaration
                </a>
            </div>
        @endif
    </div>

    {{-- Panneau : Fiche de l'unité industrielle --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
            <svg width="20" height="20" style="flex-shrink:0" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
            {{-- État vide : icône 24px max --}}
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <svg width="20" height="20" style="flex-shrink:0" class="text-gray-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
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
