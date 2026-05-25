@extends('layouts.app')

@section('titre', 'Modifier l\'agrément')
@section('sous_titre', $a->numero_agrement)

@section('contenu')

<div class="max-w-2xl">

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

    <form method="POST" action="{{ route('admin.agrements.update', $a->id) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Modifier l'agrément</h2>

            {{-- Numéro (lecture seule) --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Numéro d'agrément
                </label>
                <input type="text" value="{{ $a->numero_agrement }}" disabled
                       class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 bg-gray-100 text-gray-500 font-mono cursor-not-allowed">
                <p class="text-xs text-gray-400 mt-1">Le numéro d'agrément ne peut pas être modifié.</p>
            </div>

            {{-- Unité industrielle --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Unité industrielle <span class="text-red-500">*</span>
                </label>
                <select name="unite_industrielle_id"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('unite_industrielle_id') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    @foreach ($unites as $unite)
                        <option value="{{ $unite->id }}"
                                {{ old('unite_industrielle_id', $a->unite_industrielle_id) == $unite->id ? 'selected' : '' }}>
                            {{ $unite->denomination }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Type d'agrément <span class="text-red-500">*</span>
                </label>
                <input type="text" name="type_agrement"
                       value="{{ old('type_agrement', $a->type_agrement) }}"
                       list="types-agrement"
                       class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('type_agrement') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                <datalist id="types-agrement">
                    @foreach (['Exploitation industrielle','Exportation','Zone franche','Transformation agroalimentaire','Extraction minière','Traitement des déchets','Importation de matières premières'] as $type)
                        <option value="{{ $type }}">
                    @endforeach
                </datalist>
            </div>

            {{-- Dates --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Date de délivrance <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_delivrance"
                           value="{{ old('date_delivrance', $a->date_delivrance) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('date_delivrance') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Date d'expiration
                    </label>
                    <input type="date" name="date_expiration"
                           value="{{ old('date_expiration', $a->date_expiration) }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('date_expiration') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>
            </div>

            {{-- Statut --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Statut <span class="text-red-500">*</span>
                </label>
                <select name="statut"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('statut') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    @foreach (['valide' => 'Valide', 'expire' => 'Expiré', 'suspendu' => 'Suspendu', 'revoque' => 'Révoqué'] as $val => $label)
                        <option value="{{ $val }}" {{ old('statut', $a->statut) === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Observations --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Observations
                </label>
                <textarea name="observations" rows="3"
                          class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors resize-none">{{ old('observations', $a->observations) }}</textarea>
            </div>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow transition-all hover:opacity-90"
                    style="background-color: #F97316;">
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.agrements.show', $a->id) }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
