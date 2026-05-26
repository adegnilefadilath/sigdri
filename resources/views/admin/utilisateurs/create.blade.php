@extends('layouts.app')

@section('titre', 'Nouvel utilisateur')
@section('sous_titre', 'Création d\'un nouveau compte')

@section('contenu')

{{-- ── En-tête ───────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.utilisateurs.index') }}"
       class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <div>
        <h2 class="text-lg font-bold text-gray-900">Créer un compte utilisateur</h2>
        <p class="text-xs text-gray-500">Tous les champs marqués * sont obligatoires.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.utilisateurs.store') }}" id="form-utilisateur">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Colonne principale (2/3) ─────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Identité --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                    Identité
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Nom --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nom" value="{{ old('nom') }}"
                               class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                      {{ $errors->has('nom') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        @error('nom')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Prénom --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="prenom" value="{{ old('prenom') }}"
                               class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                      {{ $errors->has('prenom') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        @error('prenom')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- E-mail --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Adresse e-mail <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                      {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        @error('email')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Mot de passe --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                    Mot de passe
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="mot_de_passe"
                               class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                      {{ $errors->has('mot_de_passe') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        @error('mot_de_passe')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Confirmation <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="mot_de_passe_confirmation"
                               class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Le mot de passe doit contenir au moins 8 caractères.</p>
            </div>

        </div>

        {{-- ── Colonne latérale (1/3) ───────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Rôle et accès --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">
                    Rôle et accès
                </h3>
                {{-- Sélection du rôle --}}
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Rôle <span class="text-red-500">*</span>
                    </label>
                    <select name="role" id="select-role" onchange="afficherChampUnite()"
                            class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                   {{ $errors->has('role') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        <option value="">— Choisir un rôle —</option>
                        @foreach ($roles as $cle => $libelle)
                            <option value="{{ $cle }}" {{ old('role') === $cle ? 'selected' : '' }}>
                                {{ $libelle }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Unité industrielle — visible uniquement si rôle = industriel --}}
                <div id="champ-unite" style="{{ old('role') === 'industriel' ? '' : 'display:none;' }}">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        Unité industrielle <span class="text-red-500">*</span>
                    </label>
                    <select name="unite_industrielle_id"
                            class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                   {{ $errors->has('unite_industrielle_id') ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                        <option value="">— Choisir une unité —</option>
                        @foreach ($unites as $unite)
                            <option value="{{ $unite->id }}"
                                    {{ old('unite_industrielle_id') == $unite->id ? 'selected' : '' }}>
                                {{ $unite->denomination }} ({{ $unite->departement }})
                            </option>
                        @endforeach
                    </select>
                    @error('unite_industrielle_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-400 mt-1">
                        L'industriel sera associé à cette unité et pourra y soumettre des déclarations.
                    </p>
                </div>

                {{-- Description des rôles --}}
                <div class="mt-4 pt-3 border-t border-gray-100 space-y-2">
                    <p class="text-xs font-medium text-gray-500 mb-1">Descriptions des rôles</p>
                    @foreach ([
                        'super_admin' => 'Accès complet à toutes les fonctionnalités.',
                        'admin'       => 'Gestion des utilisateurs et des paramètres.',
                        'agent_mic'   => 'Validation et suivi des déclarations.',
                        'decideur'    => 'Consultation des rapports et statistiques.',
                        'industriel'  => 'Soumission de déclarations pour son unité.',
                    ] as $cle => $desc)
                    <div class="flex gap-2">
                        <span class="text-xs font-semibold shrink-0" style="color:#1a237e;">{{ $cle }}</span>
                        <span class="text-xs text-gray-400">{{ $desc }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Boutons --}}
            <div class="flex flex-col gap-2">
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-lg text-sm font-medium text-white shadow-sm"
                        style="background-color:#1a237e;">
                    Créer le compte
                </button>
                <a href="{{ route('admin.utilisateurs.index') }}"
                   class="w-full text-center px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                    Annuler
                </a>
            </div>

        </div>
    </div>

</form>

@endsection

@push('scripts')
<script>
// Affiche ou masque le champ "Unité industrielle" selon le rôle sélectionné
function afficherChampUnite() {
    var role  = document.getElementById('select-role').value;
    var champ = document.getElementById('champ-unite');
    champ.style.display = (role === 'industriel') ? '' : 'none';
}
</script>
@endpush
