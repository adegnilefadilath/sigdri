<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SIGDRI') }} — @yield('titre', 'Tableau de bord')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- ═══════════════════════════════════════════════════════════════════════
        SIDEBAR — Navigation principale
        Fond : #1a237e (bleu indigo profond)
        Lien actif : fond orange #F97316, texte blanc, arrondi
        Liens inactifs : texte blanc/gris clair, hover léger
    ════════════════════════════════════════════════════════════════════════ --}}
    <aside class="w-64 shrink-0 flex flex-col overflow-hidden" style="background-color: #1a237e;">

        {{-- ── Logo SIGDRI ───────────────────────────────────────────────── --}}
        <div class="flex items-center gap-3 px-5 py-5" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
            {{-- Cercle orange avec icône bâtiment --}}
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
                    Ministère de l'Industrie
                </p>
            </div>
        </div>

        {{-- ── Navigation scrollable ─────────────────────────────────────── --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">

            {{-- ▸ Section PRINCIPAL --}}
            <p class="px-3 pt-2 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Principal</p>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('dashboard') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('dashboard') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-4.5 h-4.5 shrink-0 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            {{-- ▸ Section GESTION --}}
            <p class="px-3 pt-5 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Gestion</p>

            {{-- Déclarations --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Déclarations
            </a>

            {{-- Unités industrielles --}}
            <a href="{{ route('admin.unites.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.unites.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('admin.unites.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Unités industrielles
            </a>

            {{-- Produits & Matières --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Produits &amp; Matières
            </a>

            {{-- Agréments --}}
            <a href="{{ route('admin.agrements.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ request()->routeIs('admin.agrements.*') ? 'text-white shadow-md' : 'hover:bg-white/10' }}"
               style="{{ request()->routeIs('admin.agrements.*') ? 'background-color: #F97316;' : 'color: rgba(255,255,255,0.75);' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Agréments
            </a>

            {{-- ▸ Section RAPPORTS --}}
            <p class="px-3 pt-5 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Rapports</p>

            {{-- Statistiques --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Statistiques
            </a>

            {{-- Rapports PDF/Excel --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Rapports PDF/Excel
            </a>

            {{-- Alertes --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Alertes
            </a>

            {{-- ▸ Section ADMINISTRATION --}}
            <p class="px-3 pt-5 pb-2 text-xs font-bold uppercase tracking-widest"
               style="color: rgba(255,255,255,0.35);">Administration</p>

            {{-- Utilisateurs --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Utilisateurs
            </a>

            {{-- Paramètres --}}
            <a href="#"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 hover:bg-white/10"
               style="color: rgba(255,255,255,0.75);">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Paramètres
            </a>

        </nav>

    </aside>{{-- /sidebar --}}

    {{-- ═══════════════════════════════════════════════════════════════════════
        ZONE PRINCIPALE
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- ── Header ────────────────────────────────────────────────────── --}}
        <header class="bg-white border-b border-gray-200 px-6 py-4 shrink-0">
            <div class="flex items-center justify-between">

                {{-- Titre + sous-titre de la page --}}
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        @yield('titre', 'Dashboard')
                    </h1>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @yield('sous_titre', '')
                    </p>
                </div>

                {{-- Actions à droite --}}
                <div class="flex items-center gap-4">

                    {{-- Icône cloche avec pastille de notification --}}
                    <button class="relative p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Notifications">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        {{-- Pastille rouge --}}
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                    </button>

                    {{-- Avatar + nom + rôle + dropdown ──────────────────── --}}
                    <div class="relative" x-data="{ ouvert: false }" @click.outside="ouvert = false">
                        <button
                            onclick="toggleDropdown()"
                            class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-gray-100 transition-colors"
                        >
                            {{-- Avatar initiales --}}
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0"
                                 style="background-color: #1a237e;">
                                {{ strtoupper(mb_substr(Auth::user()->prenom, 0, 1)) }}{{ strtoupper(mb_substr(Auth::user()->nom, 0, 1)) }}
                            </div>
                            {{-- Nom + rôle --}}
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-semibold text-gray-800 leading-tight">
                                    {{ Auth::user()->prenom }} {{ Auth::user()->nom }}
                                </p>
                                <p class="text-xs text-gray-400 leading-tight capitalize">
                                    {{ str_replace('_', ' ', Auth::user()->role) }}
                                </p>
                            </div>
                            {{-- Flèche dropdown --}}
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        {{-- Menu dropdown --}}
                        <div id="dropdown-user"
                             class="hidden absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-semibold text-gray-800 truncate">{{ Auth::user()->email }}</p>
                            </div>
                            <a href="#" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Mon profil
                            </a>
                            <div class="border-t border-gray-100 mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </header>

        {{-- ── Contenu scrollable ────────────────────────────────────────── --}}
        <main class="flex-1 overflow-y-auto p-6">
            {{-- Flash succès --}}
            @if (session('statut'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('statut') }}
                </div>
            @endif

            {{-- Flash erreur métier (redirect avec ->with('erreur', '...')) --}}
            @if (session('erreur'))
                <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    {{ session('erreur') }}
                </div>
            @endif

            @yield('contenu')
        </main>

    </div>{{-- /zone principale --}}

</div>{{-- /flex global --}}

{{-- Script dropdown utilisateur --}}
<script>
    function toggleDropdown() {
        const menu = document.getElementById('dropdown-user');
        menu.classList.toggle('hidden');
    }
</script>

{{-- Slot pour les scripts spécifiques à chaque page (Chart.js, etc.) --}}
@stack('scripts')

</body>
</html>
