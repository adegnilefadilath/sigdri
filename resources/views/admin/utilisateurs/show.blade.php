@extends('layouts.app')

@section('titre', $utilisateur->prenom . ' ' . $utilisateur->nom)
@section('sous_titre', 'Fiche utilisateur')

@section('contenu')

{{-- ── En-tête ─────────────────────────────────────────────────────────────── --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('admin.utilisateurs.index') }}"
       class="p-2 rounded-lg hover:bg-gray-100 transition-colors text-gray-500">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </a>
    <h2 class="text-lg font-bold text-gray-900">
        {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
    </h2>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Colonne gauche : fiche profil ──────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Carte profil --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-start gap-5">
                {{-- Grand avatar initiales --}}
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white text-xl font-bold shrink-0"
                     style="background-color:#1a237e;">
                    {{ strtoupper(mb_substr($utilisateur->prenom, 0, 1)) }}{{ strtoupper(mb_substr($utilisateur->nom, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h3 class="text-lg font-bold text-gray-900">
                            {{ $utilisateur->prenom }} {{ $utilisateur->nom }}
                        </h3>
                        {{-- Badge statut --}}
                        @if ($utilisateur->actif)
                            <span class="text-xs font-semibold text-green-700 px-2 py-0.5 rounded-full"
                                  style="background:#dcfce7;">Actif</span>
                        @else
                            <span class="text-xs font-semibold text-red-600 px-2 py-0.5 rounded-full"
                                  style="background:#fee2e2;">Inactif</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500">{{ $utilisateur->email }}</p>
                </div>
            </div>

            {{-- Détails en tableau --}}
            <div class="mt-5 border-t border-gray-100 pt-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-0.5">Rôle</dt>
                        <dd>
                            @php
                                $badgesRole = [
                                    'super_admin' => 'background:#ede9fe;color:#5b21b6;',
                                    'admin'       => 'background:#dbeafe;color:#1e40af;',
                                    'agent_mic'   => 'background:#d1fae5;color:#065f46;',
                                    'decideur'    => 'background:#fed7aa;color:#92400e;',
                                    'industriel'  => 'background:#e2e8f0;color:#334155;',
                                ];
                                $styleRole = $badgesRole[$utilisateur->role] ?? 'background:#f1f5f9;color:#475569;';
                            @endphp
                            <span style="{{ $styleRole }} padding:2px 10px; border-radius:999px; font-size:11px; font-weight:600;">
                                {{ $roles[$utilisateur->role] ?? $utilisateur->role }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-0.5">Statut du compte</dt>
                        <dd class="font-medium {{ $utilisateur->actif ? 'text-green-700' : 'text-red-600' }}">
                            {{ $utilisateur->actif ? 'Actif' : 'Inactif' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-0.5">Compte créé le</dt>
                        <dd class="text-gray-700">
                            {{ \Carbon\Carbon::parse($utilisateur->created_at)->format('d/m/Y à H:i') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-0.5">Dernière connexion</dt>
                        <dd class="text-gray-700">
                            {{ $utilisateur->derniere_connexion
                                ? \Carbon\Carbon::parse($utilisateur->derniere_connexion)->format('d/m/Y à H:i')
                                : '—' }}
                        </dd>
                    </div>
                    @if ($utilisateur->email_verifie_le)
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-0.5">E-mail vérifié le</dt>
                        <dd class="text-gray-700">
                            {{ \Carbon\Carbon::parse($utilisateur->email_verifie_le)->format('d/m/Y') }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Unité industrielle liée --}}
        @if ($unite)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Unité industrielle liée</h3>
            <div class="flex items-center gap-4 p-3 rounded-lg" style="background:#f8f9ff;">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0"
                     style="background-color:rgba(26,35,126,0.1);">
                    <svg class="w-5 h-5" style="color:#1a237e;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-gray-900 text-sm">{{ $unite->denomination }}</p>
                    <p class="text-xs text-gray-500">{{ $unite->departement }} — {{ $unite->secteur_activite }}</p>
                </div>
                <a href="{{ route('admin.unites.show', $unite->id) }}"
                   class="text-xs font-medium px-3 py-1.5 rounded-lg hover:bg-white transition-colors"
                   style="color:#1a237e;">
                    Voir l'unité →
                </a>
            </div>
        </div>
        @endif

        {{-- Historique des connexions --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Historique des connexions (10 dernières)</h3>
            </div>
            @if ($connexions->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-400">
                    Aucune session enregistrée pour cet utilisateur.
                </p>
            @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100" style="background:#f8f9ff;">
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Date et heure</th>
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Adresse IP</th>
                        <th class="text-left px-4 py-2 text-xs font-semibold text-gray-600">Navigateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($connexions as $session)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2.5 text-gray-700">
                            {{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-600 font-mono text-xs">
                            {{ $session->ip_address ?? '—' }}
                        </td>
                        <td class="px-4 py-2.5 text-gray-500 text-xs truncate max-w-xs">
                            {{ $session->user_agent
                                ? Str::limit($session->user_agent, 60)
                                : '—' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

    </div>

    {{-- ── Colonne droite : actions ─────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Modifier le compte --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Actions</h3>
            <div class="space-y-2">
                {{-- Modifier --}}
                <a href="{{ route('admin.utilisateurs.edit', $utilisateur->id) }}"
                   class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-white"
                   style="background-color:#1a237e;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier le compte
                </a>

                {{-- Activer / Désactiver --}}
                @if ($utilisateur->id !== Auth::id() && $utilisateur->role !== 'super_admin')
                <form method="POST"
                      action="{{ route('admin.utilisateurs.toggle-statut', $utilisateur->id) }}"
                      onsubmit="return confirm('{{ $utilisateur->actif ? 'Désactiver ce compte ?' : 'Activer ce compte ?' }}')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-sm font-medium
                                   {{ $utilisateur->actif
                                       ? 'text-orange-700 bg-orange-50 hover:bg-orange-100'
                                       : 'text-green-700 bg-green-50 hover:bg-green-100' }}">
                        @if ($utilisateur->actif)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Désactiver le compte
                        @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Activer le compte
                        @endif
                    </button>
                </form>
                @endif

                {{-- Réinitialiser le mot de passe --}}
                <form method="POST"
                      action="{{ route('admin.utilisateurs.reset-password', $utilisateur->id) }}"
                      onsubmit="return confirm('Réinitialiser le mot de passe ? Un nouveau mot de passe temporaire sera généré.')">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-sm font-medium
                                   text-red-700 bg-red-50 hover:bg-red-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        Réinitialiser le mot de passe
                    </button>
                </form>
            </div>
        </div>

        {{-- Carte récapitulatif rôle --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations du rôle</h3>
            @php
                $descRoles = [
                    'super_admin' => 'Accès complet. Peut gérer tous les utilisateurs, paramètres et données du système.',
                    'admin'       => 'Peut gérer les utilisateurs et les paramètres de la plateforme.',
                    'agent_mic'   => 'Peut consulter, valider et rejeter les déclarations industrielles.',
                    'decideur'    => 'Accès en lecture aux rapports, statistiques et cartographie.',
                    'industriel'  => 'Peut soumettre et corriger les déclarations de son unité industrielle.',
                ];
            @endphp
            <p class="text-xs text-gray-500 leading-relaxed">
                {{ $descRoles[$utilisateur->role] ?? 'Rôle non documenté.' }}
            </p>
        </div>

    </div>

</div>

@endsection
