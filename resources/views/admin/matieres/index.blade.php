@extends('layouts.app')

@section('titre', 'Matières premières')
@section('sous_titre', 'Consommations déclarées par les unités industrielles')

@section('contenu')

{{-- ── Statistiques globales ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Matières distinctes</p>
        <p class="text-3xl font-black text-gray-900">
            {{ number_format($stats->nb_matieres_distinctes ?? 0) }}
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Valeur totale (FCFA)</p>
        <p class="text-2xl font-black" style="color:#1a237e;">
            {{ number_format($stats->valeur_totale ?? 0, 0, ',', ' ') }}
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Locales</p>
        <p class="text-3xl font-black text-green-600">
            {{ number_format($stats->nb_locale ?? 0) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">entrées de déclarations</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-1">Importées</p>
        <p class="text-3xl font-black text-amber-600">
            {{ number_format($stats->nb_importee ?? 0) }}
        </p>
        <p class="text-xs text-gray-400 mt-0.5">entrées de déclarations</p>
    </div>

</div>

{{-- ── Légende disponibilité --}}
<div class="mb-4 flex flex-wrap items-center gap-3 text-xs text-gray-500">
    <span class="font-semibold">Disponibilité :</span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
        <span>Disponible — dernière déclaration &lt; 90 j</span>
    </span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
        <span>Tension — 90 à 180 j</span>
    </span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
        <span>Rupture — &gt; 180 j</span>
    </span>
    <span class="inline-flex items-center gap-1.5">
        <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span>
        <span>Inconnu</span>
    </span>
</div>

{{-- ── Filtres ─────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 mb-5">
    <form method="GET" action="{{ route('admin.matieres.index') }}"
          class="flex flex-wrap items-end gap-3">

        {{-- Recherche libre --}}
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Désignation</label>
            <input type="text" name="recherche" value="{{ request('recherche') }}"
                   placeholder="Rechercher une matière…"
                   class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                   style="--tw-ring-color:#1a237e;">
        </div>

        {{-- Filtre origine --}}
        <div class="min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Origine</label>
            <select name="origine"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color:#1a237e;">
                <option value="">Toutes</option>
                <option value="locale"   {{ request('origine') === 'locale'   ? 'selected' : '' }}>Locale</option>
                <option value="importee" {{ request('origine') === 'importee' ? 'selected' : '' }}>Importée</option>
            </select>
        </div>

        {{-- Filtre disponibilité --}}
        <div class="min-w-[140px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Disponibilité</label>
            <select name="disponibilite"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color:#1a237e;">
                <option value="">Toutes</option>
                <option value="Disponible" {{ request('disponibilite') === 'Disponible' ? 'selected' : '' }}>Disponible</option>
                <option value="Tension"    {{ request('disponibilite') === 'Tension'    ? 'selected' : '' }}>Tension</option>
                <option value="Rupture"    {{ request('disponibilite') === 'Rupture'    ? 'selected' : '' }}>Rupture</option>
            </select>
        </div>

        <button type="submit"
                class="px-4 py-2 text-sm font-semibold rounded-xl text-white transition-opacity hover:opacity-90"
                style="background-color: #1a237e;">
            Filtrer
        </button>

        @if (request()->hasAny(['recherche', 'origine', 'disponibilite']))
        <a href="{{ route('admin.matieres.index') }}"
           class="px-4 py-2 text-sm font-semibold rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition-colors">
            Réinitialiser
        </a>
        @endif

        {{-- Lien vers catalogue produits --}}
        <div class="ml-auto">
            <a href="{{ route('admin.produits.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border transition-colors"
               style="border-color: #1a237e; color: #1a237e;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Catalogue produits
            </a>
        </div>
    </form>
</div>

{{-- ── Tableau des matières agrégées ──────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70">
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Désignation</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Origine</th>
                    <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-widest text-gray-400">Disponibilité</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Qté totale</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Valeur FCFA</th>
                    <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-widest text-gray-400">Déclarations</th>
                    <th class="px-5 py-3 text-center text-xs font-bold uppercase tracking-widest text-gray-400">Détail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse ($matieres as $m)

                @php
                    $disponConfig = match($m->disponibilite) {
                        'Disponible' => ['dot' => 'bg-green-500',  'bg' => 'bg-green-100',  'text' => 'text-green-700'],
                        'Tension'    => ['dot' => 'bg-amber-400',  'bg' => 'bg-amber-100',  'text' => 'text-amber-700'],
                        'Rupture'    => ['dot' => 'bg-red-500',    'bg' => 'bg-red-100',    'text' => 'text-red-700'],
                        default      => ['dot' => 'bg-gray-300',   'bg' => 'bg-gray-100',   'text' => 'text-gray-500'],
                    };
                @endphp

                <tr class="hover:bg-gray-50/60 transition-colors">

                    {{-- Désignation --}}
                    <td class="px-5 py-3.5">
                        <p class="font-semibold text-gray-900">{{ $m->designation }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $m->unites_mesure }}</p>
                    </td>

                    {{-- Origine --}}
                    <td class="px-5 py-3.5">
                        @if ($m->origine === 'locale')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">Locale</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">Importée</span>
                        @endif
                    </td>

                    {{-- Disponibilité --}}
                    <td class="px-5 py-3.5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold
                                     {{ $disponConfig['bg'] }} {{ $disponConfig['text'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $disponConfig['dot'] }}"></span>
                            {{ $m->disponibilite }}
                        </span>
                        @if ($m->derniere_utilisation)
                        <p class="text-[10px] text-gray-400 mt-1">
                            {{ \Carbon\Carbon::parse($m->derniere_utilisation)->format('d/m/Y') }}
                        </p>
                        @endif
                    </td>

                    {{-- Quantité totale --}}
                    <td class="px-5 py-3.5 text-right font-mono text-sm text-gray-700">
                        {{ number_format($m->quantite_totale, 2, ',', ' ') }}
                    </td>

                    {{-- Valeur FCFA --}}
                    <td class="px-5 py-3.5 text-right font-mono text-sm text-gray-700">
                        {{ number_format($m->valeur_totale, 0, ',', ' ') }}
                    </td>

                    {{-- Nb déclarations --}}
                    <td class="px-5 py-3.5 text-right">
                        <span class="text-sm font-bold text-gray-700">{{ $m->nb_declarations }}</span>
                    </td>

                    {{-- Lien détail --}}
                    <td class="px-5 py-3.5 text-center">
                        <a href="{{ route('admin.matieres.show', urlencode($m->designation)) }}"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors
                                  border-gray-300 text-gray-600 hover:bg-gray-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Voir
                        </a>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400 text-sm">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                        </svg>
                        Aucune matière première trouvée dans les déclarations.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($matieres->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">
        {{ $matieres->links() }}
    </div>
    @endif

</div>

@endsection
