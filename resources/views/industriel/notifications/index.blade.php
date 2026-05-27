@extends('layouts.industriel')

@section('titre', 'Notifications')
@section('sous_titre', $nonLues . ' non lue' . ($nonLues > 1 ? 's' : ''))

@section('contenu')

{{-- ── En-tête de page ──────────────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-lg font-bold text-gray-900">Mes notifications</h2>
        <p class="text-sm text-gray-500 mt-0.5">
            Validations, rejets et alertes sur votre agrément
        </p>
    </div>

    @if ($nonLues > 0)
    <form method="POST" action="{{ route('industriel.notifications.toutes-lues') }}">
        @csrf
        <button type="submit"
                class="flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-opacity hover:opacity-90"
                style="background-color: #1a237e;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Tout marquer comme lu
        </button>
    </form>
    @endif
</div>

{{-- ── Message flash ───────────────────────────────────────────────────────── --}}
@if (session('statut'))
<div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('statut') }}
</div>
@endif

{{-- ── Liste des notifications ─────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

    @forelse ($notifications as $notif)

    {{-- Type → badge couleur + icône --}}
    @php
        $config = match($notif->type) {
            'declaration_validee'  => ['bg' => 'bg-green-50',  'border' => 'border-green-200',  'dot' => 'bg-green-500',  'badge_bg' => 'bg-green-100',  'badge_text' => 'text-green-700',  'label' => 'Validée'],
            'declaration_rejetee'  => ['bg' => 'bg-red-50',    'border' => 'border-red-200',    'dot' => 'bg-red-500',    'badge_bg' => 'bg-red-100',    'badge_text' => 'text-red-700',    'label' => 'Rejetée'],
            'agrement_expirant'    => ['bg' => 'bg-amber-50',  'border' => 'border-amber-200',  'dot' => 'bg-amber-500',  'badge_bg' => 'bg-amber-100',  'badge_text' => 'text-amber-700',  'label' => 'Alerte agrément'],
            'agrement_expire'      => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'dot' => 'bg-orange-500', 'badge_bg' => 'bg-orange-100', 'badge_text' => 'text-orange-700', 'label' => 'Agrément expiré'],
            default                => ['bg' => 'bg-gray-50',   'border' => 'border-gray-200',   'dot' => 'bg-gray-400',   'badge_bg' => 'bg-gray-100',   'badge_text' => 'text-gray-600',   'label' => 'Système'],
        };
    @endphp

    <div class="flex items-start gap-4 px-5 py-4 border-b border-gray-100 last:border-0
                {{ $notif->lu ? '' : 'bg-blue-50/40' }}
                transition-colors hover:bg-gray-50/80">

        {{-- Point indicateur --}}
        <span class="mt-2 w-2.5 h-2.5 rounded-full shrink-0
                     {{ $notif->lu ? 'bg-gray-200' : 'bg-blue-500' }}"></span>

        {{-- Corps de la notification --}}
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-sm font-semibold text-gray-900">{{ $notif->titre }}</span>

                {{-- Badge type --}}
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $config['badge_bg'] }} {{ $config['badge_text'] }}">
                    {{ $config['label'] }}
                </span>

                {{-- Badge "Nouveau" pour les non lues --}}
                @if (! $notif->lu)
                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                    Nouveau
                </span>
                @endif
            </div>

            <p class="text-sm text-gray-600 leading-relaxed">{{ $notif->message }}</p>

            <p class="text-xs text-gray-400 mt-1.5">
                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                <span class="text-gray-300 mx-1">·</span>
                {{ \Carbon\Carbon::parse($notif->created_at)->format('d/m/Y à H:i') }}
            </p>
        </div>

        {{-- Action marquer comme lue --}}
        @if (! $notif->lu)
        <form method="POST" action="{{ route('industriel.notifications.lue', $notif->id) }}" class="shrink-0 mt-1">
            @csrf
            <button type="submit"
                    title="Marquer comme lue"
                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium text-gray-500 border border-gray-200 hover:border-blue-300 hover:text-blue-600 transition-colors bg-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Lu
            </button>
        </form>
        @else
        <span class="shrink-0 mt-1 flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-300">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Lu
        </span>
        @endif

    </div>

    @empty

    {{-- État vide --}}
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
             style="background-color: rgba(26,35,126,0.06);">
            <svg class="w-8 h-8" fill="none" stroke="#1a237e" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <p class="text-base font-semibold text-gray-700 mb-1">Aucune notification</p>
        <p class="text-sm text-gray-400">
            Vous serez notifié ici lors de validations, rejets ou alertes sur votre agrément.
        </p>
    </div>

    @endforelse

</div>

{{-- ── Pagination ───────────────────────────────────────────────────────────── --}}
@if ($notifications->hasPages())
<div class="mt-4">
    {{ $notifications->links() }}
</div>
@endif

@endsection
