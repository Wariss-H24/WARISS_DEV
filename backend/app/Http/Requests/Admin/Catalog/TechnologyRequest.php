<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Enums\TechnologyProficiency;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class TechnologyRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'technology_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('technology_categories', 'id')],
            'name' => [...$this->requiredOnCreate(), 'string', 'max:100'],
            'slug' => [...$this->requiredOnCreate(), ...$this->slugFormat(), Rule::unique('technologies', 'slug')->ignore($this->routeId())],
            'icon_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')],
            'icon_key' => ['nullable', 'string', 'max:100'],
            'brand_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'official_url' => $this->safeUrl(),
            'proficiency' => ['nullable', Rule::enum(TechnologyProficiency::class)],
            'is_visible' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            ...$this->translationRules(['description' => ['string', 'max:2000']]),
        ];
    }
}
