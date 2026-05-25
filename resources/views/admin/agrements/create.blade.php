@extends('layouts.app')

@section('titre', 'Nouvel agrément')
@section('sous_titre', 'Le numéro d\'agrément sera généré automatiquement (AGR-ANNÉE-XXX)')

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

    {{-- Info génération automatique du numéro --}}
    <div class="mb-5 flex items-center gap-2.5 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Le numéro d'agrément (format <code class="font-mono font-bold">AGR-{{ date('Y') }}-XXX</code>) sera généré automatiquement à l'enregistrement.
    </div>

    <form method="POST" action="{{ route('admin.agrements.store') }}">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Informations de l'agrément</h2>

            {{-- Unité industrielle --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Unité industrielle <span class="text-red-500">*</span>
                </label>
                <select name="unite_industrielle_id"
                        class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('unite_industrielle_id') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    <option value="">— Sélectionner une unité industrielle —</option>
                    @foreach ($unites as $unite)
                        <option value="{{ $unite->id }}"
                                {{ (old('unite_industrielle_id', $unitePreselectionnee) == $unite->id) ? 'selected' : '' }}>
                            {{ $unite->denomination }}
                            <span class="text-gray-400">({{ $unite->numero_immatriculation }})</span>
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Type d'agrément --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Type d'agrément <span class="text-red-500">*</span>
                </label>
                <input type="text" name="type_agrement" value="{{ old('type_agrement') }}"
                       placeholder="ex : Exploitation industrielle"
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
                    <input type="date" name="date_delivrance" value="{{ old('date_delivrance') }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('date_delivrance') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Date d'expiration
                        <span class="text-gray-400 font-normal normal-case">(facultatif)</span>
                    </label>
                    <input type="date" name="date_expiration" value="{{ old('date_expiration') }}"
                           class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('date_expiration') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>
            </div>

            {{-- Observations --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Observations
                    <span class="text-gray-400 font-normal normal-case">(facultatif)</span>
                </label>
                <textarea name="observations" rows="3"
                          placeholder="Conditions particulières, remarques, restrictions..."
                          class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors resize-none">{{ old('observations') }}</textarea>
            </div>

        </div>

        <div class="flex items-center gap-3 mt-5">
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl text-white text-sm font-bold shadow transition-all hover:opacity-90"
                    style="background-color: #F97316;">
                Créer l'agrément
            </button>
            <a href="{{ route('admin.agrements.index') }}"
               class="px-6 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
                Annuler
            </a>
        </div>

    </form>
</div>

@endsection
