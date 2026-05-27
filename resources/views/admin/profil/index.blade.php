@extends('layouts.app')

@section('titre', 'Mon profil')
@section('sous_titre', 'Informations personnelles et sécurité du compte')

@section('contenu')

@php
    // Libellés lisibles des rôles
    $libelles = [
        'super_admin' => 'Super Administrateur',
        'admin'       => 'Administrateur',
        'agent_mic'   => 'Agent MIC',
        'decideur'    => 'Décideur',
        'industriel'  => 'Industriel',
    ];
    $libelleRole = $libelles[$utilisateur->role] ?? $utilisateur->role;

    // Déterminer si le formulaire mot de passe était actif (après erreur ou succès)
    $ongletMdp = session('onglet_actif') === 'password'
              || session('statut_mdp')
              || $errors->has('ancien_mot_de_passe')
              || $errors->has('nouveau_mot_de_passe')
              || $errors->has('nouveau_mot_de_passe_confirmation');
@endphp

{{-- ── Grille principale ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ════════════════════════════════════════════════════════════════════════
        COLONNE GAUCHE — Carte identité
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-1 space-y-5">

        {{-- Carte avatar + identité --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 text-center">

            {{-- Grand avatar avec initiales --}}
            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-white text-2xl font-black mx-auto mb-4"
                 style="background-color: #1a237e;">
                {{ strtoupper(mb_substr($utilisateur->prenom, 0, 1)) }}{{ strtoupper(mb_substr($utilisateur->nom, 0, 1)) }}
            </div>

            <h2 class="text-lg font-bold text-gray-900 leading-tight">
                {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
            </h2>

            {{-- Badge rôle --}}
            <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-bold"
                  style="background-color: rgba(26,35,126,0.1); color: #1a237e;">
                {{ $libelleRole }}
            </span>

            <p class="text-sm text-gray-500 mt-3">{{ $utilisateur->email }}</p>

            {{-- Statut actif --}}
            <div class="mt-4 flex items-center justify-center gap-1.5">
                <span class="w-2 h-2 rounded-full {{ $utilisateur->actif ? 'bg-green-500' : 'bg-red-400' }}"></span>
                <span class="text-xs font-medium {{ $utilisateur->actif ? 'text-green-700' : 'text-red-600' }}">
                    {{ $utilisateur->actif ? 'Compte actif' : 'Compte suspendu' }}
                </span>
            </div>
        </div>

        {{-- Carte méta : dates --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-widest text-gray-400">Activité du compte</h3>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color: rgba(26,35,126,0.08);">
                    <svg class="w-4 h-4" fill="none" stroke="#1a237e" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Membre depuis</p>
                    <p class="text-sm font-semibold text-gray-800">
                        {{ \Carbon\Carbon::parse($utilisateur->created_at)->format('d/m/Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color: rgba(249,115,22,0.08);">
                    <svg class="w-4 h-4" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Dernière connexion</p>
                    <p class="text-sm font-semibold text-gray-800">
                        @if ($utilisateur->derniere_connexion)
                            {{ \Carbon\Carbon::parse($utilisateur->derniere_connexion)->format('d/m/Y à H:i') }}
                        @else
                            <span class="text-gray-400 font-normal">Non renseignée</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

    </div>{{-- /colonne gauche --}}

    {{-- ════════════════════════════════════════════════════════════════════════
        COLONNE DROITE — Formulaires
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- ── Formulaire informations personnelles ───────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Informations personnelles</h3>
                <p class="text-xs text-gray-400 mt-0.5">Nom, prénom et adresse e-mail de connexion</p>
            </div>

            {{-- Flash succès informations --}}
            @if (session('statut') && ! $ongletMdp)
            <div class="mx-6 mt-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('statut') }}
            </div>
            @endif

            {{-- Erreurs du formulaire infos --}}
            @if (! $ongletMdp && $errors->hasAny(['nom', 'prenom', 'email']))
            <div class="mx-6 mt-4 flex items-start gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->only(['nom', 'prenom', 'email']) as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.profil.update') }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

                    {{-- Nom --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nom"
                               value="{{ old('nom', $utilisateur->nom) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('nom') && ! $ongletMdp ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    </div>

                    {{-- Prénom --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="prenom"
                               value="{{ old('prenom', $utilisateur->prenom) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('prenom') && ! $ongletMdp ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    </div>

                    {{-- Email --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Adresse e-mail <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email"
                               value="{{ old('email', $utilisateur->email) }}"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('email') && ! $ongletMdp ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                        <p class="text-xs text-gray-400 mt-1">Cette adresse est utilisée pour la connexion.</p>
                    </div>

                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                        style="background-color: #1a237e;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les modifications
                </button>
            </form>
        </div>

        {{-- ── Formulaire changement de mot de passe ──────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Changer le mot de passe</h3>
                <p class="text-xs text-gray-400 mt-0.5">Minimum 8 caractères</p>
            </div>

            {{-- Flash succès mot de passe --}}
            @if (session('statut_mdp'))
            <div class="mx-6 mt-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('statut_mdp') }}
            </div>
            @endif

            {{-- Erreurs du formulaire mot de passe --}}
            @if ($ongletMdp && $errors->hasAny(['ancien_mot_de_passe', 'nouveau_mot_de_passe', 'nouveau_mot_de_passe_confirmation']))
            <div class="mx-6 mt-4 flex items-start gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->only(['ancien_mot_de_passe', 'nouveau_mot_de_passe', 'nouveau_mot_de_passe_confirmation']) as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.profil.password') }}" class="p-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">

                    {{-- Ancien mot de passe — pleine largeur --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Ancien mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="ancien_mot_de_passe"
                               autocomplete="current-password"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('ancien_mot_de_passe') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    </div>

                    {{-- Nouveau mot de passe --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Nouveau mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="nouveau_mot_de_passe"
                               autocomplete="new-password"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('nouveau_mot_de_passe') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    </div>

                    {{-- Confirmation --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                            Confirmer le mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="nouveau_mot_de_passe_confirmation"
                               autocomplete="new-password"
                               class="w-full px-3 py-2.5 text-sm rounded-xl border
                                      {{ $errors->has('nouveau_mot_de_passe_confirmation') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                      focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    </div>

                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                        style="background-color: #F97316;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Changer le mot de passe
                </button>
            </form>
        </div>

    </div>{{-- /colonne droite --}}

</div>

@endsection
