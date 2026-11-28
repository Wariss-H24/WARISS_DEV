<?php

namespace App\Http\Requests\Admin\Communication;

use App\Enums\MessageStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

/**
 * Traitement d'un message reçu (D13) : seuls son statut et la note interne changent ;
 * le contenu écrit par le visiteur n'est jamais modifié.
 */
class MessageRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(MessageStatus::class)],
            'internal_note' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
