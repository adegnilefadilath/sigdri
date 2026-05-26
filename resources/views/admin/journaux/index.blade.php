@extends('layouts.app')

@section('titre', 'Journal d\'audit')
@section('sous_titre', 'Traçabilité des actions SIGDRI')

@section('contenu')

{{-- ── En-tête avec compteur global ──────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
    <div>
        <p class="text-sm text-gray-500">
            <span class="text-2xl font-bold" style="color:#1a237e;">{{ number_format($total) }}</span>
            entrée(s) au total
        </p>
    </div>
    {{-- Export PDF du journal filtré --}}
    <a href="{{ route('admin.journaux.exporter', request()->query()) }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm"
       style="background-color:#e53e3e;">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
        Exporter PDF
    </a>
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" action="{{ route('admin.journaux.index') }}"
          class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        {{-- Type d'action --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
            <select name="action" class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
                <option value="">Toutes les actions</option>
                @foreach ($actionsDisponibles as $act)
                    <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                        {{ $act }}
                    </option>
                @endforeach
            </select>
        </div>
        {{-- Utilisateur --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Utilisateur</label>
            <input type="text" name="utilisateur" value="{{ request('utilisateur') }}"
                   placeholder="Nom ou e-mail…"
                   class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
        </div>
        {{-- Date début --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date début</label>
            <input type="date" name="date_debut" value="{{ request('date_debut') }}"
                   class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
        </div>
        {{-- Date fin --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Date fin</label>
            <input type="date" name="date_fin" value="{{ request('date_fin') }}"
                   class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none">
        </div>
        {{-- Boutons --}}
        <div class="flex items-end gap-2">
            <button type="submit"
                    class="flex-1 px-4 py-2 rounded-lg text-sm font-medium text-white"
                    style="background-color:#1a237e;">
                Filtrer
            </button>
            <a href="{{ route('admin.journaux.index') }}"
               class="flex-1 text-center px-4 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                Réinitialiser
            </a>
        </div>
    </form>
</div>

{{-- ── Tableau des entrées du journal ──────────────────────────────────────── --}}
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100" style="background-color:#f8f9ff;">
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Date & heure</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Utilisateur</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Action</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Description</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-700">Adresse IP</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse ($journaux as $j)
            <tr class="hover:bg-gray-50 transition-colors">
                {{-- Date ----------------------------------------------------- --}}
                <td class="px-4 py-3 text-gray-600 whitespace-nowrap text-xs">
                    {{ \Carbon\Carbon::parse($j->created_at)->format('d/m/Y') }}<br>
                    <span class="text-gray-400">{{ \Carbon\Carbon::parse($j->created_at)->format('H:i:s') }}</span>
                </td>
                {{-- Utilisateur ----------------------------------------------- --}}
                <td class="px-4 py-3">
                    @if (trim($j->auteur))
                        <p class="font-medium text-gray-900 text-xs">{{ trim($j->auteur) }}</p>
                        <p class="text-xs text-gray-400">{{ $j->auteur_email }}</p>
                    @else
                        <span class="text-xs text-gray-400 italic">Système</span>
                    @endif
                </td>
                {{-- Badge action ---------------------------------------------- --}}
                <td class="px-4 py-3">
                    @php
                        // Couleur du badge selon la catégorie d'action
                        $badgesAction = [
                            'connexion'    => 'background:#dbeafe;color:#1e40af;',
                            'deconnexion'  => 'background:#e0f2fe;color:#075985;',
                            'creation'     => 'background:#dcfce7;color:#166534;',
                            'soumission'   => 'background:#dcfce7;color:#166534;',
                            'modification' => 'background:#fed7aa;color:#92400e;',
                            'correction'   => 'background:#fef9c3;color:#854d0e;',
                            'validation'   => 'background:#d1fae5;color:#065f46;',
                            'suspension'   => 'background:#fee2e2;color:#991b1b;',
                            'rejet'        => 'background:#fee2e2;color:#991b1b;',
                            'desactivation'=> 'background:#fee2e2;color:#991b1b;',
                            'reactivation' => 'background:#dcfce7;color:#166534;',
                            'rapport_pdf'  => 'background:#ede9fe;color:#5b21b6;',
                            'rapport_excel'=> 'background:#ede9fe;color:#5b21b6;',
                        ];
                        $styleAction = $badgesAction[$j->action] ?? 'background:#f1f5f9;color:#475569;';
                    @endphp
                    <span style="{{ $styleAction }} padding:2px 8px; border-radius:999px;
                                  font-size:11px; font-weight:600; white-space:nowrap;">
                        {{ $j->action }}
                    </span>
                    @if ($j->table_concernee)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $j->table_concernee }}
                            @if ($j->enregistrement_id)
                                #{{ $j->enregistrement_id }}
                            @endif
                        </p>
                    @endif
                </td>
                {{-- Description ---------------------------------------------- --}}
                <td class="px-4 py-3 text-gray-700 max-w-xs">
                    <p class="text-xs">{{ $j->description ?? '—' }}</p>
                </td>
                {{-- Adresse IP ----------------------------------------------- --}}
                <td class="px-4 py-3 text-gray-500 font-mono text-xs whitespace-nowrap">
                    {{ $j->ip_address ?? '—' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-12 text-center text-gray-400 text-sm">
                    Aucune entrée de journal ne correspond aux critères sélectionnés.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination ─────────────────────────────────────────────────────────── --}}
    @if ($journaux->hasPages())
    <div class="px-4 py-3 border-t border-gray-100 text-xs text-gray-500">
        <div class="flex items-center justify-between">
            <span>
                Entrées {{ $journaux->firstItem() }}–{{ $journaux->lastItem() }}
                sur {{ $journaux->total() }}
            </span>
            {{ $journaux->links() }}
        </div>
    </div>
    @endif
</div>

@endsection
