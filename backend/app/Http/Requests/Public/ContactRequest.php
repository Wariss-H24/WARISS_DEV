<?php

namespace App\Http\Requests\Public;

use App\Enums\MessageType;
use Illuminate\Validation\Rule;

/**
 * Formulaire de contact et de demande de devis (D13). Le consentement au traitement des données
 * est obligatoire ; le service visé, s'il est indiqué, doit être publié.
 */
class ContactRequest extends PublicFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::enum(MessageType::class)],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'service' => ['nullable', 'string', 'max:255'],
            'details' => ['sometimes', 'array:budget,deadline,project_type,company'],
            'details.*' => ['nullable', 'string', 'max:255'],
            'consent' => ['accepted'],
            ...$this->protectionRules(),
        ];
    }
}
