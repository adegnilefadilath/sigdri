@extends('layouts.app')

@section('titre', 'Alertes')
@section('sous_titre', 'Surveillance des agréments et des déclarations')

@section('contenu')

{{-- ── Cartes compteurs ────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total alertes actives --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-500">Alertes actives</p>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background:rgba(229,62,62,0.08);">
                <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="#e53e3e" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $compteurs['total'] }}</p>
    </div>

    {{-- Agréments expirant bientôt --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-500">Expirent bientôt</p>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background:rgba(249,115,22,0.08);">
                <svg class="w-5 h-5" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $compteurs['expirant_bientot'] }}</p>
    </div>

    {{-- Agréments expirés --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-500">Agréments expirés</p>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background:rgba(229,62,62,0.08);">
                <svg class="w-5 h-5" fill="none" stroke="#e53e3e" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $compteurs['expires'] }}</p>
    </div>

    {{-- Déclarations en attente --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-gray-500">Décl. en attente</p>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                 style="background:rgba(214,158,46,0.08);">
                <svg class="w-5 h-5" fill="none" stroke="#d69e2e" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
        <p class="text-3xl font-bold text-gray-900">{{ $compteurs['declarations_attente'] }}</p>
    </div>

</div>

{{-- ════════════════════════════════════════════════════════════════════════
     SECTION 1 — Agréments expirant dans les 30 prochains jours
════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-5">

    {{-- En-tête de section --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full" style="background:#F97316;"></div>
            <h2 class="font-semibold text-gray-800">Agréments expirant dans les 30 prochains jours</h2>
            @if ($compteurs['expirant_bientot'] > 0)
                <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white"
                      style="background:#F97316;">
                    {{ $compteurs['expirant_bientot'] }}
                </span>
            @endif
        </div>
        <a href="{{ route('admin.agrements.index', ['expiration' => 'bientot']) }}"
           class="text-xs font-medium hover:underline" style="color:#1a237e;">
            Voir tous les agréments →
        </a>
    </div>

    {{-- Contenu --}}
    @if ($expirantBientot->isEmpty())
        <div class="px-5 py-8 text-center text-sm text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aucun agrément n'expire dans les 30 prochains jours.
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach ($expirantBientot as $a)
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-orange-50/40 transition-colors">

                    {{-- Informations de l'alerte --}}
                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Badge jours restants --}}
                        <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold"
                              style="background:#fff3e0;color:#c05621;">
                            {{ $a->jours_restants }}j
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ $a->denomination_unite }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $a->numero_agrement }} — {{ $a->type_agrement }}
                                &nbsp;·&nbsp; {{ $a->departement_unite }}
                                &nbsp;·&nbsp; Expire le
                                <strong>{{ \Carbon\Carbon::parse($a->date_expiration)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        <a href="{{ route('admin.agrements.show', $a->id) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Voir
                        </a>
                        <form method="POST" action="{{ route('admin.alertes.traiter', $a->id) }}">
                            @csrf
                            <input type="hidden" name="type" value="agrement_expirant">
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition-colors"
                                    style="background:#F97316;"
                                    onclick="return confirm('Marquer cette alerte comme traitée ?')">
                                Marquer traité
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     SECTION 2 — Agréments déjà expirés
════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-5">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full" style="background:#e53e3e;"></div>
            <h2 class="font-semibold text-gray-800">Agréments expirés</h2>
            @if ($compteurs['expires'] > 0)
                <span class="px-2 py-0.5 rounded-full text-xs font-bold text-white"
                      style="background:#e53e3e;">
                    {{ $compteurs['expires'] }}
                </span>
            @endif
        </div>
        <a href="{{ route('admin.agrements.index', ['statut' => 'expire']) }}"
           class="text-xs font-medium hover:underline" style="color:#1a237e;">
            Voir tous les agréments →
        </a>
    </div>

    @if ($expires->isEmpty())
        <div class="px-5 py-8 text-center text-sm text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aucun agrément expiré non traité.
        </div>
    @else
        <div class="divide-y divide-gray-50">
            @foreach ($expires as $a)
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-red-50/40 transition-colors">

                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Badge jours écoulés --}}
                        <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold"
                              style="background:#fff5f5;color:#c53030;">
                            +{{ $a->jours_ecoules }}j
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ $a->denomination_unite }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $a->numero_agrement }} — {{ $a->type_agrement }}
                                &nbsp;·&nbsp; {{ $a->departement_unite }}
                                &nbsp;·&nbsp; Expiré le
                                <strong>{{ \Carbon\Carbon::parse($a->date_expiration)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        <a href="{{ route('admin.agrements.show', $a->id) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Voir
                        </a>
                        <form method="POST" action="{{ route('admin.alertes.traiter', $a->id) }}">
                            @csrf
                            <input type="hidden" name="type" value="agrement_expire">
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition-colors"
                                    style="background:#e53e3e;"
                                    onclick="return confirm('Marquer cette alerte comme traitée ?')">
                                Marquer traité
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ════════════════════════════════════════════════════════════════════════
     SECTION 3 — Déclarations soumises en attente depuis plus de 7 jours
════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100">

    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-2 h-2 rounded-full" style="background:#d69e2e;"></div>
            <h2 class="font-semibold text-gray-800">Déclarations en attente depuis plus de 7 jours</h2>
            @if ($compteurs['declarations_attente'] > 0)
                <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                      style="background:#fefcbf;color:#744210;">
                    {{ $compteurs['declarations_attente'] }}
                </span>
            @endif
        </div>
        <a href="{{ route('admin.declarations.index', ['statut' => 'soumise']) }}"
           class="text-xs font-medium hover:underline" style="color:#1a237e;">
            Voir toutes les déclarations →
        </a>
    </div>

    @if ($declarationsEnAttente->isEmpty())
        <div class="px-5 py-8 text-center text-sm text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aucune déclaration en attente prolongée.
        </div>
    @else
        @php
            $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                      'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
        @endphp
        <div class="divide-y divide-gray-50">
            @foreach ($declarationsEnAttente as $d)
                <div class="flex items-center justify-between px-5 py-3.5 hover:bg-yellow-50/40 transition-colors">

                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Badge jours d'attente --}}
                        <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold"
                              style="background:#fefcbf;color:#744210;">
                            {{ $d->jours_attente }}j
                        </span>

                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">
                                {{ $d->denomination_unite }}
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $d->numero_declaration }}
                                &nbsp;·&nbsp; {{ $nomsM[$d->mois] ?? $d->mois }} {{ $d->annee }}
                                &nbsp;·&nbsp; {{ $d->departement_unite }}
                                &nbsp;·&nbsp; Soumise le
                                <strong>{{ \Carbon\Carbon::parse($d->date_soumission)->format('d/m/Y') }}</strong>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 ml-4">
                        <a href="{{ route('admin.declarations.show', $d->id) }}"
                           class="px-3 py-1.5 rounded-lg text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
                            Traiter
                        </a>
                        <form method="POST" action="{{ route('admin.alertes.traiter', $d->id) }}">
                            @csrf
                            <input type="hidden" name="type" value="declaration_en_attente">
                            <button type="submit"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-white transition-colors"
                                    style="background:#d69e2e;"
                                    onclick="return confirm('Marquer cette alerte comme traitée ?')">
                                Marquer traité
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
