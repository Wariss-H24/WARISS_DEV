<?php

namespace App\Http\Requests\Admin\Resume;

use App\Http\Requests\Admin\AdminFormRequest;
use App\Rules\AllowedUpload;

/**
 * Version d'un CV (D15) : un fichier PDF, contrôlé sur son contenu réel.
 * Le fichier d'une version ne change jamais : on envoie une nouvelle version.
 */
class ResumeVersionRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'file' => $this->isCreating() ? ['required', 'file', new AllowedUpload(['pdf'])] : ['prohibited'],
            'change_note' => ['nullable', 'string', 'max:2000'],
            'make_current' => $this->isCreating() ? ['sometimes', 'boolean'] : ['prohibited'],
        ];
    }
}
