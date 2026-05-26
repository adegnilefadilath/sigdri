@extends('layouts.app')

@section('titre', 'Utilisateurs')
@section('sous_titre', 'Gestion des comptes et des accès')

@section('contenu')

{{-- ── En-tête avec compteurs ─────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div class="flex items-center gap-6">
        {{-- Compteur total --}}
        <div>
            <p class="text-2xl font-bold" style="color:#1a237e;">{{ $total }}</p>
            <p class="text-xs text-gray-500">compte(s) total</p>
        </div>
        <div class="w-px h-8 bg-gray-200"></div>
        {{-- Compteur actifs --}}
        <div>
            <p class="text-2xl font-bold text-green-600">{{ $totalActifs }}</p>
            <p class="text-xs text-gray-500">compte(s) actif(s)</p>
        </div>
        <div class="w-px h-8 bg-gray-200"></div>
        {{-- Compteur inactifs --}}
        <div>
            <p class="text-2xl font-bold text-red-500">{{ $total - $totalActifs }}</p>
            <p class="text-xs text-gray-500">compte(s) inactif(s)</p>
        </div>
    </div>
    <a href="{{ route('admin.utilisateurs.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm"
       style="background-color:#1a237e;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
        Nouvel utilisateur
    </a>
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="{{ route('admin.utilisateurs.index') }}"
          class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        {{-- Recherche textuelle --}}
        <div class="col-span-2 sm:col-span-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
            <input type="text" name="recherche" value="{{ request('recherche') }}"
                   placeholder="Nom, prénom, e-mail…"
                   class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
        </div>
        {{-- Filtre rôle --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Rôle</label>
            <select name="role" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Tous les rôles</option>
                @foreach ($roles as $cle => $libelle)
                    <option value="{{ $cle }}" {{ request('role') === $cle ? 'selected' : '' }}>
                        {{ $libelle }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Filtre statut --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
            <select name="statut" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Tous les statuts</option>
                <option value="actif"   {{ request('statut') === 'actif'   ? 'selected' : '' }}>Actif</option>
                <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
        </div>
        {{-- Boutons action --}}
        <div class="flex items-end gap-2">
            <button type="submit"
                    class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-white"
                    style="background-color:#1a237e;">
                Filtrer
            </button>
            <a href="{{ route('admin.utilisateurs.index') }}"
               class="flex-1 text-center px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

{{-- ── Tableau des utilisateurs ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100" style="background-color:#f8f9ff;">
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Utilisateur</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">E-mail</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Rôle</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Statut</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Dernière connexion</th>
                <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($utilisateurs as $u)
            <tr class="hover:bg-gray-50 transition-colors">
                {{-- Avatar + nom ------------------------------------------------ --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        {{-- Avatar initiales --}}
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                             style="background-color:#1a237e;">
                            {{ strtoupper(mb_substr($u->prenom, 0, 1)) }}{{ strtoupper(mb_substr($u->nom, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $u->prenom }} {{ $u->nom }}</p>
                            @if ($u->unite_industrielle_id)
                                <p class="text-xs text-gray-400">Compte industriel lié</p>
                            @endif
                        </div>
                    </div>
                </td>
                {{-- E-mail ------------------------------------------------------- --}}
                <td class="px-4 py-3 text-gray-600">{{ $u->email }}</td>
                {{-- Badge rôle --------------------------------------------------- --}}
                <td class="px-4 py-3">
                    @php
                        $badgesRole = [
                            'super_admin' => 'background:#ede9fe;color:#5b21b6;',
                            'admin'       => 'background:#dbeafe;color:#1e40af;',
                            'agent_mic'   => 'background:#d1fae5;color:#065f46;',
                            'decideur'    => 'background:#fed7aa;color:#92400e;',
                            'industriel'  => 'background:#e2e8f0;color:#334155;',
                        ];
                        $styleRole = $badgesRole[$u->role] ?? 'background:#f1f5f9;color:#475569;';
                    @endphp
                    <span style="{{ $styleRole }} padding:2px 10px; border-radius:999px;
                                  font-size:11px; font-weight:600; white-space:nowrap;">
                        {{ $roles[$u->role] ?? $u->role }}
                    </span>
                </td>
                {{-- Badge statut ------------------------------------------------- --}}
                <td class="px-4 py-3">
                    @if ($u->actif)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700"
                              style="background:#dcfce7; padding:2px 10px; border-radius:999px;">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                            Actif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600"
                              style="background:#fee2e2; padding:2px 10px; border-radius:999px;">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>
                            Inactif
                        </span>
                    @endif
                </td>
                {{-- Dernière connexion ------------------------------------------- --}}
                <td class="px-4 py-3 text-gray-500 text-xs">
                    {{ $u->derniere_connexion
                        ? \Carbon\Carbon::parse($u->derniere_connexion)->format('d/m/Y H:i')
                        : '—' }}
                </td>
                {{-- Actions ------------------------------------------------------- --}}
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        {{-- Voir le détail --}}
                        <a href="{{ route('admin.utilisateurs.show', $u->id) }}"
                           class="p-1.5 rounded-lg hover:bg-blue-50 transition-colors" title="Voir">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        {{-- Modifier --}}
                        <a href="{{ route('admin.utilisateurs.edit', $u->id) }}"
                           class="p-1.5 rounded-lg hover:bg-indigo-50 transition-colors" title="Modifier">
                            <svg class="w-4 h-4" style="color:#1a237e;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        {{-- Activer / désactiver --}}
                        @if ($u->id !== Auth::id() && $u->role !== 'super_admin')
                        <form method="POST"
                              action="{{ route('admin.utilisateurs.toggle-statut', $u->id) }}"
                              onsubmit="return confirm('{{ $u->actif ? 'Désactiver ce compte ?' : 'Activer ce compte ?' }}')">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg hover:bg-gray-100 transition-colors"
                                    title="{{ $u->actif ? 'Désactiver' : 'Activer' }}">
                                @if ($u->actif)
                                    <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-12 text-center text-gray-400 text-sm">
                    Aucun utilisateur ne correspond aux critères de recherche.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination ─────────────────────────────────────────────────────────── --}}
    @if ($utilisateurs->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $utilisateurs->links() }}
    </div>
    @endif
</div>

@endsection
