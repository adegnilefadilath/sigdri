<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest — Validation de la création d'une unité industrielle
 *
 * Centralise les règles de validation de UnitesIndustriellesController@store.
 * Le contrôleur peut désormais type-hinter StoreUniteRequest en lieu et place
 * de Request + $request->validate([...]).
 */
class StoreUniteRequest extends FormRequest
{
    /**
     * Tous les agents MIC et administrateurs connectés peuvent créer des unités.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'denomination'              => ['required', 'string', 'max:200'],
            'numero_immatriculation'    => ['required', 'string', 'max:50',
                                            'unique:unites_industrielles,numero_immatriculation'],
            'secteur_activite'          => ['required', 'string', 'max:150'],
            'departement'               => ['required', 'string', 'max:100'],
            'commune'                   => ['required', 'string', 'max:100'],
            'adresse'                   => ['required', 'string', 'max:255'],
            'coordonnees_geographiques' => ['nullable', 'string', 'max:100'],
            'responsable_nom'           => ['required', 'string', 'max:150'],
            'responsable_fonction'      => ['nullable', 'string', 'max:100'],
            'email'                     => ['required', 'email', 'max:150'],
            'telephone'                 => ['required', 'string', 'max:20'],
            'nombre_employes'           => ['nullable', 'integer', 'min:0'],
            'capacite_production'       => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'denomination.required'           => 'La raison sociale est obligatoire.',
            'numero_immatriculation.required'  => 'Le numéro RCCM est obligatoire.',
            'numero_immatriculation.unique'    => 'Ce numéro RCCM est déjà enregistré dans SIGDRI.',
            'secteur_activite.required'        => 'Le secteur d\'activité est obligatoire.',
            'departement.required'             => 'Le département est obligatoire.',
            'commune.required'                 => 'La commune est obligatoire.',
            'adresse.required'                 => 'Le quartier / l\'adresse est obligatoire.',
            'responsable_nom.required'         => 'Le nom du responsable est obligatoire.',
            'email.required'                   => 'L\'e-mail de contact est obligatoire.',
            'email.email'                      => 'L\'adresse e-mail n\'est pas valide.',
            'telephone.required'               => 'Le téléphone de contact est obligatoire.',
            'nombre_employes.integer'          => 'Le nombre d\'employés doit être un entier.',
            'nombre_employes.min'              => 'Le nombre d\'employés ne peut pas être négatif.',
        ];
    }
}
