<?php

namespace App\Http\Requests\Admin\Services;

use App\Http\Requests\Admin\AdminFormRequest;

class ServiceCategoryRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'icon' => ['nullable', 'string', 'max:100'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
            ...$this->sluggedTranslationRules('service_category', ['description' => ['string', 'max:2000']], labelField: 'name'),
        ];
    }
}
