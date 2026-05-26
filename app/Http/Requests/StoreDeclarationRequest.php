<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * FormRequest — Validation et autorisation de la soumission d'une déclaration
 *
 * Centralise les règles de validation de Industriel\DeclarationsController@store.
 * La méthode authorize() vérifie également qu'un agrément valide est actif,
 * ce qui évite de dupliquer ce garde-fou dans le contrôleur.
 */
class StoreDeclarationRequest extends FormRequest
{
    /**
     * L'industriel doit avoir un agrément valide et non expiré pour déclarer.
     */
    public function authorize(): bool
    {
        $uniteId = Auth::user()?->unite_industrielle_id;

        if (! $uniteId) {
            return false;
        }

        return DB::table('agrements')
            ->where('unite_industrielle_id', $uniteId)
            ->where('statut', 'valide')
            ->where(function ($q) {
                $q->whereNull('date_expiration')
                  ->orWhere('date_expiration', '>=', now()->toDateString());
            })
            ->exists();
    }

    public function rules(): array
    {
        return [
            'mois'                   => ['required', 'integer', 'min:1', 'max:12'],
            'annee'                  => ['required', 'integer', 'min:2020', 'max:2100'],
            'chiffre_affaires_total' => ['nullable', 'numeric', 'min:0'],
            'observations'           => ['nullable', 'string', 'max:2000'],
            // Lignes de production (optionnelles)
            'produits'               => ['nullable', 'array'],
            'produits.*.designation' => ['nullable', 'string', 'max:200'],
            'produits.*.unite_mesure'=> ['nullable', 'string', 'max:50'],
            'produits.*.quantite_produite'     => ['nullable', 'numeric', 'min:0'],
            'produits.*.quantite_vendue_local' => ['nullable', 'numeric', 'min:0'],
            'produits.*.quantite_exportee'     => ['nullable', 'numeric', 'min:0'],
            'produits.*.valeur_fcfa'           => ['nullable', 'numeric', 'min:0'],
            // Matières premières (optionnelles)
            'matieres'                => ['nullable', 'array'],
            'matieres.*.designation'  => ['nullable', 'string', 'max:200'],
            'matieres.*.origine'      => ['nullable', 'in:locale,importee'],
            'matieres.*.unite_mesure' => ['nullable', 'string', 'max:50'],
            'matieres.*.quantite_consommee' => ['nullable', 'numeric', 'min:0'],
            'matieres.*.valeur_fcfa'        => ['nullable', 'numeric', 'min:0'],
            'matieres.*.fournisseur'        => ['nullable', 'string', 'max:200'],
        ];
    }

    public function messages(): array
    {
        return [
            'mois.required'  => 'Le mois est obligatoire.',
            'mois.min'       => 'Le mois doit être compris entre 1 et 12.',
            'mois.max'       => 'Le mois doit être compris entre 1 et 12.',
            'annee.required' => 'L\'année est obligatoire.',
            'annee.min'      => 'L\'année doit être supérieure ou égale à 2020.',
            'annee.max'      => 'L\'année ne peut pas dépasser 2100.',
            'chiffre_affaires_total.numeric' => 'Le chiffre d\'affaires doit être un nombre.',
            'chiffre_affaires_total.min'     => 'Le chiffre d\'affaires ne peut pas être négatif.',
        ];
    }

    /**
     * Message retourné si authorize() renvoie false.
     * L'industriel est redirigé avec ce message d'erreur.
     */
    protected function failedAuthorization(): never
    {
        abort(403, 'Soumission impossible : agrément expiré, suspendu ou absent.');
    }
}
