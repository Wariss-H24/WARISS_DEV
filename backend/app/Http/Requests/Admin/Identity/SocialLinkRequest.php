<?php

namespace App\Http\Requests\Admin\Identity;

use App\Http\Requests\Admin\AdminFormRequest;

class SocialLinkRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'platform' => [...$this->requiredOnCreate(), 'string', 'max:50', 'regex:/^[a-z0-9]+(?:[-_][a-z0-9]+)*$/'],
            'url' => [...$this->requiredOnCreate(), 'string', 'max:2048', 'regex:#^(https?://|mailto:)#i'],
            ...$this->displayRules(featurable: false),
        ];
    }
}
