@extends('layouts.app')

@section('titre', 'Unités industrielles')
@section('sous_titre', 'Référentiel des entreprises et unités industrielles enregistrées')

@section('contenu')

{{-- ── En-tête de page ──────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        {{-- Compteurs rapides --}}
        <span class="text-sm text-gray-500">
            <span class="font-bold text-gray-800">{{ $total }}</span> unité(s) au total ·
            <span class="font-bold text-green-600">{{ $totalActives }}</span> active(s)
        </span>
    </div>
    <a href="{{ route('admin.unites.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-bold shadow transition-all hover:opacity-90"
       style="background-color: #F97316;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvelle unité
    </a>
</div>

{{-- ── Barre de filtres ─────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.unites.index') }}"
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <div class="flex flex-wrap gap-3 items-end">

        {{-- Département --}}
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Département</label>
            <select name="departement"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e]">
                <option value="">Tous</option>
                @foreach ($departements as $dept)
                    <option value="{{ $dept }}" {{ request('departement') === $dept ? 'selected' : '' }}>
                        {{ $dept }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Secteur d'activité --}}
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Secteur d'activité</label>
            <input type="text" name="secteur" value="{{ request('secteur') }}"
                   placeholder="ex : agroalimentaire"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e]">
        </div>

        {{-- Statut --}}
        <div class="min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Statut</label>
            <select name="statut"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e]">
                <option value="">Tous</option>
                <option value="actif"   {{ request('statut') === 'actif'   ? 'selected' : '' }}>Active</option>
                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>

        {{-- Boutons --}}
        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 text-sm font-semibold rounded-lg text-white transition-all hover:opacity-90"
                    style="background-color: #1a237e;">
                Filtrer
            </button>
            @if (request()->hasAny(['departement', 'secteur', 'statut']))
                <a href="{{ route('admin.unites.index') }}"
                   class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
                    Réinitialiser
                </a>
            @endif
        </div>

    </div>
</form>

{{-- ── Tableau ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead>
            <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wide text-gray-500">
                <th class="px-5 py-3 text-left">Dénomination</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">N° Immatriculation</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Secteur</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Localisation</th>
                <th class="px-5 py-3 text-center">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($unites as $unite)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-900">{{ $unite->denomination }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 md:hidden">{{ $unite->numero_immatriculation }}</p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        <span class="font-mono text-xs text-gray-600">{{ $unite->numero_immatriculation }}</span>
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-gray-600">
                        {{ $unite->secteur_activite }}
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-gray-600">
                        {{ $unite->commune }}, {{ $unite->departement }}
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if ($unite->actif)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.unites.show', $unite->id) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Voir le détail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.unites.edit', $unite->id) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-orange-500 hover:bg-orange-50 transition-colors"
                               title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if ($unite->actif)
                                <form method="POST" action="{{ route('admin.unites.destroy', $unite->id) }}"
                                      onsubmit="return confirm('Désactiver l\'unité « {{ addslashes($unite->denomination) }} » ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Désactiver">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <p class="text-sm">Aucune unité industrielle trouvée.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    @if ($unites->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $unites->links() }}
        </div>
    @endif
</div>

@endsection
