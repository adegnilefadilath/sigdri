<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès refusé — SIGDRI</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            min-height: 100vh; padding: 20px;
        }
        .carte {
            background: #fff; border-radius: 16px; padding: 48px 40px;
            max-width: 480px; width: 100%; text-align: center;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .code {
            font-size: 72px; font-weight: 900; line-height: 1;
            color: #e53e3e; letter-spacing: -2px;
        }
        .trait {
            width: 48px; height: 4px; border-radius: 2px;
            background: #F97316; margin: 16px auto;
        }
        .titre { font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 8px; }
        .message { font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 28px; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            background: #1a237e; color: #fff; text-decoration: none;
            padding: 10px 24px; border-radius: 8px; font-size: 14px; font-weight: 600;
        }
        .lien-retour {
            display: block; margin-top: 14px; font-size: 13px; color: #94a3b8;
            text-decoration: none;
        }
        .lien-retour:hover { color: #1a237e; }
        .sigdri { font-size: 12px; color: #cbd5e1; margin-top: 32px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="carte">
        <div class="code">403</div>
        <div class="trait"></div>
        <h1 class="titre">Accès refusé</h1>
        <p class="message">
            Vous n'avez pas les droits nécessaires pour accéder à cette page.<br>
            Contactez un administrateur si vous pensez qu'il s'agit d'une erreur.<br>
            @if (!empty($message))
                <em style="font-size:12px;color:#94a3b8;">{{ $message }}</em>
            @endif
        </p>
        <a href="{{ url('/dashboard') }}" class="btn">
            ← Retour au tableau de bord
        </a>
        <a href="javascript:history.back()" class="lien-retour">
            Page précédente
        </a>
        <div class="sigdri">SIGDRI — Ministère de l'Industrie</div>
    </div>
</body>
</html>
