<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Journal d'audit — SIGDRI</title>
    <style>
        /* ── Police DejaVu pour le support UTF-8 complet ── */
        * { font-family: DejaVu Sans, sans-serif; font-size: 9px; }

        body { margin: 0; padding: 20px; color: #1a1a1a; }

        /* ── En-tête officiel ── */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .header-gauche { width: 55%; padding-right: 16px; border-right: 2px solid #1a237e; vertical-align: top; }
        .header-droite { width: 45%; padding-left: 16px; text-align: right; vertical-align: top; }
        .republique    { font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; color: #1a237e; }
        .ministere     { font-size: 7.5px; color: #333; margin-top: 2px; }
        .direction     { font-size: 7px; color: #555; margin-top: 1px; }
        .sigdri-titre  { font-size: 22px; font-weight: bold; color: #1a237e; }
        .sigdri-sous   { font-size: 7px; color: #555; }
        .separateur    { border-bottom: 3px solid #1a237e; margin: 6px 0; }
        .doc-meta      { text-align: center; margin-bottom: 10px; }
        .doc-titre     { font-size: 11px; font-weight: bold; color: #1a237e; }
        .doc-date      { font-size: 8px; color: #666; }

        /* ── Tableau ── */
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th {
            background: #1a237e; color: #fff; padding: 4px 6px;
            text-align: left; font-size: 8px; font-weight: bold;
        }
        table.data td { padding: 3px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #f8f9ff; }
    </style>
</head>
<body>

    {{-- ── En-tête officiel ──────────────────────────────────────────────────── --}}
    <table class="header-table">
        <tr>
            <td class="header-gauche">
                <div class="republique">République du Bénin</div>
                <div class="ministere">Ministère de l'Industrie et du Commerce</div>
                <div class="direction">Direction Générale de l'Industrie</div>
            </td>
            <td class="header-droite">
                <div class="sigdri-titre">SIGDRI</div>
                <div class="sigdri-sous">Système Intégré de Gestion des Déclarations<br>et du Reporting Industriel</div>
            </td>
        </tr>
    </table>
    <div class="separateur"></div>
    <div class="doc-meta">
        <div class="doc-titre">JOURNAL D'AUDIT — TRAÇABILITÉ DES ACTIONS</div>
        <div class="doc-date">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

    {{-- ── Tableau des entrées ────────────────────────────────────────────────── --}}
    <table class="data">
        <thead>
            <tr>
                <th style="width:14%;">Date & heure</th>
                <th style="width:16%;">Utilisateur</th>
                <th style="width:12%;">Action</th>
                <th style="width:42%;">Description</th>
                <th style="width:16%;">Adresse IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($journaux as $j)
            <tr>
                <td>{{ \Carbon\Carbon::parse($j->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ trim($j->auteur) ?: 'Système' }}</td>
                <td>{{ $j->action }}</td>
                <td>{{ $j->description ?? '—' }}</td>
                <td>{{ $j->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:12px; color:#666;">
                    Aucune entrée trouvée.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
