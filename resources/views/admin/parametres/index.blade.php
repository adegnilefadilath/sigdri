@extends('layouts.app')

@section('titre', 'Paramètres système')
@section('sous_titre', 'Configuration générale de la plateforme SIGDRI')

@section('contenu')

<form method="POST" action="{{ route('admin.parametres.update') }}">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Colonne principale (2/3) ─────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Section : Identité de la plateforme --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">Identité de la plateforme</h3>
                <p class="text-xs text-gray-400 mb-4">
                    Informations affichées dans l'interface et les documents officiels.
                </p>

                @foreach ($parametres->whereIn('cle', ['nom_plateforme', 'nom_ministere', 'direction_generale']) as $p)
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ $p->libelle }}
                    </label>
                    <input type="{{ $p->type }}"
                           name="parametres[{{ $p->cle }}]"
                           value="{{ old('parametres.' . $p->cle, $p->valeur) }}"
                           class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                  {{ $errors->has('parametres.' . $p->cle) ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @if ($p->aide)
                        <p class="text-xs text-gray-400 mt-1">{{ $p->aide }}</p>
                    @endif
                    @error('parametres.' . $p->cle)
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>

            {{-- Section : Contacts et délais --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-1">Contacts et délais</h3>
                <p class="text-xs text-gray-400 mb-4">
                    Adresse de contact officielle et seuils d'alerte.
                </p>

                @foreach ($parametres->whereIn('cle', ['email_contact_ministere', 'delai_validation_declarations']) as $p)
                <div class="mb-4">
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ $p->libelle }}
                    </label>
                    <input type="{{ $p->type }}"
                           name="parametres[{{ $p->cle }}]"
                           value="{{ old('parametres.' . $p->cle, $p->valeur) }}"
                           @if ($p->type === 'number') min="1" max="365" @endif
                           class="w-full rounded-lg border text-sm px-3 py-2 focus:outline-none
                                  {{ $errors->has('parametres.' . $p->cle) ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                    @if ($p->aide)
                        <p class="text-xs text-gray-400 mt-1">{{ $p->aide }}</p>
                    @endif
                    @error('parametres.' . $p->cle)
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endforeach
            </div>

        </div>

        {{-- ── Colonne latérale (1/3) ───────────────────────────────────────── --}}
        <div class="space-y-5">

            {{-- Bouton de sauvegarde --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-lg text-sm font-medium text-white shadow-sm"
                        style="background-color:#1a237e;">
                    Enregistrer les paramètres
                </button>
                <p class="text-xs text-gray-400 text-center mt-2">
                    Les modifications sont appliquées immédiatement.
                </p>
            </div>

            {{-- Note d'information --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5"
                         style="background:rgba(26,35,126,0.08);">
                        <svg class="w-4 h-4" style="color:#1a237e;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-700 mb-1">À propos des paramètres</p>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Ces paramètres sont enregistrés en base de données. Ils prennent effet
                            immédiatement sans redémarrage du serveur.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Accès rapide --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Accès rapide</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.utilisateurs.index') }}"
                       class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 py-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Gérer les utilisateurs
                    </a>
                    <a href="{{ route('admin.alertes.index') }}"
                       class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 py-1">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Voir les alertes
                    </a>
                </div>
            </div>

        </div>
    </div>

</form>

@endsection
