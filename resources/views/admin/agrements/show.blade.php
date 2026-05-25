@extends('layouts.app')

@section('titre', $a->numero_agrement)
@section('sous_titre', 'Détail et gestion de l\'agrément')

@section('contenu')

@php
    $badge = match($a->statut) {
        'valide'   => ['bg-green-100 text-green-700',   'Valide'],
        'expire'   => ['bg-red-100 text-red-700',       'Expiré'],
        'suspendu' => ['bg-yellow-100 text-yellow-700', 'Suspendu'],
        'revoque'  => ['bg-purple-100 text-purple-700', 'Révoqué'],
        default    => ['bg-gray-100 text-gray-500',     ucfirst($a->statut)],
    };
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- ══════════════════════════════════════════════════════════════════════
        COLONNE PRINCIPALE (2/3)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- ── Fiche agrément ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

            <div class="flex items-start justify-between mb-5">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h2 class="font-mono text-xl font-bold text-gray-900">{{ $a->numero_agrement }}</h2>
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold {{ $badge[0] }}">
                            {{ $badge[1] }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500">{{ $a->type_agrement }}</p>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Unité industrielle</dt>
                    <dd class="mt-1">
                        <a href="{{ route('admin.unites.show', $a->unite_id) }}"
                           class="font-semibold hover:underline" style="color: #1a237e;">
                            {{ $a->denomination_unite }}
                        </a>
                        <p class="text-xs text-gray-400">{{ $a->departement_unite }}</p>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Type d'agrément</dt>
                    <dd class="mt-1 font-medium text-gray-800">{{ $a->type_agrement }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date de délivrance</dt>
                    <dd class="mt-1 font-medium text-gray-800">
                        {{ \Carbon\Carbon::parse($a->date_delivrance)->format('d/m/Y') }}
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Date d'expiration</dt>
                    <dd class="mt-1">
                        @if ($a->date_expiration)
                            @php
                                $jours = now()->diffInDays($a->date_expiration, false);
                            @endphp
                            <span class="font-medium {{ $jours < 0 ? 'text-red-600' : ($jours <= 30 ? 'text-orange-500' : 'text-gray-800') }}">
                                {{ \Carbon\Carbon::parse($a->date_expiration)->format('d/m/Y') }}
                            </span>
                            @if ($a->statut === 'valide')
                                @if ($jours < 0)
                                    <span class="ml-1 text-xs text-red-500">(dépassée)</span>
                                @elseif ($jours <= 30)
                                    <span class="ml-1 text-xs text-orange-500">(dans {{ $jours }} jour(s))</span>
                                @endif
                            @endif
                        @else
                            <span class="text-gray-400">Durée indéterminée</span>
                        @endif
                    </dd>
                </div>
                @if ($a->observations)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Observations</dt>
                    <dd class="mt-1 text-gray-700 bg-gray-50 rounded-lg p-3 text-sm">{{ $a->observations }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- ── Panneau suspension (visible seulement si statut != suspendu et != revoque) --}}
        @if (in_array($a->statut, ['valide', 'expire']))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-sm font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-yellow-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                Suspendre cet agrément
            </h3>

            {{-- Formulaire collapse/expand --}}
            <div id="form-suspension" class="hidden">
                <form method="POST" action="{{ route('admin.agrements.suspendre', $a->id) }}">
                    @csrf
                    @if ($errors->has('motif'))
                        <p class="text-xs text-red-600 mb-2">{{ $errors->first('motif') }}</p>
                    @endif
                    <textarea name="motif" rows="3"
                              placeholder="Motif de suspension (obligatoire, minimum 10 caractères)..."
                              class="w-full px-4 py-2.5 text-sm rounded-lg border {{ $errors->has('motif') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent resize-none mb-3">{{ old('motif') }}</textarea>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-bold rounded-lg text-white bg-yellow-500 hover:bg-yellow-600 transition-colors">
                            Confirmer la suspension
                        </button>
                        <button type="button" onclick="toggleSuspension()"
                                class="px-4 py-2 text-sm font-semibold rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>

            <div id="btn-suspension">
                <button type="button" onclick="toggleSuspension()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-yellow-300 text-yellow-700 bg-yellow-50 hover:bg-yellow-100 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Saisir le motif et suspendre
                </button>
            </div>
        </div>
        @endif

        {{-- ── Journal d'audit ──────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-bold text-gray-800">Historique des actions</h3>
            </div>

            @if ($historique->isEmpty())
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune action enregistrée.</div>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach ($historique as $entree)
                        @php
                            $actionStyle = match($entree->action) {
                                'creation'              => 'bg-blue-100 text-blue-700',
                                'modification'          => 'bg-gray-100 text-gray-600',
                                'suspension'            => 'bg-yellow-100 text-yellow-700',
                                'reactivation'          => 'bg-green-100 text-green-700',
                                'expiration_automatique'=> 'bg-red-100 text-red-600',
                                default                 => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <div class="px-5 py-3 flex items-start gap-3">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold {{ $actionStyle }} shrink-0 mt-0.5">
                                {{ str_replace('_', ' ', $entree->action) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-500">
                                    Par <span class="font-semibold text-gray-700">
                                        {{ trim($entree->auteur) ?: 'Système' }}
                                    </span>
                                    · {{ \Carbon\Carbon::parse($entree->created_at)->format('d/m/Y à H:i') }}
                                </p>
                                @if ($entree->nouvelles_valeurs)
                                    @php $nv = json_decode($entree->nouvelles_valeurs, true); @endphp
                                    @if (isset($nv['motif_suspension']))
                                        <p class="text-xs text-gray-600 mt-1 bg-yellow-50 px-2 py-1 rounded">
                                            Motif : {{ $nv['motif_suspension'] }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
        COLONNE ACTIONS (1/3)
    ══════════════════════════════════════════════════════════════════════ --}}
    <div class="space-y-4">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-3">Actions</h3>
            <div class="space-y-2">

                <a href="{{ route('admin.agrements.edit', $a->id) }}"
                   class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold border border-gray-200 text-gray-700 hover:bg-gray-50 transition-all">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>

                @if ($a->statut === 'suspendu')
                    <form method="POST" action="{{ route('admin.agrements.reactiver', $a->id) }}"
                          onsubmit="return confirm('Réactiver cet agrément ?')">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                                style="background-color: #059669;">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Réactiver
                        </button>
                    </form>
                @endif

            </div>
        </div>

        {{-- Méta --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 text-xs text-gray-400 space-y-2">
            <div class="flex justify-between">
                <span>Créé le</span>
                <span class="font-medium text-gray-600">
                    {{ \Carbon\Carbon::parse($a->created_at)->format('d/m/Y') }}
                </span>
            </div>
            <div class="flex justify-between">
                <span>Modifié le</span>
                <span class="font-medium text-gray-600">
                    {{ \Carbon\Carbon::parse($a->updated_at)->format('d/m/Y') }}
                </span>
            </div>
        </div>

        <a href="{{ route('admin.agrements.index') }}"
           class="flex items-center gap-2 text-sm text-gray-400 hover:text-gray-600 transition-colors px-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Retour à la liste
        </a>

    </div>

</div>

@push('scripts')
<script>
    function toggleSuspension() {
        document.getElementById('form-suspension').classList.toggle('hidden');
        document.getElementById('btn-suspension').classList.toggle('hidden');
    }
    // Si des erreurs de validation existent sur le motif, ouvrir le formulaire directement
    @if ($errors->has('motif'))
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('form-suspension').classList.remove('hidden');
            document.getElementById('btn-suspension').classList.add('hidden');
        });
    @endif
</script>
@endpush

@endsection
