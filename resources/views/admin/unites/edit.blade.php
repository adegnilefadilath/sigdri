@extends('layouts.app')

@section('titre', 'Modifier l\'unité industrielle')
@section('sous_titre', $unite->denomination)

@section('contenu')

<div class="max-w-3xl">

    @if ($errors->any())
        <div class="mb-5 flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $erreur)
                    <li>{{ $erreur }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.unites.update', $unite->id) }}">
        @csrf
        @method('PUT')

        {{-- ── Identité --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Identité de l'unité</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Dénomination <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="denomination"
                           value="{{ old('denomination', $unite->denomination) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('denomination') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        N° Immatriculation <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="numero_immatriculation"
                           value="{{ old('numero_immatriculation', $unite->numero_immatriculation) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('numero_immatriculation') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Secteur d'activité <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="secteur_activite"
                           value="{{ old('secteur_activite', $unite->secteur_activite) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('secteur_activite') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Régime</label>
                    <select name="regime"
                            class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                        <option value="">— Sélectionner —</option>
                        @foreach (['Droit commun', 'Zone franche', 'Zone économique spéciale', 'Franchise douanière', 'Autre'] as $r)
                            <option value="{{ $r }}" {{ old('regime', $unite->regime) === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ── Localisation --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Localisation</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Adresse <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="adresse"
                           value="{{ old('adresse', $unite->adresse) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('adresse') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Commune <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="commune"
                           value="{{ old('commune', $unite->commune) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('commune') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Département <span class="text-red-500">*</span>
                    </label>
                    <select name="departement"
                            class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('departement') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                        <option value="">— Sélectionner —</option>
                        @foreach (['Alibori','Atacora','Atlantique','Borgou','Collines','Couffo','Donga','Littoral','Mono','Ouémé','Plateau','Zou'] as $dept)
                            <option value="{{ $dept }}" {{ old('departement', $unite->departement) === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </div>

        {{-- ── Contact et responsable --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Contact &amp; Responsable</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Téléphone</label>
                    <input type="text" name="telephone"
                           value="{{ old('telephone', $unite->telephone) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">E-mail de contact</label>
                    <input type="email" name="email"
                           value="{{ old('email', $unite->email) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Nom du responsable</label>
                    <input type="text" name="responsable_nom"
                           value="{{ old('responsable_nom', $unite->responsable_nom) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Fonction</label>
                    <input type="text" name="responsable_fonction"
                           value="{{ old('responsable_fonction', $unite->responsable_fonction) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

            </div>
        </div>

        {{-- ── Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow transition-all hover:opacity-90"
                    style="background-color: #F97316;">
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.unites.show', $unite->id) }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
