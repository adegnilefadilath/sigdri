@extends('layouts.app')

@section('titre', 'Reporting & Statistiques')

@section('contenu')
<div class="space-y-6">

    {{-- ── En-tête de page ──────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reporting & Statistiques</h1>
            <p class="text-sm text-gray-500 mt-0.5">Analyse des déclarations industrielles</p>
        </div>
        {{-- Boutons d'export --}}
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.reporting.export-pdf', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm transition-colors"
               style="background-color: #e53e3e;" target="_blank">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                PDF
            </a>
            <a href="{{ route('admin.reporting.export-excel', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-white shadow-sm transition-colors"
               style="background-color: #276749;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                Excel (.xls)
            </a>

        </div>
    </div>

    {{-- ── Formulaire de filtres ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <form method="GET" action="{{ route('admin.reporting.index') }}" id="filtreForm"
              class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">

            {{-- Département --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Département</label>
                <select name="departement"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-2"
                        style="focus:ring-color: #1a237e;">
                    <option value="">Tous</option>
                    @foreach ($departements as $dep)
                        <option value="{{ $dep }}" {{ $filtres['departement'] === $dep ? 'selected' : '' }}>
                            {{ $dep }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Secteur --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Secteur d'activité</label>
                <select name="secteur"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-2">
                    <option value="">Tous</option>
                    @foreach ($secteurs as $sec)
                        <option value="{{ $sec }}" {{ $filtres['secteur'] === $sec ? 'selected' : '' }}>
                            {{ $sec }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Mois --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Mois</label>
                <select name="mois"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-2">
                    <option value="">Tous</option>
                    @foreach (['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin',
                               '7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre']
                              as $num => $nom)
                        <option value="{{ $num }}" {{ $filtres['mois'] === (string)$num ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Année --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Année</label>
                <select name="annee"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-2">
                    <option value="">Toutes</option>
                    @foreach ($annees as $a)
                        <option value="{{ $a }}" {{ $filtres['annee'] === (string)$a ? 'selected' : '' }}>
                            {{ $a }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Statut --}}
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                <select name="statut"
                        class="w-full rounded-lg border border-gray-200 text-sm px-3 py-2 focus:outline-none focus:ring-2">
                    <option value="">Tous</option>
                    <option value="soumise"   {{ $filtres['statut'] === 'soumise'   ? 'selected' : '' }}>Soumise</option>
                    <option value="validee"   {{ $filtres['statut'] === 'validee'   ? 'selected' : '' }}>Validée</option>
                    <option value="rejetee"   {{ $filtres['statut'] === 'rejetee'   ? 'selected' : '' }}>Rejetée</option>
                    <option value="brouillon" {{ $filtres['statut'] === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                </select>
            </div>

            {{-- Boutons filtre --}}
            <div class="col-span-2 sm:col-span-3 lg:col-span-5 flex gap-2 pt-1">
                <button type="submit"
                        class="px-5 py-2 rounded-lg text-sm font-medium text-white shadow-sm"
                        style="background-color: #1a237e;">
                    Appliquer les filtres
                </button>
                <a href="{{ route('admin.reporting.index') }}"
                   class="px-5 py-2 rounded-lg text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200">
                    Réinitialiser
                </a>
            </div>

        </form>
    </div>

    {{-- ── 4 Cartes statistiques ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4" id="statsCards">

        {{-- Total déclarations --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Total déclarations</p>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background-color: rgba(26,35,126,0.08);">
                    <svg class="w-5 h-5" fill="none" stroke="#1a237e" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900" id="stat-total">
                {{ number_format($stats['total_declarations']) }}
            </p>
        </div>

        {{-- CA Total --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">CA Total (FCFA)</p>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background-color: rgba(249,115,22,0.08);">
                    <svg class="w-5 h-5" fill="none" stroke="#F97316" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900" id="stat-ca">
                {{ number_format($stats['ca_total'], 0, ',', ' ') }}
            </p>
        </div>

        {{-- Unités déclarantes --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Unités déclarantes</p>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background-color: rgba(56,161,105,0.08);">
                    <svg class="w-5 h-5" fill="none" stroke="#38a169" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900" id="stat-unites">
                {{ number_format($stats['unites_declarantes']) }}
            </p>
        </div>

        {{-- Déclarations validées --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm font-medium text-gray-500">Déclarations validées</p>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background-color: rgba(49,151,149,0.08);">
                    <svg class="w-5 h-5" fill="none" stroke="#319795" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900" id="stat-validees">
                {{ number_format($stats['declarations_validees']) }}
            </p>
        </div>

    </div>

    {{-- ── Graphiques ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Graphique barres : CA par secteur --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">CA par secteur d'activité</h2>
            <div class="relative" style="height: 280px;">
                <canvas id="chartSecteur"></canvas>
            </div>
        </div>

        {{-- Graphique camembert : répartition par département --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Répartition par département</h2>
            <div class="relative" style="height: 280px;">
                <canvas id="chartDepartement"></canvas>
            </div>
        </div>

    </div>

    {{-- Graphique courbe : évolution mensuelle --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Évolution mensuelle des déclarations</h2>
        <div class="relative" style="height: 260px;">
            <canvas id="chartEvolution"></canvas>
        </div>
    </div>

</div>
@endsection

@push('scripts')
{{-- Chart.js via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// ── Données PHP → JS ────────────────────────────────────────────────────────
const dataSecteur     = @json($chartSecteur);
const dataEvolution   = @json($chartEvolution);
const dataDepartement = @json($chartDepartement);

// Palette de couleurs SIGDRI
const couleursPalette = [
    '#1A237E', '#F97316', '#38A169', '#319795', '#805AD5',
    '#D69E2E', '#E53E3E', '#3182CE', '#DD6B20', '#2D3748',
];

// ── Graphique barres — CA par secteur ───────────────────────────────────────
const ctxSecteur = document.getElementById('chartSecteur').getContext('2d');
new Chart(ctxSecteur, {
    type: 'bar',
    data: {
        labels: dataSecteur.map(d => d.secteur_activite ?? 'N/A'),
        datasets: [{
            label: 'CA Total (FCFA)',
            data: dataSecteur.map(d => parseFloat(d.ca_total) || 0),
            backgroundColor: dataSecteur.map((_, i) => couleursPalette[i % couleursPalette.length] + 'CC'),
            borderColor:     dataSecteur.map((_, i) => couleursPalette[i % couleursPalette.length]),
            borderWidth: 1,
            borderRadius: 4,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' FCFA',
                },
            },
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(val),
                },
            },
            x: { ticks: { maxRotation: 30 } },
        },
    },
});

// ── Graphique camembert — répartition par département ───────────────────────
const ctxDept = document.getElementById('chartDepartement').getContext('2d');
new Chart(ctxDept, {
    type: 'doughnut',
    data: {
        labels: dataDepartement.map(d => d.departement ?? 'N/A'),
        datasets: [{
            data: dataDepartement.map(d => parseInt(d.nb_declarations) || 0),
            backgroundColor: dataDepartement.map((_, i) => couleursPalette[i % couleursPalette.length] + 'CC'),
            borderColor: '#fff',
            borderWidth: 2,
        }],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 11 }, padding: 8, boxWidth: 12 },
            },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label} : ${ctx.raw} déclaration(s)`,
                },
            },
        },
        cutout: '60%',
    },
});

// ── Graphique courbe — évolution mensuelle ──────────────────────────────────
const nomsM = ['','Janv','Févr','Mars','Avr','Mai','Juin','Juil','Août','Sept','Oct','Nov','Déc'];
const labelsEvo = dataEvolution.map(d => nomsM[d.mois] + ' ' + d.annee);

const ctxEvo = document.getElementById('chartEvolution').getContext('2d');
new Chart(ctxEvo, {
    type: 'line',
    data: {
        labels: labelsEvo,
        datasets: [
            {
                label: 'Nb déclarations',
                data: dataEvolution.map(d => parseInt(d.nb_declarations) || 0),
                borderColor: '#F97316',
                backgroundColor: 'rgba(249,115,22,0.08)',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                yAxisID: 'yGauche',
            },
            {
                label: 'CA Total (FCFA)',
                data: dataEvolution.map(d => parseFloat(d.ca_total) || 0),
                borderColor: '#1A237E',
                backgroundColor: 'rgba(26,35,126,0.05)',
                tension: 0.35,
                fill: false,
                pointRadius: 4,
                yAxisID: 'yDroite',
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { position: 'top', labels: { font: { size: 12 } } },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        if (ctx.datasetIndex === 1) {
                            return ' CA : ' + new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' FCFA';
                        }
                        return ' ' + ctx.raw + ' déclaration(s)';
                    },
                },
            },
        },
        scales: {
            yGauche: {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                title: { display: true, text: 'Déclarations' },
            },
            yDroite: {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                grid: { drawOnChartArea: false },
                ticks: {
                    callback: val => new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(val),
                },
                title: { display: true, text: 'CA (FCFA)' },
            },
            x: { ticks: { maxRotation: 45 } },
        },
    },
});
</script>
@endpush
