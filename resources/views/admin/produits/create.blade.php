@extends('layouts.app')

@section('titre', 'Nouveau produit')
@section('sous_titre', 'Ajouter un produit au catalogue industriel')

@section('contenu')

<div class="max-w-2xl">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800">Informations du produit</h3>
            <p class="text-xs text-gray-400 mt-0.5">
                Les champs marqués <span class="text-red-500">*</span> sont obligatoires.
            </p>
        </div>

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

        <form method="POST" action="{{ route('admin.produits.store') }}" class="p-6 space-y-4">
            @csrf

            {{-- Unité industrielle --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Unité industrielle <span class="text-red-500">*</span>
                </label>
                <select name="unite_industrielle_id"
                        class="w-full px-3 py-2.5 text-sm rounded-xl border
                               {{ $errors->has('unite_industrielle_id') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                               focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                    <option value="">— Sélectionner une unité —</option>
                    @foreach ($unites as $u)
                    <option value="{{ $u->id }}"
                            {{ old('unite_industrielle_id') == $u->id ? 'selected' : '' }}>
                        {{ $u->denomination }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Désignation --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Désignation du produit <span class="text-red-500">*</span>
                </label>
                <input type="text" name="designation"
                       value="{{ old('designation') }}"
                       placeholder="Ex : Huile de palme raffinée, Tissu bogolan…"
                       class="w-full px-3 py-2.5 text-sm rounded-xl border
                              {{ $errors->has('designation') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                              focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                {{-- Code produit --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Code produit <span class="text-gray-400 font-normal normal-case">(optionnel)</span>
                    </label>
                    <input type="text" name="code_produit"
                           value="{{ old('code_produit') }}"
                           placeholder="Ex : SH 1511, PROD-001…"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border
                                  {{ $errors->has('code_produit') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

                {{-- Unité de mesure --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Unité de mesure <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="unite_mesure"
                           value="{{ old('unite_mesure') }}"
                           placeholder="Ex : tonne, litre, m², pièce…"
                           class="w-full px-3 py-2.5 text-sm rounded-xl border
                                  {{ $errors->has('unite_mesure') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                  focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors">
                </div>

            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                    Description <span class="text-gray-400 font-normal normal-case">(optionnelle)</span>
                </label>
                <textarea name="description" rows="3"
                          placeholder="Description technique ou commerciale du produit…"
                          class="w-full px-3 py-2.5 text-sm rounded-xl border
                                 {{ $errors->has('description') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }}
                                 focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent transition-colors resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Boutons --}}
            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                        style="background-color: #1a237e;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer le produit
                </button>
                <a href="{{ route('admin.produits.index') }}"
                   class="px-5 py-2.5 rounded-xl text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
                    Annuler
                </a>
            </div>

        </form>
    </div>

</div>

@endsection
