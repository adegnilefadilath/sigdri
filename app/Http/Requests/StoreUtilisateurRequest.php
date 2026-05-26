<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FormRequest — Validation de la création d'un compte utilisateur
 *
 * Centralise les règles de validation de UtilisateursController@store.
 * Règle métier incluse : un compte de rôle 'industriel' exige une unité liée.
 */
class StoreUtilisateurRequest extends FormRequest
{
    /**
     * Seul un administrateur ou super_admin peut créer des comptes.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'                   => ['required', 'string', 'max:100'],
            'prenom'                => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:200', 'unique:utilisateurs,email'],
            'role'                  => ['required', Rule::in(['super_admin', 'admin', 'agent_mic', 'decideur', 'industriel'])],
            // Obligatoire uniquement si rôle = industriel
            'unite_industrielle_id' => ['required_if:role,industriel', 'nullable', 'exists:unites_industrielles,id'],
            'mot_de_passe'          => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required'                      => 'Le nom est obligatoire.',
            'prenom.required'                   => 'Le prénom est obligatoire.',
            'email.required'                    => 'L\'adresse e-mail est obligatoire.',
            'email.unique'                      => 'Cette adresse e-mail est déjà utilisée.',
            'role.required'                     => 'Le rôle est obligatoire.',
            'role.in'                           => 'Le rôle sélectionné est invalide.',
            'unite_industrielle_id.required_if' => 'L\'unité industrielle est obligatoire pour un compte industriel.',
            'unite_industrielle_id.exists'      => 'L\'unité industrielle sélectionnée n\'existe pas.',
            'mot_de_passe.required'             => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min'                  => 'Le mot de passe doit contenir au moins 8 caractères.',
            'mot_de_passe.confirmed'            => 'La confirmation du mot de passe ne correspond pas.',
        ];
    }
}
