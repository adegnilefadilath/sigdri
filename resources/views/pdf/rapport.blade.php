<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport SIGDRI — {{ $genereeLe }}</title>
    <style>
        /* ── Typographie UTF-8 compatible DomPDF ───────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #1a202c;
            background: #fff;
            padding: 20px 28px;
        }

        /* ── En-tête officiel ──────────────────────────────────────────────── */
        .header {
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-gauche {
            width: 58%;
            vertical-align: middle;
            padding-right: 16px;
            border-right: 2px solid #1a237e;
        }
        .header-droite {
            width: 42%;
            vertical-align: middle;
            text-align: right;
            padding-left: 16px;
        }
        .republique {
            font-size: 10pt;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .ministere {
            font-size: 9pt;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 2px;
        }
        .direction {
            font-size: 8.5pt;
            color: #4a5568;
            font-style: italic;
        }
        .sigdri-acronyme {
            font-size: 26pt;
            font-weight: bold;
            color: #1a237e;
            letter-spacing: 3px;
            line-height: 1;
            margin-bottom: 4px;
        }
        .sigdri-intitule {
            font-size: 7.5pt;
            color: #4a5568;
            line-height: 1.4;
        }
        .header-separateur {
            border-bottom: 3px solid #1a237e;
            margin-bottom: 0;
        }
        .header-meta {
            margin-top: 7px;
            font-size: 7.5pt;
            color: #718096;
            text-align: right;
        }
        .header-meta .badge {
            display: inline-block;
            background-color: #F97316;
            color: #fff;
            padding: 2px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7.5pt;
            margin-right: 6px;
        }

        /* ── Sous-titre / filtres actifs ──────────────────────────────────── */
        .subtitle-bar {
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 14px;
            font-size: 8pt;
            color: #4a5568;
        }
        .subtitle-bar strong { color: #1a237e; }

        /* ── Cartes statistiques ──────────────────────────────────────────── */
        .stats-grid {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .stat-card {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 8px 10px;
            text-align: center;
        }
        .stat-card .label {
            font-size: 7.5pt;
            color: #718096;
            margin-bottom: 4px;
        }
        .stat-card .value {
            font-size: 13pt;
            font-weight: bold;
            color: #1a237e;
        }
        .stat-card.orange .value { color: #F97316; }
        .stat-card.green  .value { color: #276749; }
        .stat-card.teal   .value { color: #319795; }

        /* ── Titres de sections ────────────────────────────────────────────── */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1a237e;
            border-left: 4px solid #F97316;
            padding-left: 8px;
            margin: 14px 0 8px;
        }

        /* ── Tableaux ──────────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        thead tr {
            background-color: #1a237e;
            color: #fff;
        }
        thead th {
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
        }
        tbody tr:nth-child(even) { background-color: #f7fafc; }
        tbody tr:nth-child(odd)  { background-color: #ffffff; }
        tbody td {
            padding: 4px 6px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Badge statut ──────────────────────────────────────────────────── */
        .badge-statut {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-validee   { background: #c6f6d5; color: #276749; }
        .badge-soumise   { background: #bee3f8; color: #2b6cb0; }
        .badge-rejetee   { background: #fed7d7; color: #c53030; }
        .badge-brouillon { background: #e2e8f0; color: #4a5568; }

        /* ── Tableau secteur (barres visuelles) ─────────────────────────────── */
        .secteur-bar-bg {
            height: 10px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 2px;
        }
        .secteur-bar {
            height: 10px;
            background: #1a237e;
            border-radius: 2px;
        }

        /* ── Pied de page ──────────────────────────────────────────────────── */
        .footer {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 7.5pt;
            color: #a0aec0;
            display: flex;
            justify-content: space-between;
        }
        .footer strong { color: #4a5568; }

        /* ── Avertissement tronquage ───────────────────────────────────────── */
        .notice {
            font-size: 7.5pt;
            color: #e53e3e;
            font-style: italic;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

    {{-- ══ EN-TÊTE OFFICIEL ════════════════════════════════════════════════ --}}
    <div class="header">

        <table class="header-table">
            <tr>
                {{-- Colonne gauche : institution ──────────────────────────── --}}
                <td class="header-gauche">
                    <p class="republique">République du Bénin</p>
                    <p class="ministere">Ministère de l'Industrie et du Commerce</p>
                    <p class="direction">Direction Générale de l'Industrie</p>
                </td>

                {{-- Colonne droite : sigle et intitulé système ─────────────── --}}
                <td class="header-droite">
                    <p class="sigdri-acronyme">SIGDRI</p>
                    <p class="sigdri-intitule">
                        Système Intégré de Gestion des Déclarations<br>
                        et du Reporting Industriel
                    </p>
                </td>
            </tr>
        </table>

        {{-- Ligne de séparation bleue foncée ──────────────────────────────── --}}
        <div class="header-separateur"></div>

        {{-- Badge + date de génération ────────────────────────────────────── --}}
        <div class="header-meta">
            <span class="badge">RAPPORT OFFICIEL</span>
            Généré le {{ $genereeLe }}
        </div>

    </div>

    {{-- ══ FILTRES ACTIFS ═══════════════════════════════════════════════════ --}}
    <div class="subtitle-bar">
        <strong>Filtres appliqués :</strong>
        Département :
        <strong>{{ $filtres['departement'] ?: 'Tous' }}</strong> &nbsp;|&nbsp;
        Secteur :
        <strong>{{ $filtres['secteur'] ?: 'Tous' }}</strong> &nbsp;|&nbsp;
        Mois :
        <strong>
            @php
                $nomsM = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                          'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
            @endphp
            {{ $filtres['mois'] ? ($nomsM[(int)$filtres['mois']] ?? $filtres['mois']) : 'Tous' }}
        </strong> &nbsp;|&nbsp;
        Année :
        <strong>{{ $filtres['annee'] ?: 'Toutes' }}</strong> &nbsp;|&nbsp;
        Statut :
        <strong>{{ $filtres['statut'] ?: 'Tous' }}</strong>
    </div>

    {{-- ══ CARTES STATISTIQUES ═════════════════════════════════════════════ --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Total déclarations</div>
            <div class="value">{{ number_format($stats['total_declarations']) }}</div>
        </div>
        <div class="stat-card orange">
            <div class="label">CA Total (FCFA)</div>
            <div class="value">{{ number_format($stats['ca_total'], 0, ',', ' ') }}</div>
        </div>
        <div class="stat-card green">
            <div class="label">Unités déclarantes</div>
            <div class="value">{{ number_format($stats['unites_declarantes']) }}</div>
        </div>
        <div class="stat-card teal">
            <div class="label">Déclarations validées</div>
            <div class="value">{{ number_format($stats['declarations_validees']) }}</div>
        </div>
    </div>

    {{-- ══ STATISTIQUES PAR SECTEUR ════════════════════════════════════════ --}}
    @if ($parSecteur->isNotEmpty())
        <div class="section-title">Statistiques par secteur d'activité</div>

        @php
            $caMax = $parSecteur->max('ca_total') ?: 1;
        @endphp

        <table>
            <thead>
                <tr>
                    <th>Secteur d'activité</th>
                    <th class="text-center">Nb déclarations</th>
                    <th class="text-right">CA Total (FCFA)</th>
                    <th style="width:120px;">Proportion CA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($parSecteur as $s)
                    @php $pct = round(($s->ca_total / $caMax) * 100); @endphp
                    <tr>
                        <td>{{ $s->secteur_activite ?? 'N/A' }}</td>
                        <td class="text-center">{{ number_format($s->nb_declarations) }}</td>
                        <td class="text-right">{{ number_format($s->ca_total, 0, ',', ' ') }}</td>
                        <td>
                            <div class="secteur-bar-bg">
                                <div class="secteur-bar" style="width:{{ $pct }}%;"></div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══ LISTE DES DÉCLARATIONS ══════════════════════════════════════════ --}}
    <div class="section-title">
        Liste des déclarations
        @if ($declarations->count() >= 500)
            <span class="notice">(limité aux 500 premières lignes)</span>
        @endif
    </div>

    @if ($declarations->isEmpty())
        <p style="color:#718096; font-style:italic; margin-bottom:8px;">
            Aucune déclaration pour les filtres sélectionnés.
        </p>
    @else
        <table>
            <thead>
                <tr>
                    <th>N° Déclaration</th>
                    <th>Unité industrielle</th>
                    <th>Département</th>
                    <th>Secteur</th>
                    <th class="text-center">Mois déclaré</th>
                    <th>Statut</th>
                    <th class="text-right">CA (FCFA)</th>
                    <th class="text-center">Date soumission</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($declarations as $d)
                    <tr>
                        <td>{{ $d->numero_declaration }}</td>
                        <td>{{ $d->denomination_unite }}</td>
                        <td>{{ $d->departement_unite }}</td>
                        <td>{{ $d->secteur_activite }}</td>
                        <td class="text-center">
                            {{ ($nomsM[$d->mois] ?? '?') . ' ' . $d->annee }}
                        </td>
                        <td>
                            <span class="badge-statut badge-{{ $d->statut }}">
                                {{ ucfirst($d->statut) }}
                            </span>
                        </td>
                        <td class="text-right">
                            {{ number_format($d->chiffre_affaires_total, 0, ',', ' ') }}
                        </td>
                        <td class="text-center">
                            {{ $d->date_soumission ? date('d/m/Y', strtotime($d->date_soumission)) : '—' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ══ PIED DE PAGE ════════════════════════════════════════════════════ --}}
    <div class="footer">
        <span>
            <strong>SIGDRI</strong> — Ministère de l'Industrie et du Commerce du Bénin
        </span>
        <span>
            Document généré automatiquement le {{ $genereeLe }} — Confidentiel
        </span>
    </div>

</body>
</html>
