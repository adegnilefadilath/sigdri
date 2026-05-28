<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGDRI — Espace Industriel — @yield('titre', 'Dashboard')</title>

    {{-- ── PWA — Module 9 ─────────────────────────────────────────────────── --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#F97316">
    {{-- Compatibilité iOS --}}
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIGDRI">
    <link rel="apple-touch-icon" href="/icons/icon-192.svg">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

{{-- ── Overlay sombre (mobile uniquement) — masque le contenu derrière la sidebar ── --}}
<div id="sidebar-overlay"
     class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden"
     onclick="fermerSidebar()"
     aria-hidden="true"></div>

<div class="flex h-screen overflow-hidden">

    {{-- ═══════════════════════════════════════════════════════════════════════
        SIDEBAR INDUSTRIEL
        Mobile : position fixed, décalée hors-écran (-translate-x-full), animée.
        Desktop (lg+) : position statique dans le flux flex, toujours visible.
    ════════════════════════════════════════════════════════════════════════ --}}
    <aside id="sidebar-industriel"
           class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col shrink-0
                  -translate-x-full lg:translate-x-0 lg:static
                  transition-transform duration-300 ease-in-out"
           style="background-color: #1a237e;">

        {{-- ── Logo SIGDRI + bouton fermeture (mobile) ─────────────────── --}}
        <div class="flex items-center justify-between px-5 py-5"
             style="border-bottom: 1px solid rgba(255,255,255,0.1);">

            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                     style="background-color: rgba(249,115,22,0.15); border: 1.5px solid rgba(249,115,22,0.5);">
                    <svg width="20" height="20" style="flex-shrink:0" viewBox="0 0 24 24" fill="none"
                         stroke="#F97316" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18M3 7v14M17 7v14M7 7v14M13 7v14"/>
                        <path d="M3 7l4-4 3 4 3-4 4 4"/>
                        <rect x="9" y="14" width="6" height="7"/>
                    </svg>
                </div>
                <div>
                    <p class="font-black text-base tracking-wider" style="color: #F97316;">SIGDRI</p>
                    <p class="text-xs leading-tight" style="color: rgba(255,255,255,0.5);">
                        Espace Industriel
                    </p>
                </div>
            </div>

            {{-- Bouton × fermer la sidebar — visible uniquement sur mobile --}}
            <button onclick="fermerSidebar()"
                    class="lg:hidden p-1.5 rounded-lg transition-colors hover:bg-white/10"
                    aria-label="Fermer le menu">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="rgba(255,255,255,0.7)"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- ── Bannière unité industrielle ───────────────────────────────── --}}
        @if (Auth::user()->unite_industrielle_id)
        <div class="mx-3 mt-4 px-3 py-2.5 rounded-xl" style="background: rgba(249,115,22,0.12);">
            <p class="text-xs font-semibold" style="color: rgba(249,115,22,0.9);">MON UNITÉ</p>
            <p class="text-sm font-bold text-white mt-0.5 leading-tight truncate">
                {{ session('unite_denomination', 'Unité industrielle') }}
            </p>
        </div>
        @endif

        {{-- ── Navigation — onclick="fermerSidebar()" sur chaque lien pour fermer sur mobile ── --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <p class="px-3 pt-2 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Mon espace</p>

            {{-- Dashboard --}}
            <a href="{{ route('industriel.dashboard') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.dashboard') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.dashboard') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Déclarations</p>

            {{-- Mes déclarations --}}
            <a href="{{ route('industriel.declarations.index') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.declarations.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.declarations.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Mes déclarations
            </a>

            {{-- Nouvelle déclaration --}}
            <a href="{{ route('industriel.declarations.create') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle déclaration
            </a>

            <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Mon entreprise</p>

            {{-- Mon agrément --}}
            <a href="{{ route('industriel.agrement.show') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.agrement.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.agrement.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Mon agrément
            </a>

            {{-- Mon profil --}}
            <a href="{{ route('industriel.profil.index') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.profil.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.profil.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Mon profil
            </a>

            <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Alertes</p>

            {{-- Notifications --}}
            <a href="{{ route('industriel.notifications.index') }}"
               onclick="fermerSidebar()"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.notifications.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.notifications.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <span class="relative shrink-0">
                    <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if ($notifNonLues > 0)
                    <span class="absolute -top-1 -right-1 w-3.5 h-3.5 flex items-center justify-center rounded-full text-white font-black"
                          style="font-size:8px; background-color:#ef4444;">
                        {{ $notifNonLues > 9 ? '9+' : $notifNonLues }}
                    </span>
                    @endif
                </span>
                Notifications
                @if ($notifNonLues > 0)
                <span class="ml-auto text-xs font-bold px-1.5 py-0.5 rounded-full"
                      style="background: rgba(239,68,68,0.2); color: #fca5a5;">
                    {{ $notifNonLues }}
                </span>
                @endif
            </a>

        </nav>

        {{-- ── Déconnexion ────────────────────────────────────────────────── --}}
        <div class="p-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
            <form method="POST" action="{{ route('industriel.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
                        style="color: rgba(249,115,22,0.85);">
                    <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Déconnexion
                </button>
            </form>
        </div>

    </aside>{{-- /sidebar --}}

    {{-- ═══════════════════════════════════════════════════════════════════════
        ZONE PRINCIPALE
        min-w-0 évite que le contenu déborde du flex-1 sur mobile.
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden min-w-0">

        {{-- ── Header ─────────────────────────────────────────────────────── --}}
        <header class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 shrink-0">
            <div class="flex items-center justify-between gap-3">

                {{-- Hamburger (mobile) + titre de page --}}
                <div class="flex items-center gap-3 min-w-0 flex-1">

                    {{-- Bouton hamburger — visible uniquement sous lg --}}
                    <button onclick="ouvrirSidebar()"
                            class="lg:hidden shrink-0 p-2 rounded-lg hover:bg-gray-100 transition-colors"
                            aria-label="Ouvrir le menu">
                        <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="#4b5563"
                             stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                            <path d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <h1 class="text-base sm:text-xl font-bold text-gray-900 truncate">
                            @yield('titre', 'Dashboard')
                        </h1>
                        {{-- Sous-titre masqué sur très petits écrans --}}
                        <p class="text-xs text-gray-400 mt-0.5 hidden sm:block">
                            @yield('sous_titre', '')
                        </p>
                    </div>
                </div>

                {{-- Cloche notifications + avatar --}}
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">

                    {{-- Cloche avec dropdown ──────────────────────────────── --}}
                    <div class="relative" id="notif-wrapper">
                        <button id="notif-btn"
                                onclick="toggleNotifDropdown()"
                                class="relative p-2 rounded-lg transition-colors hover:bg-gray-100"
                                aria-label="Notifications">
                            <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if ($notifNonLues > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-0.5 flex items-center justify-center rounded-full text-white font-bold"
                                  style="font-size:10px; background-color:#ef4444;">
                                {{ $notifNonLues > 9 ? '9+' : $notifNonLues }}
                            </span>
                            @endif
                        </button>

                        {{-- Dropdown — plus étroit sur mobile --}}
                        <div id="notif-dropdown"
                             class="hidden absolute right-0 top-full mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-xl border border-gray-200 z-50 overflow-hidden">

                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <span class="text-sm font-bold text-gray-800">Notifications</span>
                                @if ($notifNonLues > 0)
                                <form method="POST" action="{{ route('industriel.notifications.toutes-lues') }}">
                                    @csrf
                                    <button type="submit" class="text-xs font-medium transition-colors"
                                            style="color: #1a237e;">
                                        Tout marquer lu
                                    </button>
                                </form>
                                @endif
                            </div>

                            <ul class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                                @forelse ($notifRecentes as $notif)
                                <li class="px-4 py-3 transition-colors hover:bg-gray-50 {{ $notif->lu ? '' : 'bg-blue-50/60' }}">
                                    <div class="flex items-start gap-3">
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $notif->lu ? 'bg-gray-200' : 'bg-blue-500' }}"></span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-gray-800 truncate">{{ $notif->titre }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $notif->message }}</p>
                                            <p class="text-[10px] text-gray-400 mt-1">
                                                {{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}
                                            </p>
                                        </div>
                                        @if (! $notif->lu)
                                        <form method="POST" action="{{ route('industriel.notifications.lue', $notif->id) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" title="Marquer comme lue"
                                                    class="text-gray-300 hover:text-blue-500 transition-colors">
                                                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </li>
                                @empty
                                <li class="px-4 py-6 text-center text-xs text-gray-400">
                                    Aucune notification
                                </li>
                                @endforelse
                            </ul>

                            <div class="border-t border-gray-100">
                                <a href="{{ route('industriel.notifications.index') }}"
                                   class="flex items-center justify-center gap-1.5 py-3 text-xs font-semibold transition-colors hover:bg-gray-50"
                                   style="color: #1a237e;">
                                    Voir toutes les notifications
                                    <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>{{-- /notif-wrapper --}}

                    {{-- Nom + unité (masqués sur mobile) --}}
                    <div class="text-right hidden sm:block">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ session('unite_denomination', 'Industriel') }}
                        </p>
                    </div>

                    {{-- Avatar initiales --}}
                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                         style="background-color: #F97316;">
                        {{ strtoupper(mb_substr(Auth::user()->prenom, 0, 1)) }}{{ strtoupper(mb_substr(Auth::user()->nom, 0, 1)) }}
                    </div>
                </div>

            </div>
        </header>

        {{-- ── Contenu principal scrollable ──────────────────────────────── --}}
        {{-- p-4 sur mobile, p-6 à partir de sm --}}
        <main class="flex-1 overflow-y-auto p-4 sm:p-6">

            {{-- Bannière hors-ligne (Module 9) — affichée par JS si offline ── --}}
            <div id="banner-offline"
                 class="hidden mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:#fef3c7; border-color:#fbbf24; color:#92400e;">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M18.364 5.636a9 9 0 01-12.728 12.728M3 3l18 18"/>
                </svg>
                <span>
                    <strong>Mode hors-ligne</strong> — Vos déclarations saisies seront synchronisées automatiquement à la reconnexion.
                </span>
                <span id="badge-sync" class="hidden ml-auto shrink-0 px-2 py-0.5 rounded-full text-xs font-bold"
                      style="background:#f59e0b; color:#fff;"></span>
            </div>

            {{-- Bannière synchronisation réussie (Module 9) --}}
            <div id="banner-sync-ok"
                 class="hidden mb-4 flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:#f0fdf4; border-color:#86efac; color:#166534;">
                <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="texte-sync-ok">Déclarations synchronisées avec succès.</span>
            </div>

            @if (session('statut'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <svg width="20" height="20" style="flex-shrink:0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('statut') }}
                </div>
            @endif

            @yield('contenu')
        </main>

    </div>

</div>

@stack('scripts')

{{-- ── Dropdown notifications ──────────────────────────────────────────── --}}
<script>
function toggleNotifDropdown() {
    document.getElementById('notif-dropdown').classList.toggle('hidden');
}
document.addEventListener('click', function (e) {
    var wrapper = document.getElementById('notif-wrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        document.getElementById('notif-dropdown').classList.add('hidden');
    }
});
</script>

{{-- ── Sidebar mobile : ouverture / fermeture ─────────────────────────── --}}
<script>
function ouvrirSidebar() {
    document.getElementById('sidebar-industriel').classList.remove('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // bloque le scroll du fond
}
function fermerSidebar() {
    document.getElementById('sidebar-industriel').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    document.body.style.overflow = '';
}
</script>

{{-- ══════════════════════════════════════════════════════════════════════════
    MODULE 9 — PWA : Enregistrement du Service Worker + gestion hors-ligne
════════════════════════════════════════════════════════════════════════════ --}}
<script src="/js/offline-db.js"></script>
<script>
(function () {

    /* ── 1. Enregistrement du Service Worker ─────────────────────────────── */
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register('/sw.js', { scope: '/' })
            .then((reg) => {
                console.log('[SIGDRI SW] Enregistré, scope :', reg.scope);
                reg.onupdatefound = () => {
                    const sw = reg.installing;
                    sw.onstatechange = () => {
                        if (sw.state === 'installed' && navigator.serviceWorker.controller) {
                            console.log('[SIGDRI SW] Mise à jour disponible.');
                            sw.postMessage({ type: 'SKIP_WAITING' });
                        }
                    };
                };
            })
            .catch((err) => console.warn('[SIGDRI SW] Échec enregistrement :', err));

        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'SYNC_DECLARATIONS') {
                lancerSynchronisation();
            }
        });
    }

    /* ── 2. Bannière hors-ligne ──────────────────────────────────────────── */
    const bannerOffline = document.getElementById('banner-offline');
    const badgeSync     = document.getElementById('badge-sync');

    function majBanniereOffline() {
        if (!navigator.onLine) {
            bannerOffline.classList.remove('hidden');
            majBadgeSync();
        } else {
            bannerOffline.classList.add('hidden');
        }
    }

    async function majBadgeSync() {
        try {
            const nb = await SigdriOfflineDB.compter();
            if (nb > 0) {
                badgeSync.textContent = `${nb} en attente`;
                badgeSync.classList.remove('hidden');
            } else {
                badgeSync.classList.add('hidden');
            }
        } catch { /* IndexedDB indisponible */ }
    }

    window.addEventListener('online',  majBanniereOffline);
    window.addEventListener('offline', majBanniereOffline);
    majBanniereOffline();

    /* ── 3. Synchronisation automatique à la reconnexion ────────────────── */
    async function lancerSynchronisation() {
        try {
            const resultats = await SigdriOfflineDB.synchroniser();
            const ok  = resultats.filter((r) => r.statut === 'ok').length;
            const ko  = resultats.filter((r) => r.statut !== 'ok').length;

            if (ok > 0) {
                const bannerOk = document.getElementById('banner-sync-ok');
                const texteOk  = document.getElementById('texte-sync-ok');
                if (bannerOk) {
                    texteOk.textContent = `${ok} déclaration${ok > 1 ? 's' : ''} synchronisée${ok > 1 ? 's' : ''} avec succès.`;
                    bannerOk.classList.remove('hidden');
                    setTimeout(() => bannerOk.classList.add('hidden'), 6000);
                }
            }
            if (ko > 0) {
                console.warn(`[SIGDRI Sync] ${ko} déclaration(s) n'ont pas pu être synchronisées.`);
            }
            majBadgeSync();
        } catch (err) {
            console.error('[SIGDRI Sync] Erreur synchronisation :', err);
        }
    }

    // Fallback synchronisation à la reconnexion (Background Sync non supporté partout)
    window.addEventListener('online', () => {
        setTimeout(lancerSynchronisation, 1500);
    });

    // Synchronisation si des entrées existent au chargement et qu'on est connecté
    if (navigator.onLine) {
        SigdriOfflineDB.compter().then((nb) => {
            if (nb > 0) lancerSynchronisation();
        }).catch(() => {});
    }

})();
</script>

</body>
</html>
