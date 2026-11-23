<?php

namespace App\Http\Requests\Admin\Career;

use App\Enums\AchievementType;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class AchievementRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => [...$this->requiredOnCreate(), Rule::enum(AchievementType::class)],
            'achieved_on' => [...$this->requiredOnCreate(), 'date'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'url' => $this->safeUrl(),
            'media_id' => $this->mediaRule(),
            ...$this->displayRules(),
            ...$this->translationRules(['title' => ['string', 'max:255'], 'description' => ['string', 'max:5000']], ['title']),
        ];
    }
}
