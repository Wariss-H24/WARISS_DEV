<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class SkillRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'skill_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('skill_categories', 'id')],
            'slug' => [...$this->requiredOnCreate(), ...$this->slugFormat(), Rule::unique('skills', 'slug')->ignore($this->routeId())],
            'proficiency' => ['nullable', 'integer', 'between:1,5'],
            'years_of_experience' => ['nullable', 'numeric', 'between:0,99.9'],
            'is_visible' => ['sometimes', 'boolean'],
            'is_featured' => ['sometimes', 'boolean'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->translationRules(['name' => ['string', 'max:255'], 'description' => ['string', 'max:2000']], ['name']),
        ];
    }
}
