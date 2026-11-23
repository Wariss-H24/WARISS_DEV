<?php

namespace App\Http\Requests\Admin\Seo;

use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

/**
 * Redirection manuelle (D-16) : chemin interne vers chemin interne ou URL externe.
 */
class RedirectRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'from_path' => [...$this->requiredOnCreate(), 'string', 'max:2048', 'regex:#^/([^/]|$)#', Rule::unique('redirects', 'from_path')->ignore($this->routeId())],
            'to_path' => [...$this->requiredOnCreate(), 'string', 'max:2048', 'regex:#^(https?://|/([^/]|$))#i', 'different:from_path'],
            'status_code' => ['sometimes', 'integer', Rule::in([301, 302, 307, 308])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
