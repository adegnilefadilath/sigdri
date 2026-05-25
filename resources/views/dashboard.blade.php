@extends('layouts.app')

@section('titre', 'Dashboard')
@section('sous_titre', 'Vue générale de la production industrielle')

@section('contenu')

{{-- ═══════════════════════════════════════════════════════════════════════════
    CARTES STATISTIQUES — 4 cartes colorées en grille
    Chaque carte : label haut gauche · grand chiffre · sous-label · icône droite
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    {{-- ── Carte 1 : Déclarations reçues (bleue) ────────────────────────── --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #1a237e 0%, #283593 100%);">
        {{-- Cercle décoratif en arrière-plan --}}
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20"
             style="background: rgba(255,255,255,0.3);"></div>

        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Déclarations reçues
                </p>
                <p class="text-4xl font-black mt-1 leading-none">
                    {{ number_format($statistiques['declarations']) }}
                </p>
                <p class="text-xs mt-2 opacity-75">
                    <span class="font-semibold text-yellow-300">{{ $statistiques['declarations_en_attente'] }}</span>
                    en attente de validation
                </p>
            </div>
            {{-- Icône --}}
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Carte 2 : Unités industrielles (orange) ──────────────────────── --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #F97316 0%, #ea580c 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20"
             style="background: rgba(255,255,255,0.3);"></div>

        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Unités industrielles
                </p>
                <p class="text-4xl font-black mt-1 leading-none">
                    {{ number_format($statistiques['unites_industrielles']) }}
                </p>
                <p class="text-xs mt-2 opacity-75">
                    <span class="font-semibold">{{ $statistiques['unites_actives'] }}</span>
                    unités actives
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Carte 3 : Alertes actives (rose/rouge) ───────────────────────── --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20"
             style="background: rgba(255,255,255,0.3);"></div>

        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Alertes actives
                </p>
                <p class="text-4xl font-black mt-1 leading-none">
                    {{ number_format($statistiques['alertes']) }}
                </p>
                <p class="text-xs mt-2 opacity-75">
                    <span class="font-semibold">{{ $statistiques['agrements_expires'] }}</span>
                    agréments expirés
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Carte 4 : Départements (gris foncé) ─────────────────────────── --}}
    <div class="relative rounded-2xl p-5 text-white overflow-hidden"
         style="background: linear-gradient(135deg, #374151 0%, #1f2937 100%);">
        <div class="absolute -right-4 -top-4 w-24 h-24 rounded-full opacity-20"
             style="background: rgba(255,255,255,0.3);"></div>

        <div class="flex items-start justify-between relative z-10">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider opacity-80">
                    Départements
                </p>
                <p class="text-4xl font-black mt-1 leading-none">
                    {{ number_format($statistiques['departements']) }}
                </p>
                <p class="text-xs mt-2 opacity-75">
                    Couverts à travers le Bénin
                </p>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                 style="background: rgba(255,255,255,0.15);">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
        </div>
    </div>

</div>{{-- /cartes stats --}}

{{-- ═══════════════════════════════════════════════════════════════════════════
    LIGNE INFÉRIEURE : Graphique filières + État matières premières
════════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">

    {{-- ── Graphique "Unités par filière" (Chart.js) — 3/5 de la largeur ── --}}
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800">Unités par filière</h3>
                <p class="text-xs text-gray-400 mt-0.5">Répartition des unités industrielles par secteur</p>
            </div>
            {{-- Légende couleur --}}
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm inline-block" style="background-color: #1a237e;"></span>
                <span class="text-xs text-gray-500">Unités enregistrées</span>
            </div>
        </div>

        {{-- Canvas Chart.js --}}
        <div class="relative h-52">
            <canvas id="graphique-filieres"></canvas>
        </div>
    </div>

    {{-- ── État des matières premières — 2/5 de la largeur ─────────────── --}}
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
        <div class="mb-5">
            <h3 class="text-sm font-bold text-gray-800">État des matières premières</h3>
            <p class="text-xs text-gray-400 mt-0.5">Disponibilité déclarée ce trimestre</p>
        </div>

        {{-- Légende + barres --}}
        <div class="space-y-4">

            {{-- Disponible --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="text-sm font-medium text-gray-700">Disponible</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $statistiques['matieres']['disponible_pct'] }}%
                    </span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                         style="width: {{ $statistiques['matieres']['disponible_pct'] }}%;"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $statistiques['matieres']['disponible'] }} matières en stock suffisant
                </p>
            </div>

            {{-- Tension --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shrink-0"></span>
                        <span class="text-sm font-medium text-gray-700">Tension</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $statistiques['matieres']['tension_pct'] }}%
                    </span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-amber-400 transition-all duration-500"
                         style="width: {{ $statistiques['matieres']['tension_pct'] }}%;"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $statistiques['matieres']['tension'] }} matières en approvisionnement tendu
                </p>
            </div>

            {{-- Rupture --}}
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                        <span class="text-sm font-medium text-gray-700">Rupture</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $statistiques['matieres']['rupture_pct'] }}%
                    </span>
                </div>
                <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-red-500 transition-all duration-500"
                         style="width: {{ $statistiques['matieres']['rupture_pct'] }}%;"></div>
                </div>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $statistiques['matieres']['rupture'] }} matières en rupture de stock
                </p>
            </div>

        </div>

        {{-- Total entrées --}}
        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs text-gray-400">Total matières suivies</span>
            <span class="text-sm font-bold text-gray-800">
                {{ $statistiques['matieres']['disponible'] + $statistiques['matieres']['tension'] + $statistiques['matieres']['rupture'] }}
            </span>
        </div>
    </div>

</div>{{-- /ligne inférieure --}}

@endsection

{{-- ════════════════════════════════════════════════════════════════════════════
    SCRIPTS — Chart.js injecté dans le @stack('scripts') du layout
════════════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function () {
    // Données des filières passées depuis le contrôleur via JSON
    const labels  = @json($statistiques['filieres_labels']);
    const valeurs = @json($statistiques['filieres_valeurs']);

    const ctx = document.getElementById('graphique-filieres');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Unités enregistrées',
                data: valeurs,
                backgroundColor: '#1a237e',
                borderRadius: 6,
                borderSkipped: false,
                barThickness: 28,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1a237e',
                    titleColor: '#fff',
                    bodyColor: 'rgba(255,255,255,0.8)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        // Affiche "X unité(s)" dans le tooltip
                        label: ctx => ' ' + ctx.parsed.y + ' unité' + (ctx.parsed.y > 1 ? 's' : '')
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: '#9ca3af',
                        // Tronque les labels longs pour éviter le chevauchement
                        callback: function(val) {
                            const label = this.getLabelForValue(val);
                            return label.length > 12 ? label.slice(0, 12) + '…' : label;
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6', drawBorder: false },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                        color: '#9ca3af'
                    }
                }
            }
        }
    });
})();
</script>
@endpush
