@extends('layouts.app')

@section('titre', 'Déclarations industrielles')
@section('sous_titre', 'Suivi et validation des déclarations soumises par les industriels')

@section('contenu')

@php
    // Tableau des noms de mois pour l'affichage
    $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
              'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

    $badgeStatut = fn (string $s) => match ($s) {
        'brouillon'   => 'bg-gray-100 text-gray-500',
        'soumise'     => 'bg-blue-100 text-blue-700',
        'en_revision' => 'bg-yellow-100 text-yellow-700',
        'validee'     => 'bg-green-100 text-green-700',
        'rejetee'     => 'bg-red-100 text-red-700',
        default       => 'bg-gray-100 text-gray-500',
    };
    $libelleStatut = fn (string $s) => match ($s) {
        'brouillon'   => 'Brouillon',
        'soumise'     => 'Soumise',
        'en_revision' => 'En révision',
        'validee'     => 'Validée',
        'rejetee'     => 'Rejetée',
        default       => ucfirst($s),
    };
@endphp

{{-- ── Compteurs rapides par statut ─────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
    @foreach (['brouillon','soumise','en_revision','validee','rejetee'] as $s)
    <a href="{{ route('admin.declarations.index', ['statut' => $s] + request()->except('statut','page')) }}"
       class="bg-white rounded-2xl border p-4 text-center hover:shadow-md transition-shadow
              {{ request('statut') === $s ? 'border-[#1a237e] shadow-md' : 'border-gray-100' }}">
        <p class="text-2xl font-black {{ request('statut') === $s ? 'text-[#1a237e]' : 'text-gray-800' }}">
            {{ $compteurs[$s] ?? 0 }}
        </p>
        <p class="text-xs font-semibold mt-0.5">
            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeStatut($s) }}">
                {{ $libelleStatut($s) }}
            </span>
        </p>
    </a>
    @endforeach
</div>

{{-- ── Filtres ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('admin.declarations.index') }}" class="flex flex-wrap gap-3 items-end">

        {{-- Mois --}}
        <div class="min-w-[130px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Mois</label>
            <select name="mois"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                <option value="">Tous</option>
                @foreach ($nomsM as $num => $nom)
                    @if ($num > 0)
                    <option value="{{ $num }}" {{ request('mois') == $num ? 'selected' : '' }}>{{ $nom }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- Année --}}
        <div class="min-w-[100px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Année</label>
            <select name="annee"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                <option value="">Toutes</option>
                @foreach ($annees as $a)
                    <option value="{{ $a }}" {{ request('annee') == $a ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        {{-- Statut --}}
        <div class="min-w-[140px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Statut</label>
            <select name="statut"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                <option value="">Tous</option>
                <option value="soumise"     {{ request('statut') === 'soumise'     ? 'selected' : '' }}>Soumise</option>
                <option value="en_revision" {{ request('statut') === 'en_revision' ? 'selected' : '' }}>En révision</option>
                <option value="validee"     {{ request('statut') === 'validee'     ? 'selected' : '' }}>Validée</option>
                <option value="rejetee"     {{ request('statut') === 'rejetee'     ? 'selected' : '' }}>Rejetée</option>
                <option value="brouillon"   {{ request('statut') === 'brouillon'   ? 'selected' : '' }}>Brouillon</option>
            </select>
        </div>

        {{-- Département --}}
        <div class="min-w-[150px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Département</label>
            <select name="departement"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50
                           focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
                <option value="">Tous</option>
                @foreach ($departements as $dept)
                    <option value="{{ $dept }}" {{ request('departement') === $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
        </div>

        {{-- Secteur --}}
        <div class="min-w-[160px]">
            <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Secteur</label>
            <input type="text" name="secteur" value="{{ request('secteur') }}" placeholder="ex : Agroalimentaire"
                   class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 bg-gray-50
                          focus:outline-none focus:ring-2 focus:ring-[#1a237e] focus:border-transparent">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-2 rounded-lg text-sm font-bold text-white transition-all hover:opacity-90"
                    style="background-color:#1a237e;">Filtrer</button>
            <a href="{{ route('admin.declarations.index') }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold border border-gray-300 text-gray-600 hover:bg-gray-50 transition-all">
               Réinitialiser
            </a>
            <a href="{{ route('admin.declarations.exporter', request()->all()) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-semibold border transition-all hover:opacity-90"
               style="border-color:#1a237e; color:#1a237e;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                CSV
            </a>
        </div>

    </form>
</div>

{{-- ── Tableau ────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="text-sm font-bold text-gray-800">{{ $declarations->total() }} déclaration(s)</h3>
    </div>

    @if ($declarations->isEmpty())
        <div class="py-14 text-center text-gray-400 text-sm">Aucune déclaration trouvée.</div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                        <th class="text-left px-5 py-3">N° Déclaration</th>
                        <th class="text-left px-5 py-3">Unité industrielle</th>
                        <th class="text-left px-5 py-3">Mois déclaré</th>
                        <th class="text-right px-5 py-3">CA (FCFA)</th>
                        <th class="text-left px-5 py-3">Soumise le</th>
                        <th class="text-center px-5 py-3">Statut</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($declarations as $decl)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                            {{ $decl->numero_declaration }}
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-semibold text-gray-800 truncate max-w-[200px]">{{ $decl->denomination_unite }}</p>
                            <p class="text-xs text-gray-400">{{ $decl->departement_unite }} — {{ $decl->secteur_activite }}</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs font-medium">
                            {{ $nomsM[$decl->mois] ?? '?' }} {{ $decl->annee }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-700 tabular-nums">
                            {{ number_format($decl->chiffre_affaires_total, 0, ',', ' ') }}
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            {{ $decl->date_soumission
                                ? \Carbon\Carbon::parse($decl->date_soumission)->format('d/m/Y')
                                : '—' }}
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badgeStatut($decl->statut) }}">
                                {{ $libelleStatut($decl->statut) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.declarations.show', $decl->id) }}"
                               class="text-xs font-semibold hover:underline" style="color:#1a237e;">
                                Voir →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($declarations->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $declarations->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
