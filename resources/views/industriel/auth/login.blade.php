<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Industriel — SIGDRI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Reset minimal pour éviter tout débordement */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(160deg, #1a237e 0%, #283593 100%);
            overflow-x: hidden;
        }

        /* ── Carte ── */
        .carte {
            width: 90%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 24px 64px rgba(0, 0, 0, 0.35);
        }

        /* ── Champs de formulaire ── */
        .champ-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .champ-icone {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            line-height: 0;
            color: #9ca3af;
        }
        .champ-input {
            width: 100%;
            padding: 12px 12px 12px 38px;
            font-size: 14px;
            color: #111827;
            background: #f9fafb;
            border: 1.5px solid #d1d5db;
            border-radius: 10px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }
        .champ-input:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.12);
            background: #fff;
        }
        .champ-input.erreur {
            border-color: #f87171;
            background: #fef2f2;
        }
        .champ-input.erreur:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }
        /* Décalage droite pour le champ mot de passe (bouton œil) */
        .champ-input.avec-oeil { padding-right: 44px; }

        /* ── Bouton œil ── */
        .btn-oeil {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            color: #9ca3af;
            line-height: 0;
            transition: color 0.15s;
        }
        .btn-oeil:hover { color: #4b5563; }

        /* ── Label ── */
        .label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 6px;
        }

        /* ── Bouton principal ── */
        .btn-connexion {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            background-color: #F97316;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: opacity 0.15s, transform 0.1s;
            box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4);
        }
        .btn-connexion:hover  { opacity: 0.92; }
        .btn-connexion:active { transform: scale(0.98); }
        .btn-connexion:focus  { outline: 3px solid rgba(249,115,22,0.4); outline-offset: 2px; }

        /* ── Alertes flash ── */
        .alerte {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .alerte.succes {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }
        .alerte.erreur {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* ── Checkbox ── */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
            font-size: 13px;
            color: #6b7280;
        }
        .checkbox-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #F97316;
            flex-shrink: 0;
            cursor: pointer;
        }

        /* ── Séparateur ── */
        .separateur {
            border: none;
            border-top: 1px solid #f3f4f6;
            margin: 20px 0 0;
        }
    </style>
</head>
<body>

<div class="carte">

    {{-- ── En-tête : SIGDRI + sous-titre ── --}}
    <div style="text-align:center; margin-bottom:1.75rem;">
        <p style="font-size:28px; font-weight:800; color:#F97316; letter-spacing:0.06em; line-height:1;">
            SIGDRI
        </p>
        <p style="font-size:13px; color:#9ca3af; margin-top:4px; letter-spacing:0.03em;">
            Espace Industriel
        </p>
    </div>

    {{-- ── Titre du formulaire ── --}}
    <div style="margin-bottom:1.5rem;">
        <h1 style="font-size:22px; font-weight:700; color:#111827; line-height:1.2;">
            Bon retour &#x1F44B;
        </h1>
        <p style="font-size:13px; color:#6b7280; margin-top:4px;">
            Connectez-vous à votre espace
        </p>
    </div>

    {{-- ── Flash déconnexion ── --}}
    @if (session('statut'))
    <div class="alerte succes">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
            <path d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('statut') }}</span>
    </div>
    @endif

    {{-- ── Erreur de connexion ── --}}
    @if ($errors->any())
    <div class="alerte erreur">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
            <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    {{-- ── Formulaire ── --}}
    <form method="POST" action="{{ route('industriel.login.submit') }}" novalidate>
        @csrf

        {{-- Numéro d'agrément --}}
        <div style="margin-bottom:14px;">
            <label for="numero_agrement" class="label">Numéro d'agrément</label>
            <div class="champ-wrapper">
                <span class="champ-icone">
                    {{-- Icône bouclier 18 px — width/height explicites --}}
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </span>
                <input type="text"
                       id="numero_agrement"
                       name="numero_agrement"
                       value="{{ old('numero_agrement') }}"
                       required
                       autofocus
                       autocomplete="off"
                       placeholder="ex: AGR-2024-001"
                       class="champ-input {{ $errors->has('numero_agrement') ? 'erreur' : '' }}">
            </div>
        </div>

        {{-- Mot de passe --}}
        <div style="margin-bottom:16px;">
            <label for="mot_de_passe" class="label">Mot de passe</label>
            <div class="champ-wrapper">
                <span class="champ-icone">
                    {{-- Icône cadenas 18 px — width/height explicites --}}
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0110 0v4"/>
                    </svg>
                </span>
                <input type="password"
                       id="mot_de_passe"
                       name="mot_de_passe"
                       required
                       autocomplete="current-password"
                       placeholder="••••••••"
                       class="champ-input avec-oeil {{ $errors->has('mot_de_passe') ? 'erreur' : '' }}">
                {{-- Bouton afficher/masquer le mot de passe --}}
                <button type="button"
                        onclick="toggleMdp()"
                        tabindex="-1"
                        aria-label="Afficher ou masquer le mot de passe"
                        class="btn-oeil">
                    {{-- Œil ouvert (visible par défaut) --}}
                    <svg id="icone-oeil-ouvert" width="18" height="18" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{-- Œil barré (masqué par défaut) --}}
                    <svg id="icone-oeil-ferme" width="18" height="18" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         viewBox="0 0 24 24" aria-hidden="true" style="display:none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Se souvenir de moi --}}
        <div style="margin-bottom:20px;">
            <label class="checkbox-label">
                <input type="checkbox" name="se_souvenir" value="1">
                Se souvenir de moi
            </label>
        </div>

        {{-- Bouton Se connecter --}}
        <button type="submit" class="btn-connexion">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            Se connecter
        </button>

    </form>

    {{-- ── Pied de carte ── --}}
    <hr class="separateur">
    <p style="text-align:center; margin-top:14px; font-size:12px; color:#d1d5db;">
        Accès réservé aux agents autorisés
    </p>
    <p style="text-align:center; margin-top:8px; font-size:12px; color:#e5e7eb;">
        <a href="{{ route('login') }}"
           style="color:#d1d5db; text-decoration:none; transition:color 0.15s;"
           onmouseover="this.style.color='#6b7280'"
           onmouseout="this.style.color='#d1d5db'">
            → Espace administratif
        </a>
    </p>

</div>{{-- /carte --}}

{{-- Copyright sous la carte --}}
<p style="position:fixed; bottom:16px; left:0; right:0; text-align:center;
          font-size:11px; color:rgba(255,255,255,0.3); pointer-events:none;">
    SIGDRI &copy; {{ date('Y') }} — Espace Industriel
</p>

<script>
    function toggleMdp() {
        const champ  = document.getElementById('mot_de_passe');
        const ouvert = document.getElementById('icone-oeil-ouvert');
        const ferme  = document.getElementById('icone-oeil-ferme');

        if (champ.type === 'password') {
            champ.type        = 'text';
            ouvert.style.display = 'none';
            ferme.style.display  = 'inline';
        } else {
            champ.type        = 'password';
            ouvert.style.display = 'inline';
            ferme.style.display  = 'none';
        }
    }
</script>
</body>
</html>
