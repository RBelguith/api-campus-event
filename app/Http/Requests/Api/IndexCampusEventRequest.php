<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexCampusEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'venue' => ['nullable', 'string', 'exists:venues,slug'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au moins :min.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser :max.',
            'venue.exists' => 'La valeur sélectionnée pour le champ lieu est invalide.',
        ];
    }
}
