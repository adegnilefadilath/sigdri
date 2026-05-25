@extends('layouts.app')

@section('titre', 'Agréments industriels')
@section('sous_titre', 'Liste et suivi de tous les agréments délivrés')

@section('contenu')

{{-- ── En-tête : onglets de filtrage rapide + bouton nouveau ───────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-4 mb-5">

    {{-- Onglets compteurs --}}
    <div class="flex items-center gap-2 flex-wrap">
        @php
            $filtres = [
                ''         => ['label' => 'Tous',      'count' => $compteurs['total'],    'color' => 'bg-gray-100 text-gray-700'],
                'valide'   => ['label' => 'Valides',   'count' => $compteurs['valide'],   'color' => 'bg-green-100 text-green-700'],
                'expire'   => ['label' => 'Expirés',   'count' => $compteurs['expire'],   'color' => 'bg-red-100 text-red-700'],
                'suspendu' => ['label' => 'Suspendus', 'count' => $compteurs['suspendu'], 'color' => 'bg-yellow-100 text-yellow-700'],
            ];
        @endphp
        @foreach ($filtres as $val => $info)
            <a href="{{ route('admin.agrements.index', array_merge(request()->except('statut','page'), $val ? ['statut' => $val] : [])) }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition-all
                      {{ request('statut', '') === $val ? $info['color'] . ' ring-2 ring-offset-1 ring-current' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                {{ $info['label'] }}
                <span class="font-bold">{{ $info['count'] }}</span>
            </a>
        @endforeach
    </div>

    <a href="{{ route('admin.agrements.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-white text-sm font-bold shadow transition-all hover:opacity-90"
       style="background-color: #F97316;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvel agrément
    </a>
</div>

{{-- ── Filtre expiration ────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('admin.agrements.index') }}"
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    {{-- Conserver le filtre statut si actif --}}
    @if (request('statut'))
        <input type="hidden" name="statut" value="{{ request('statut') }}">
    @endif
    <div class="flex flex-wrap gap-3 items-end">
        <div class="min-w-[200px]">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Expiration</label>
            <select name="expiration"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#1a237e]">
                <option value="">Toutes les dates</option>
                <option value="bientot" {{ request('expiration') === 'bientot' ? 'selected' : '' }}>
                    Expire dans 30 jours
                </option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 text-sm font-semibold rounded-lg text-white transition-all hover:opacity-90"
                    style="background-color: #1a237e;">
                Filtrer
            </button>
            @if (request()->hasAny(['expiration','statut']))
                <a href="{{ route('admin.agrements.index') }}"
                   class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">
                    Réinitialiser
                </a>
            @endif
        </div>
    </div>
</form>

{{-- ── Tableau ──────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-100 text-sm">
        <thead>
            <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wide text-gray-500">
                <th class="px-5 py-3 text-left">N° Agrément</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Unité industrielle</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Type</th>
                <th class="px-5 py-3 text-left hidden lg:table-cell">Délivré le</th>
                <th class="px-5 py-3 text-left hidden md:table-cell">Expire le</th>
                <th class="px-5 py-3 text-center">Statut</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($agrements as $agrement)
                @php
                    $badge = match($agrement->statut) {
                        'valide'   => 'bg-green-100 text-green-700',
                        'expire'   => 'bg-red-100 text-red-700',
                        'suspendu' => 'bg-yellow-100 text-yellow-700',
                        'revoque'  => 'bg-purple-100 text-purple-700',
                        default    => 'bg-gray-100 text-gray-500',
                    };
                    $libelle = match($agrement->statut) {
                        'valide'   => 'Valide',
                        'expire'   => 'Expiré',
                        'suspendu' => 'Suspendu',
                        'revoque'  => 'Révoqué',
                        default    => ucfirst($agrement->statut),
                    };
                    // Signal visuel pour les agréments proches de l'expiration (< 30j)
                    $expireBientot = $agrement->date_expiration
                        && $agrement->statut === 'valide'
                        && now()->diffInDays($agrement->date_expiration, false) <= 30
                        && now()->diffInDays($agrement->date_expiration, false) >= 0;
                @endphp
                <tr class="hover:bg-gray-50 transition-colors {{ $expireBientot ? 'bg-yellow-50/40' : '' }}">
                    <td class="px-5 py-3.5">
                        <p class="font-mono text-xs font-bold text-gray-800">{{ $agrement->numero_agrement }}</p>
                        <p class="text-xs text-gray-400 mt-0.5 md:hidden">{{ $agrement->denomination_unite }}</p>
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell text-gray-700 max-w-[200px] truncate">
                        {{ $agrement->denomination_unite }}
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-gray-600">
                        {{ $agrement->type_agrement }}
                    </td>
                    <td class="px-5 py-3.5 hidden lg:table-cell text-gray-600">
                        {{ \Carbon\Carbon::parse($agrement->date_delivrance)->format('d/m/Y') }}
                    </td>
                    <td class="px-5 py-3.5 hidden md:table-cell">
                        @if ($agrement->date_expiration)
                            <span class="{{ $expireBientot ? 'text-orange-600 font-semibold' : 'text-gray-600' }}">
                                {{ \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y') }}
                            </span>
                            @if ($expireBientot)
                                <p class="text-xs text-orange-500">Expire bientôt</p>
                            @endif
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                            {{ $libelle }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.agrements.show', $agrement->id) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                               title="Voir le détail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.agrements.edit', $agrement->id) }}"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-orange-500 hover:bg-orange-50 transition-colors"
                               title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <p class="text-sm">Aucun agrément trouvé.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($agrements->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">
            {{ $agrements->links() }}
        </div>
    @endif
</div>

@endsection
