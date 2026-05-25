@extends('layouts.app')

@section('titre', 'Enregistrer une unité industrielle')
@section('sous_titre', 'Ajouter une unité déjà autorisée au référentiel SIGDRI')

@section('contenu')

<div class="max-w-3xl">

    {{-- ── Note d'information ──────────────────────────────────────────────── --}}
    <div class="flex items-start gap-3 px-4 py-3 mb-6 rounded-xl border text-sm"
         style="background:#eff3ff; border-color:#c7d2fe; color:#3730a3;">
        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p>
            L'autorisation d'installation industrielle est délivrée par le portail
            <strong>service-public.bj</strong>.
            Ce formulaire enregistre uniquement les unités <strong>déjà autorisées</strong> afin de les intégrer
            dans le suivi SIGDRI.
        </p>
    </div>

    {{-- ── Erreurs de validation groupées ─────────────────────────────────── --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 px-4 py-3 mb-5 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.unites.store') }}">
        @csrf

        {{-- ════════════════════════════════════════════════════════════════
            Section 1 — Identité de l'entreprise
        ════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">

            <h2 class="text-xs font-bold uppercase tracking-wide mb-4 flex items-center gap-2" style="color:#1a237e;">
                <span class="w-1 h-4 rounded-full inline-block shrink-0" style="background:#1a237e;"></span>
                Identité de l'entreprise
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Raison sociale --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Raison sociale <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="denomination" value="{{ old('denomination') }}"
                           placeholder="Dénomination officielle de l'entreprise"
                           autofocus
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('denomination') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('denomination')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- N° RCCM --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        N° RCCM <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="numero_immatriculation" value="{{ old('numero_immatriculation') }}"
                           placeholder="ex : RB-COT-2024-00001"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('numero_immatriculation') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('numero_immatriculation')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Secteur d'activité --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Secteur d'activité <span class="text-red-500">*</span>
                    </label>
                    <select name="secteur_activite"
                            class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                   {{ $errors->has('secteur_activite') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                        <option value="">— Sélectionner —</option>
                        @foreach (['Agroalimentaire','Textile','BTP','Chimie','Bois','Mécanique','Plastique','Métallurgie','Autre'] as $s)
                            <option value="{{ $s }}" {{ old('secteur_activite') === $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                    @error('secteur_activite')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
            Section 2 — Localisation
        ════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">

            <h2 class="text-xs font-bold uppercase tracking-wide mb-4 flex items-center gap-2" style="color:#1a237e;">
                <span class="w-1 h-4 rounded-full inline-block shrink-0" style="background:#1a237e;"></span>
                Localisation
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Département --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Département <span class="text-red-500">*</span>
                    </label>
                    <select name="departement"
                            class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                   focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                   {{ $errors->has('departement') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                        <option value="">— Sélectionner —</option>
                        @foreach (['Alibori','Atacora','Atlantique','Borgou','Collines','Couffo','Donga','Littoral','Mono','Ouémé','Plateau','Zou'] as $d)
                            <option value="{{ $d }}" {{ old('departement') === $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                    @error('departement')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Commune --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Commune <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="commune" value="{{ old('commune') }}"
                           placeholder="ex : Cotonou"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('commune') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('commune')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Quartier / Adresse --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Quartier / Adresse <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}"
                           placeholder="ex : Quartier Zogbo, Rue des Industries, Lot 12"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('adresse') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('adresse')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Coordonnées GPS --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Coordonnées GPS
                        <span class="ml-1 font-normal text-gray-400 normal-case">(optionnel)</span>
                    </label>
                    <input type="text" name="coordonnees_geographiques"
                           value="{{ old('coordonnees_geographiques') }}"
                           placeholder="ex : 6.3654° N, 2.4183° E"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
            Section 3 — Responsable & Contact
        ════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">

            <h2 class="text-xs font-bold uppercase tracking-wide mb-4 flex items-center gap-2" style="color:#1a237e;">
                <span class="w-1 h-4 rounded-full inline-block shrink-0" style="background:#1a237e;"></span>
                Responsable &amp; Contact
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Nom du responsable --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Nom du responsable <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="responsable_nom" value="{{ old('responsable_nom') }}"
                           placeholder="NOM Prénom"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('responsable_nom') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('responsable_nom')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fonction du responsable --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Fonction
                        <span class="ml-1 font-normal text-gray-400 normal-case">(optionnel)</span>
                    </label>
                    <input type="text" name="responsable_fonction" value="{{ old('responsable_fonction') }}"
                           placeholder="ex : Directeur Général"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                </div>

                {{-- E-mail --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        E-mail de contact <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           placeholder="contact@entreprise.bj"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Téléphone --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Téléphone de contact <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           placeholder="+229 01 00 00 00"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('telephone') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}">
                    @error('telephone')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════════════
            Section 4 — Données de production
        ════════════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">

            <h2 class="text-xs font-bold uppercase tracking-wide mb-4 flex items-center gap-2" style="color:#1a237e;">
                <span class="w-1 h-4 rounded-full inline-block shrink-0" style="background:#1a237e;"></span>
                Données de production
                <span class="ml-1 font-normal text-gray-400 normal-case">(optionnel)</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Nombre d'employés --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Nombre d'employés
                    </label>
                    <input type="number" name="nombre_employes" value="{{ old('nombre_employes') }}"
                           placeholder="ex : 45" min="0"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent
                                  {{ $errors->has('nombre_employes') ? 'border-red-400 bg-red-50' : '' }}">
                    @error('nombre_employes')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Capacité de production installée --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Capacité de production installée
                    </label>
                    <input type="text" name="capacite_production" value="{{ old('capacite_production') }}"
                           placeholder="ex : 500 tonnes/an, 200 000 unités/mois"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 transition-colors
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                </div>

            </div>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white
                           shadow-sm transition-all hover:opacity-90"
                    style="background-color:#F97316;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Enregistrer l'unité
            </button>
            <a href="{{ route('admin.unites.index') }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600
                      hover:bg-gray-50 transition-all">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
