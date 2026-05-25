<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGDRI — Espace Industriel — @yield('titre', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ═══════════════════════════════════════════════════════════════════════
        SIDEBAR INDUSTRIEL
        Même charte graphique que l'espace admin (#1a237e / orange #F97316)
        mais navigation réduite aux fonctions de l'industriel.
    ════════════════════════════════════════════════════════════════════════ --}}
    <aside class="w-64 shrink-0 flex flex-col" style="background-color: #1a237e;">

        {{-- ── Logo SIGDRI ───────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 px-5 py-5"
             style="border-bottom: 1px solid rgba(255,255,255,0.1);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                 style="background-color: rgba(249,115,22,0.15); border: 1.5px solid rgba(249,115,22,0.5);">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none"
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

        {{-- ── Bannière "Unité industrielle" ───────────────────────────────── --}}
        @if (Auth::user()->unite_industrielle_id)
        <div class="mx-3 mt-4 px-3 py-2.5 rounded-xl" style="background: rgba(249,115,22,0.12);">
            <p class="text-xs font-semibold" style="color: rgba(249,115,22,0.9);">
                MON UNITÉ
            </p>
            <p class="text-sm font-bold text-white mt-0.5 leading-tight truncate">
                {{ session('unite_denomination', 'Unité industrielle') }}
            </p>
        </div>
        @endif

        {{-- ── Navigation ───────────────────────────────────────────────── --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            {{-- Section principale --}}
            <p class="px-3 pt-2 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Mon espace</p>

            {{-- Dashboard --}}
            <a href="{{ route('industriel.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.dashboard') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.dashboard') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            {{-- Section déclarations --}}
            <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Déclarations</p>

            {{-- Mes déclarations --}}
            <a href="{{ route('industriel.declarations.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.declarations.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.declarations.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Mes déclarations
            </a>

            {{-- Nouvelle déclaration --}}
            <a href="{{ route('industriel.declarations.create') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle déclaration
            </a>

            {{-- Section unité --}}
            <p class="px-3 pt-4 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Mon entreprise</p>

            {{-- Mon agrément --}}
            <a href="{{ route('industriel.agrement.show') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('industriel.agrement.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('industriel.agrement.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Mon agrément
            </a>

            {{-- Mon profil --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Mon profil
            </a>

        </nav>

        {{-- ── Déconnexion en bas de sidebar ──────────────────────────────── --}}
        <div class="p-3" style="border-top: 1px solid rgba(255,255,255,0.1);">
            <form method="POST" action="{{ route('industriel.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
                        style="color: rgba(249,115,22,0.85);">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- ── Header ────────────────────────────────────────────────────── --}}
        <header class="bg-white border-b border-gray-200 px-6 py-4 shrink-0">
            <div class="flex items-center justify-between">

                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        @yield('titre', 'Dashboard')
                    </h1>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @yield('sous_titre', '')
                    </p>
                </div>

                {{-- Nom de l'industriel + unité --}}
                <div class="flex items-center gap-3">
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

        {{-- ── Contenu scrollable ────────────────────────────────────────── --}}
        <main class="flex-1 overflow-y-auto p-6">
            @if (session('statut'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
</body>
</html>
