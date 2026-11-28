<?php

namespace App\Http\Requests\Public;

/**
 * Inscription à la newsletter (D13), confirmée ensuite par un lien envoyé par e-mail (double opt-in).
 */
class NewsletterSubscribeRequest extends PublicFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            'consent' => ['accepted'],
            'source' => ['nullable', 'string', 'max:50', 'regex:/^[a-z0-9_-]+$/'],
            ...$this->protectionRules(),
        ];
    }
}
