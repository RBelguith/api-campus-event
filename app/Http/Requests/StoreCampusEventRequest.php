<?php

namespace App\Http\Requests;

use App\Models\CampusEvent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampusEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('create', CampusEvent::class) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:campus_events,slug'],
            'description' => ['required', 'string', 'min:20'],
            'venue_id' => ['required', 'integer', 'exists:venues,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'capacity' => ['required', 'integer', 'min:1'],
            'is_public' => ['nullable', 'boolean'],
            'topics' => ['nullable', 'array'],
            'topics.*' => ['integer', 'exists:topics,id'],
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
            'title.required' => 'Le champ titre est obligatoire.',
            'slug.required' => 'Le champ slug est obligatoire.',
            'slug.regex' => 'Le slug ne peut contenir que des lettres minuscules, des chiffres et des tirets.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'description.required' => 'Le champ description est obligatoire.',
            'description.min' => 'La description doit contenir au moins :min caractères.',
            'venue_id.required' => 'Le champ lieu est obligatoire.',
            'venue_id.exists' => 'Le lieu sélectionné est invalide.',
            'starts_at.required' => 'La date de début est obligatoire.',
            'starts_at.date' => 'La date de début doit être une date valide.',
            'ends_at.required' => 'La date de fin est obligatoire.',
            'ends_at.date' => 'La date de fin doit être une date valide.',
            'ends_at.after' => 'La date de fin doit être postérieure à la date de début.',
            'capacity.required' => 'La capacité est obligatoire.',
            'capacity.integer' => 'La capacité doit être un entier.',
            'capacity.min' => 'La capacité doit être au moins :min.',
            'is_public.boolean' => 'Le champ public doit être vrai ou faux.',
            'topics.array' => 'Les thèmes doivent être un tableau.',
            'topics.*.integer' => 'Chaque thème doit être identifié par un entier.',
            'topics.*.exists' => 'Un thème sélectionné est invalide.',
        ];
    }
}
