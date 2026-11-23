<?php

namespace App\Http\Requests\Admin\Career;

use App\Http\Requests\Admin\AdminFormRequest;

class CertificationRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'issuer_name' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'issuer_url' => $this->safeUrl(),
            'credential_id' => ['nullable', 'string', 'max:255'],
            'verification_url' => $this->safeUrl(),
            'issued_on' => [...$this->requiredOnCreate(), 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'certificate_media_id' => $this->mediaRule(),
            ...$this->displayRules(),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->translationRules(['title' => ['string', 'max:255'], 'description' => ['string', 'max:5000']], ['title']),
        ];
    }
}
