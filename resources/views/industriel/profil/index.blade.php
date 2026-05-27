@extends('layouts.industriel')

@section('titre', 'Mon profil')
@section('sous_titre', 'Informations personnelles et données de votre entreprise')

@section('contenu')

@php
    // Couleur et libellé selon le statut de l'agrément
    $statutConfig = match($agrement->statut ?? '') {
        'valide'   => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'label' => 'Valide'],
        'suspendu' => ['bg' => 'bg-amber-100',  'text' => 'text-amber-700',  'label' => 'Suspendu'],
        'expire'   => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'label' => 'Expiré'],
        'revoque'  => ['bg' => 'bg-gray-100',   'text' => 'text-gray-600',   'label' => 'Révoqué'],
        default    => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',   'label' => 'Inconnu'],
    };
@endphp

{{-- ── Grille principale ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ════════════════════════════════════════════════════════════════════════
        COLONNE GAUCHE — Carte identité + unité + agrément (lecture seule)
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-1 space-y-5">

        {{-- Carte avatar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">

            {{-- Avatar avec initiales --}}
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white text-2xl font-black mx-auto mb-4"
                 style="background-color: #F97316;">
                {{ strtoupper(mb_substr($utilisateur->prenom, 0, 1)) }}{{ strtoupper(mb_substr($utilisateur->nom, 0, 1)) }}
            </div>

            <h2 class="text-lg font-bold text-gray-900 leading-tight">
                {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
            </h2>

            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold"
                  style="background-color: rgba(249,115,22,0.1); color: #F97316;">
                Industriel
            </span>

            <p class="text-sm text-gray-500 mt-2">{{ $utilisateur->email }}</p>

            @if ($utilisateur->telephone)
            <p class="text-sm text-gray-400 mt-1">
                <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
                {{ $utilisateur->telephone }}
            </p>
            @endif
        </div>

        {{-- Carte unité industrielle (lecture seule) --}}
        @if ($unite)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color: rgba(26,35,126,0.08);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="#1a237e" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Mon unité</h3>
            </div>

            <p class="text-sm font-bold text-gray-900 mb-3 leading-tight">{{ $unite->denomination }}</p>

            <dl class="space-y-2 text-xs">
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Secteur</dt>
                    <dd class="text-gray-700 font-medium text-right">{{ $unite->secteur_activite ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Département</dt>
                    <dd class="text-gray-700 font-medium">{{ $unite->departement ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Commune</dt>
                    <dd class="text-gray-700 font-medium">{{ $unite->commune ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Immatriculation</dt>
                    <dd class="text-gray-600 font-mono text-[10px]">{{ $unite->numero_immatriculation }}</dd>
                </div>
            </dl>
        </div>
        @endif

        {{-- Carte agrément (lecture seule) --}}
        @if ($agrement)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color: rgba(26,35,126,0.08);">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="#1a237e" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Mon agrément</h3>
            </div>

            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-bold font-mono text-gray-900">{{ $agrement->numero_agrement }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $statutConfig['bg'] }} {{ $statutConfig['text'] }}">
                    {{ $statutConfig['label'] }}
                </span>
            </div>

            <dl class="space-y-2 text-xs">
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Type</dt>
                    <dd class="text-gray-700 font-medium text-right">{{ $agrement->type_agrement ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Délivré le</dt>
                    <dd class="text-gray-700 font-medium">
                        {{ $agrement->date_delivrance
                            ? \Carbon\Carbon::parse($agrement->date_delivrance)->format('d/m/Y')
                            : '—' }}
                    </dd>
                </div>
                <div class="flex justify-between gap-2">
                    <dt class="text-gray-400">Expire le</dt>
                    <dd class="font-medium {{ $agrement->statut === 'expire' ? 'text-red-600' : 'text-gray-700' }}">
                        {{ $agrement->date_expiration
                            ? \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y')
                            : 'Illimité' }}
                    </dd>
                </div>
            </dl>
        </div>
        @endif

    </div>{{-- /colonne gauche --}}

    {{-- ════════════════════════════════════════════════════════════════════════
        COLONNE DROITE — Formulaire de modification
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Modifier mes informations</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Les données de votre unité et de votre agrément sont modifiables uniquement par l'administration.
                </p>
            </div>

            {{-- Flash succès --}}
            @if (session('statut'))
            <div class="mx-6 mt-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('statut') }}
            </div>
            @endif

            {{-- Erreurs de validation --}}
            @if ($errors->any())
            <div class="mx-6 mt-4 flex items-start gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('industriel.profil.update') }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">

                    {{-- Nom --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nom"
                               value="{{ old('nom', $utilisateur->nom) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('nom') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition-colors">
                    </div>

                    {{-- Prénom --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="prenom"
                               value="{{ old('prenom', $utilisateur->prenom) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('prenom') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition-colors">
                    </div>

                    {{-- Email --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Adresse e-mail <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email"
                               value="{{ old('email', $utilisateur->email) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition-colors">
                        <p class="text-xs text-gray-400 mt-1">Utilisée pour la connexion et les notifications.</p>
                    </div>

                    {{-- Téléphone --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Téléphone <span class="font-normal text-gray-400 normal-case">(optionnel)</span>
                        </label>
                        <input type="tel" name="telephone"
                               value="{{ old('telephone', $utilisateur->telephone) }}"
                               placeholder="+229 XX XX XX XX"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('telephone') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#F97316] focus:border-transparent transition-colors">
                    </div>

                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                        style="background-color: #F97316;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </form>
        </div>

    </div>{{-- /colonne droite --}}

</div>

@endsection
