<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FormRequest — Validation de la création d'un agrément
 *
 * Centralise les règles de validation de AgrementController@store.
 */
class StoreAgrementRequest extends FormRequest
{
    /**
     * Seuls les agents MIC et administrateurs peuvent délivrer des agréments.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'unite_industrielle_id' => ['required', 'integer', 'exists:unites_industrielles,id'],
            'type_agrement'         => ['required', 'string', 'max:150'],
            'date_delivrance'       => ['required', 'date'],
            // La date d'expiration doit être postérieure à la délivrance
            'date_expiration'       => ['nullable', 'date', 'after:date_delivrance'],
            'observations'          => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'unite_industrielle_id.required' => 'L\'unité industrielle est obligatoire.',
            'unite_industrielle_id.exists'   => 'Unité industrielle introuvable.',
            'type_agrement.required'         => 'Le type d\'agrément est obligatoire.',
            'date_delivrance.required'       => 'La date de délivrance est obligatoire.',
            'date_delivrance.date'           => 'La date de délivrance n\'est pas valide.',
            'date_expiration.after'          => 'La date d\'expiration doit être postérieure à la date de délivrance.',
        ];
    }
}
