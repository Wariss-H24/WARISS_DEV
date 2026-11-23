<?php

namespace App\Http\Requests\Admin\Communication;

use App\Http\Requests\Admin\AdminFormRequest;

/**
 * Rédaction d'une campagne. Le statut (programmée, envoyée) est géré par le serveur.
 */
class NewsletterCampaignRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'locale' => $this->localeRule(),
            'subject' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'preview_text' => ['nullable', 'string', 'max:255'],
            'body' => [...$this->requiredOnCreate(), 'string', 'max:200000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
