@extends('layouts.app')

@section('titre', $unite->denomination)
@section('sous_titre', 'Fiche détaillée de l\'unité industrielle')

@section('contenu')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ══════════════════════════════════════════════════════════════════════
        COLONNE PRINCIPALE (2/3)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- ── Fiche identité ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <div class="flex items-start justify-between mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                         style="background: rgba(26,35,126,0.08);">
                        <svg class="w-6 h-6" fill="none" stroke="#1a237e" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ $unite->denomination }}</h2>
                        <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $unite->numero_immatriculation }}</p>
                    </div>
                </div>
                @if ($unite->actif)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Active
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactive
                    </span>
                @endif
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Secteur d'activité</dt>
                    <dd class="mt-1 font-medium text-gray-800">{{ $unite->secteur_activite }}</dd>
                </div>
                @if ($unite->regime)
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Régime</dt>
                    <dd class="mt-1 font-medium text-gray-800">{{ $unite->regime }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Adresse</dt>
                    <dd class="mt-1 text-gray-800">{{ $unite->adresse }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Localisation</dt>
                    <dd class="mt-1 text-gray-800">{{ $unite->commune }}, {{ $unite->departement }}</dd>
                </div>
                @if ($unite->telephone)
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Téléphone</dt>
                    <dd class="mt-1 text-gray-800">{{ $unite->telephone }}</dd>
                </div>
                @endif
                @if ($unite->email)
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">E-mail</dt>
                    <dd class="mt-1 text-gray-800">{{ $unite->email }}</dd>
                </div>
                @endif
                @if ($unite->responsable_nom)
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Responsable</dt>
                    <dd class="mt-1 text-gray-800">
                        {{ $unite->responsable_nom }}
                        @if ($unite->responsable_fonction)
                            <span class="text-gray-400 text-xs">({{ $unite->responsable_fonction }})</span>
                        @endif
                    </dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Enregistrée le</dt>
                    <dd class="mt-1 text-gray-800">
                        {{ \Carbon\Carbon::parse($unite->created_at)->format('d/m/Y') }}
                    </dd>
                </div>
            </dl>
        </div>

        {{-- ── Agréments de l'unité ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Agréments</h3>
                <a href="{{ route('admin.agrements.create', ['unite_id' => $unite->id]) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition-all hover:opacity-90"
                   style="background-color: #F97316;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Nouvel agrément
                </a>
            </div>

            @if ($agrements->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400">
                    <p class="text-sm">Aucun agrément pour cette unité.</p>
                </div>
            @else
                <table class="min-w-full divide-y divide-gray-50 text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-bold uppercase tracking-wide text-gray-400">
                            <th class="px-5 py-3 text-left">N° Agrément</th>
                            <th class="px-5 py-3 text-left hidden sm:table-cell">Type</th>
                            <th class="px-5 py-3 text-left hidden md:table-cell">Expiration</th>
                            <th class="px-5 py-3 text-center">Statut</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($agrements as $agrement)
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
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3 font-mono text-xs font-semibold text-gray-700">
                                    {{ $agrement->numero_agrement }}
                                </td>
                                <td class="px-5 py-3 hidden sm:table-cell text-gray-600">
                                    {{ $agrement->type_agrement }}
                                </td>
                                <td class="px-5 py-3 hidden md:table-cell text-gray-600">
                                    {{ $agrement->date_expiration
                                        ? \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y')
                                        : '—' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $libelle }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('admin.agrements.show', $agrement->id) }}"
                                       class="text-xs font-semibold hover:underline"
                                       style="color: #1a237e;">Voir →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- ── Comptes utilisateurs liés ───────────────────────────────── --}}
        @if ($comptes->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-bold text-gray-800 mb-3">Comptes industriels liés</h3>
            <div class="space-y-2">
                @foreach ($comptes as $compte)
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-gray-50">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $compte->prenom }} {{ $compte->nom }}</p>
                            <p class="text-xs text-gray-400">{{ $compte->email }}</p>
                        </div>
                        <span class="text-xs {{ $compte->actif ? 'text-green-600' : 'text-gray-400' }} font-semibold">
                            {{ $compte->actif ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
        COLONNE ACTIONS (1/3)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Actions</h3>
            <div class="space-y-2">

                <a href="{{ route('admin.unites.edit', $unite->id) }}"
                   class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier la fiche
                </a>

                <a href="{{ route('admin.agrements.create', ['unite_id' => $unite->id]) }}"
                   class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                   style="background-color: #F97316;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Créer un agrément
                </a>

                @if ($unite->actif)
                    <form method="POST" action="{{ route('admin.unites.destroy', $unite->id) }}"
                          onsubmit="return confirm('Désactiver cette unité industrielle ?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Désactiver l'unité
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- Méta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-xs text-gray-400 space-y-2">
            <div class="flex justify-between">
                <span>Créée le</span>
                <span class="font-medium text-gray-600">
                    {{ \Carbon\Carbon::parse($unite->created_at)->format('d/m/Y') }}
                </span>
            </div>
            <div class="flex justify-between">
                <span>Modifiée le</span>
                <span class="font-medium text-gray-600">
                    {{ \Carbon\Carbon::parse($unite->updated_at)->format('d/m/Y') }}
                </span>
            </div>
            <div class="flex justify-between">
                <span>Nb agréments</span>
                <span class="font-bold text-gray-700">{{ $agrements->count() }}</span>
            </div>
        </div>

        <a href="{{ route('admin.unites.index') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors px-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste
        </a>

    </div>

</div>

@endsection
