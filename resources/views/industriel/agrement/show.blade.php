@extends('layouts.industriel')

@section('titre', 'Mon agrément')
@section('sous_titre', 'Consultation de votre agrément d\'exploitation — lecture seule')

@section('contenu')

@if ($agrement)

    @php
        $badge = match($agrement->statut) {
            'valide'   => ['bg-green-100 text-green-700',   'border-green-200', 'Valide'],
            'expire'   => ['bg-red-100 text-red-700',       'border-red-200',   'Expiré'],
            'suspendu' => ['bg-yellow-100 text-yellow-700', 'border-yellow-200','Suspendu'],
            'revoque'  => ['bg-purple-100 text-purple-700', 'border-purple-200','Révoqué'],
            default    => ['bg-gray-100 text-gray-500',     'border-gray-200',  ucfirst($agrement->statut)],
        };
        $jours = $agrement->date_expiration
            ? now()->diffInDays($agrement->date_expiration, false)
            : null;
    @endphp

    {{-- ── Bannière alerte selon statut ──────────────────────────────────── --}}
    @if ($agrement->statut === 'expire')
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            <div>
                <p class="font-bold">Votre agrément est expiré.</p>
                <p class="text-xs mt-0.5">Veuillez contacter le Ministère de l'Industrie pour un renouvellement.</p>
            </div>
        </div>
    @elseif ($agrement->statut === 'suspendu')
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-yellow-50 border border-yellow-200 rounded-xl text-sm text-yellow-700">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Votre agrément est suspendu.</p>
                <p class="text-xs mt-0.5">Contactez le Ministère de l'Industrie pour plus d'informations.</p>
            </div>
        </div>
    @elseif ($jours !== null && $jours <= 30 && $jours >= 0)
        <div class="mb-5 flex items-center gap-3 px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl text-sm text-orange-700">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-bold">Votre agrément expire dans {{ $jours }} jour(s).</p>
                <p class="text-xs mt-0.5">Pensez à anticiper son renouvellement auprès du Ministère.</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- ── Carte principale ───────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

                {{-- En-tête de la carte --}}
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                            Numéro d'agrément
                        </p>
                        <h2 class="font-mono text-2xl font-black text-gray-900">
                            {{ $agrement->numero_agrement }}
                        </h2>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $badge[0] }}">
                        {{ $badge[2] }}
                    </span>
                </div>

                {{-- Détails --}}
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-5 text-sm">

                    <div>
                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Type d'agrément</dt>
                        <dd class="mt-1.5 font-semibold text-gray-800">{{ $agrement->type_agrement }}</dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Statut</dt>
                        <dd class="mt-1.5">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge[0] }}">
                                {{ $badge[2] }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date de délivrance</dt>
                        <dd class="mt-1.5 font-medium text-gray-800">
                            {{ \Carbon\Carbon::parse($agrement->date_delivrance)->translatedFormat('d F Y') }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date d'expiration</dt>
                        <dd class="mt-1.5">
                            @if ($agrement->date_expiration)
                                <span class="font-medium {{ $jours < 0 ? 'text-red-600' : ($jours <= 30 ? 'text-orange-500' : 'text-gray-800') }}">
                                    {{ \Carbon\Carbon::parse($agrement->date_expiration)->translatedFormat('d F Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">Durée indéterminée</span>
                            @endif
                        </dd>
                    </div>

                    @if ($agrement->observations)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Observations</dt>
                        <dd class="mt-1.5 text-gray-700 bg-gray-50 rounded-lg p-3 text-sm leading-relaxed">
                            {{ $agrement->observations }}
                        </dd>
                    </div>
                    @endif

                </dl>

                {{-- Mention lecture seule --}}
                <div class="mt-6 pt-4 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Consultation uniquement. Pour toute modification, contactez le Ministère de l'Industrie.
                </div>

            </div>
        </div>

        {{-- ── Colonne latérale : unité industrielle ──────────────────────── --}}
        <div class="space-y-4">

            @if ($unite)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Unité industrielle</h3>
                <div class="space-y-2 text-sm">
                    <p class="font-bold text-gray-800">{{ $unite->denomination }}</p>
                    <p class="text-xs font-mono text-gray-500">{{ $unite->numero_immatriculation }}</p>
                    <p class="text-xs text-gray-500">{{ $unite->commune }}, {{ $unite->departement }}</p>
                    @if ($unite->responsable_nom)
                        <div class="pt-2 border-t border-gray-100">
                            <p class="text-xs text-gray-400">Responsable</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $unite->responsable_nom }}</p>
                            @if ($unite->responsable_fonction)
                                <p class="text-xs text-gray-400">{{ $unite->responsable_fonction }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Durée de validité --}}
            @if ($agrement->date_delivrance && $agrement->date_expiration)
            @php
                $dureeAns = \Carbon\Carbon::parse($agrement->date_delivrance)
                    ->diffInYears($agrement->date_expiration);
            @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Durée de validité</h3>
                <p class="text-2xl font-black text-gray-800">{{ $dureeAns }} an(s)</p>
                <p class="text-xs text-gray-400 mt-1">
                    du {{ \Carbon\Carbon::parse($agrement->date_delivrance)->format('d/m/Y') }}
                    au {{ \Carbon\Carbon::parse($agrement->date_expiration)->format('d/m/Y') }}
                </p>
            </div>
            @endif

        </div>

    </div>

@else

    {{-- ── État vide : aucun agrément ─────────────────────────────────────── --}}
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
             style="background: rgba(26,35,126,0.07);">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h2 class="text-lg font-bold text-gray-700 mb-1">Aucun agrément enregistré</h2>
        <p class="text-sm text-gray-400 max-w-sm">
            Votre unité industrielle ne possède pas encore d'agrément dans le système.
            Contactez le Ministère de l'Industrie pour en faire la demande.
        </p>
    </div>

@endif

@endsection
