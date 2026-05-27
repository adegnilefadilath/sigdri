@extends('layouts.app')

@section('titre', 'Catalogue produits')
@section('sous_titre', 'Produits finis enregistrés par unité industrielle')

@section('contenu')

{{-- ── Statistiques rapides ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Total produits</p>
        <p class="text-3xl font-black text-gray-900">{{ number_format($total) }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Produits actifs</p>
        <p class="text-3xl font-black" style="color:#1a237e;">{{ number_format($totalActifs) }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Inactifs</p>
        <p class="text-3xl font-black text-gray-400">{{ number_format($total - $totalActifs) }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <a href="{{ route('admin.matieres.index') }}"
           class="block h-full group">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1 group-hover:text-[#F97316] transition-colors">
                Matières premières
            </p>
            <p class="text-sm font-bold mt-2 transition-colors" style="color:#1a237e;">
                Voir le catalogue →
            </p>
        </a>
    </div>

</div>

{{-- ── Filtres + bouton ajout ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5">
    <form method="GET" action="{{ route('admin.produits.index') }}"
          class="flex flex-wrap items-end gap-3">

        {{-- Filtre secteur --}}
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Secteur</label>
            <input type="text" name="secteur" value="{{ request('secteur') }}"
                   placeholder="Agro-alimentaire, textile…"
                   class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color: #1a237e;">
        </div>

        {{-- Filtre unité --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Unité industrielle</label>
            <select name="unite_id"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color: #1a237e;">
                <option value="">Toutes</option>
                @foreach ($unites as $u)
                <option value="{{ $u->id }}" {{ request('unite_id') == $u->id ? 'selected' : '' }}>
                    {{ $u->denomination }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- Filtre statut --}}
        <div class="min-w-[130px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Statut</label>
            <select name="statut"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color: #1a237e;">
                <option value="">Tous</option>
                <option value="actif"   {{ request('statut') === 'actif'   ? 'selected' : '' }}>Actif</option>
                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>

        <button type="submit"
                class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition-opacity hover:opacity-90"
                style="background-color: #1a237e;">
            Filtrer
        </button>

        @if (request()->hasAny(['secteur', 'unite_id', 'statut']))
        <a href="{{ route('admin.produits.index') }}"
           class="px-4 py-2 text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
            Réinitialiser
        </a>
        @endif

        <div class="ml-auto">
            <a href="{{ route('admin.produits.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
               style="background-color: #F97316;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter un produit
            </a>
        </div>
    </form>
</div>

{{-- ── Tableau des produits ────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Désignation</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Unité industrielle</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Code produit</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Unité mesure</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Statut</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($produits as $p)
                <tr class="hover:bg-gray-50/60 transition-colors">

                    {{-- Désignation + description --}}
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-900">{{ $p->designation }}</p>
                        @if ($p->description)
                        <p class="text-xs text-gray-400 mt-0.5 line-clamp-1">{{ $p->description }}</p>
                        @endif
                    </td>

                    {{-- Unité industrielle --}}
                    <td class="px-5 py-3.5">
                        <p class="text-gray-700 font-medium">{{ $p->denomination_unite }}</p>
                        <p class="text-xs text-gray-400">{{ $p->secteur }}</p>
                    </td>

                    {{-- Code produit --}}
                    <td class="px-5 py-3.5">
                        <span class="font-mono text-xs text-gray-500">
                            {{ $p->code_produit ?? '—' }}
                        </span>
                    </td>

                    {{-- Unité de mesure --}}
                    <td class="px-5 py-3.5 text-gray-600 text-xs">{{ $p->unite_mesure }}</td>

                    {{-- Statut --}}
                    <td class="px-5 py-3.5">
                        @if ($p->actif)
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Actif
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            Inactif
                        </span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">

                            {{-- Modifier --}}
                            <a href="{{ route('admin.produits.edit', $p->id) }}"
                               class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                      border-gray-300 text-gray-600 hover:bg-gray-50">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifier
                            </a>

                            {{-- Désactiver (seulement si actif) --}}
                            @if ($p->actif)
                            <form method="POST" action="{{ route('admin.produits.destroy', $p->id) }}"
                                  onsubmit="return confirm('Désactiver « {{ addslashes($p->designation) }} » ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                               border-red-200 text-red-600 hover:bg-red-50">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Désactiver
                                </button>
                            </form>
                            @endif

                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Aucun produit trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($produits->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $produits->links() }}
    </div>
    @endif

</div>

@endsection
