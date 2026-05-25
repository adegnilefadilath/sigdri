<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — {{ config('app.name', 'SIGDRI') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

{{-- Fond : dégradé bleu profond --}}
<body class="min-h-screen flex items-center justify-center p-4"
      style="background: linear-gradient(135deg, #0d1554 0%, #1a237e 45%, #1565c0 100%);">

<div class="w-full max-w-sm">

    {{-- ═══════════════════════════════════════════════════════════════════════
        CARTE PRINCIPALE
        Divisée en deux zones : header foncé (logo) + corps blanc (formulaire)
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="rounded-2xl shadow-2xl overflow-hidden">

        {{-- ── Zone header : logo SIGDRI + institution ──────────────────────── --}}
        <div class="px-8 py-8 text-center" style="background-color: #1a237e;">

            {{-- Icône bâtiment/usine dans un cercle orange --}}
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4"
                 style="background-color: rgba(249,115,22,0.15); border: 2px solid rgba(249,115,22,0.4);">
                {{-- Icône industrielle (bâtiment usine) --}}
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none"
                     stroke="#F97316" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 21h18M3 7v14M17 7v14M7 7v14M13 7v14"/>
                    <path d="M3 7l4-4 3 4 3-4 4 4"/>
                    <rect x="9" y="14" width="6" height="7"/>
                    <rect x="5" y="10" width="2" height="3"/>
                    <rect x="11" y="10" width="2" height="3"/>
                    <rect x="17" y="10" width="2" height="3"/>
                </svg>
            </div>

            {{-- Nom de l'application en orange --}}
            <h1 class="text-2xl font-black tracking-widest" style="color: #F97316;">
                SIGDRI
            </h1>

            {{-- Sous-titre institution --}}
            <p class="text-xs mt-1 font-medium tracking-wide" style="color: rgba(255,255,255,0.65);">
                Ministère de l'Industrie du Bénin
            </p>

        </div>{{-- /header carte --}}

        {{-- ── Zone formulaire : fond blanc ────────────────────────────────── --}}
        <div class="bg-white px-8 py-7">

            {{-- Titre de bienvenue --}}
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-800">
                    Bon retour&nbsp;&#x1F44B;
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Connectez-vous à votre espace
                </p>
            </div>

            {{-- Message flash (ex : déconnexion réussie) --}}
            @if (session('statut'))
                <div class="mb-4 flex items-center gap-2 px-3 py-2.5 bg-green-50 border border-green-200 text-green-700 rounded-lg text-xs">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('statut') }}
                </div>
            @endif

            {{-- Alerte erreur globale (identifiants incorrects, compte suspendu…) --}}
            @if ($errors->any())
                <div class="mb-4 flex items-start gap-2 px-3 py-2.5 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Formulaire --}}
            <form method="POST" action="{{ route('login.submit') }}" novalidate>
                @csrf

                {{-- Champ : adresse e-mail ──────────────────────────────────── --}}
                <div class="mb-4">
                    <label for="email" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Adresse e-mail
                    </label>
                    <div class="relative">
                        {{-- Icône enveloppe --}}
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="exemple@ministere.bj"
                            class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg border transition-colors
                                   focus:outline-none focus:ring-2 focus:border-transparent
                                   {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-300 bg-gray-50 focus:ring-[#1a237e]' }}"
                        >
                    </div>
                </div>

                {{-- Champ : mot de passe ─────────────────────────────────────── --}}
                <div class="mb-5">
                    <label for="mot_de_passe" class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">
                        Mot de passe
                    </label>
                    <div class="relative">
                        {{-- Icône cadenas --}}
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="mot_de_passe"
                            name="mot_de_passe"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full pl-9 pr-10 py-2.5 text-sm rounded-lg border transition-colors
                                   focus:outline-none focus:ring-2 focus:border-transparent
                                   {{ $errors->has('mot_de_passe') ? 'border-red-400 bg-red-50 focus:ring-red-400' : 'border-gray-300 bg-gray-50 focus:ring-[#1a237e]' }}"
                        >
                        {{-- Bouton œil : afficher / masquer le mot de passe --}}
                        <button
                            type="button"
                            onclick="toggleMdp()"
                            tabindex="-1"
                            aria-label="Afficher ou masquer le mot de passe"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                        >
                            {{-- Œil ouvert (état par défaut : mot de passe masqué) --}}
                            <svg id="icone-oeil-ouvert" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            {{-- Œil barré (état : mot de passe visible) --}}
                            <svg id="icone-oeil-ferme" class="w-4 h-4 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Option "Se souvenir de moi" --}}
                <div class="flex items-center mb-6">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="se_souvenir"
                            value="1"
                            class="w-4 h-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                        >
                        <span class="text-xs text-gray-500">Se souvenir de moi</span>
                    </label>
                </div>

                {{-- Bouton Se connecter — orange arrondi --}}
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-white text-sm font-bold shadow-lg transition-all duration-150
                           hover:opacity-90 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-400"
                    style="background-color: #F97316;"
                >
                    {{-- Icône connexion --}}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Se connecter
                </button>

            </form>

            {{-- Pied de carte : accès réservé avec icône bouclier ──────────── --}}
            <div class="mt-6 flex items-center justify-center gap-1.5 text-xs text-gray-400">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span>Accès réservé aux agents autorisés</span>
            </div>

        </div>{{-- /corps blanc --}}

    </div>{{-- /carte --}}

    {{-- Mention version sous la carte --}}
    <p class="text-center mt-5 text-xs" style="color: rgba(255,255,255,0.35);">
        SIGDRI &copy; {{ date('Y') }} — Ministère de l'Industrie du Bénin
    </p>

</div>{{-- /wrapper --}}

{{-- Script : toggle afficher / masquer le mot de passe --}}
<script>
    function toggleMdp() {
        const champ  = document.getElementById('mot_de_passe');
        const ouvert = document.getElementById('icone-oeil-ouvert');
        const ferme  = document.getElementById('icone-oeil-ferme');

        if (champ.type === 'password') {
            champ.type = 'text';
            ouvert.classList.add('hidden');
            ferme.classList.remove('hidden');
        } else {
            champ.type = 'password';
            ouvert.classList.remove('hidden');
            ferme.classList.add('hidden');
        }
    }
</script>

</body>
</html>
