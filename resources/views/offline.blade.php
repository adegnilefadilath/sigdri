<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F97316">
    <title>SIGDRI — Hors-ligne</title>
    {{--
        Page hors-ligne standalone (pas de layout parent).
        Styles inline pour que la page soit 100 % auto-suffisante
        lorsqu'elle est servie depuis le cache du Service Worker.
    --}}
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #1a237e;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: #fff;
        }

        .card {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 24px;
            padding: 48px 40px;
            max-width: 460px;
            width: 100%;
            text-align: center;
        }

        /* Icône cloud avec barre */
        .icon-offline {
            width: 80px;
            height: 80px;
            background: rgba(249, 115, 22, 0.15);
            border: 2px solid rgba(249, 115, 22, 0.4);
            border-radius: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
        }

        h1 {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 12px;
            color: #fff;
        }

        .subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.65);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        /* Indicateur de statut connexion */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 28px;
            transition: all 0.3s;
        }
        .status-badge.offline {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
        }
        .status-badge.online {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #86efac;
        }
        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .dot.offline { background: #ef4444; animation: pulse 2s infinite; }
        .dot.online  { background: #22c55e; }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50%       { opacity: 0.3; }
        }

        /* Message synchronisation */
        .sync-info {
            background: rgba(249, 115, 22, 0.1);
            border: 1px solid rgba(249, 115, 22, 0.3);
            border-radius: 14px;
            padding: 16px 20px;
            font-size: 0.82rem;
            color: rgba(249, 115, 22, 0.9);
            line-height: 1.5;
            margin-bottom: 28px;
            text-align: left;
        }
        .sync-info strong { display: block; margin-bottom: 4px; }

        /* Bouton réessayer */
        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            background-color: #F97316;
            color: #fff;
            font-weight: 700;
            font-size: 0.875rem;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .btn-retry:hover { opacity: 0.88; }

        /* Logo en bas */
        .footer-logo {
            margin-top: 40px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
            letter-spacing: 0.1em;
        }
        .footer-logo strong { color: rgba(249, 115, 22, 0.6); }
    </style>
</head>
<body>

    <div class="card">

        {{-- Icône --}}
        <div class="icon-offline">
            <svg width="40" height="40" fill="none" stroke="#F97316" stroke-width="1.75"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <!-- Nuage -->
                <path d="M18.364 5.636a9 9 0 01-12.728 12.728M9 9a3 3 0 014.243 4.243M3 3l18 18"/>
                <!-- Signal wifi barré -->
                <path d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01M16.95 13.05a7 7 0 00-9.9 0"/>
            </svg>
        </div>

        <h1>Vous êtes hors-ligne</h1>

        <p class="subtitle">
            La connexion au serveur SIGDRI est indisponible.<br>
            Vos données seront synchronisées automatiquement à la reconnexion.
        </p>

        {{-- Indicateur de statut connexion (mis à jour par JS) --}}
        <div class="status-badge offline" id="status-badge">
            <span class="dot offline" id="status-dot"></span>
            <span id="status-text">Hors-ligne</span>
        </div>

        {{-- Information sur la synchronisation --}}
        <div class="sync-info">
            <strong>📋 Mode hors-ligne actif</strong>
            Les déclarations saisies dans SIGDRI sont conservées localement
            et seront envoyées au serveur lorsque la connexion sera rétablie.
        </div>

        {{-- Bouton réessayer --}}
        <a href="/industriel/dashboard" class="btn-retry" id="btn-retry">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Réessayer
        </a>

    </div>

    <p class="footer-logo"><strong>SIGDRI</strong> — Ministère de l'Industrie du Bénin</p>

    <script>
        /* -- Mise à jour de l'indicateur de connexion en temps réel -- */
        function majStatutConnexion() {
            const badge   = document.getElementById('status-badge');
            const dot     = document.getElementById('status-dot');
            const texte   = document.getElementById('status-text');
            const bouton  = document.getElementById('btn-retry');

            if (navigator.onLine) {
                badge.className  = 'status-badge online';
                dot.className    = 'dot online';
                texte.textContent = 'Connexion rétablie — redirection…';
                // Redirection automatique vers le dashboard après 1.5 s
                setTimeout(() => {
                    window.location.href = '/industriel/dashboard';
                }, 1500);
            } else {
                badge.className  = 'status-badge offline';
                dot.className    = 'dot offline';
                texte.textContent = 'Hors-ligne';
            }
        }

        window.addEventListener('online',  majStatutConnexion);
        window.addEventListener('offline', majStatutConnexion);

        // Statut initial
        majStatutConnexion();
    </script>

</body>
</html>
