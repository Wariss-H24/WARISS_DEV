<?php

namespace App\Http\Requests\Admin\Services;

use App\Enums\PricingModel;
use App\Enums\ServiceFeatureKind;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class ServiceRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'service_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('service_categories', 'id')],
            'cover_media_id' => $this->mediaRule(),
            'icon' => ['nullable', 'string', 'max:100'],
            'pricing_model' => ['nullable', Rule::enum(PricingModel::class)],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:9999999999', 'prohibited_if:pricing_model,quote'],
            'price_currency' => ['nullable', 'string', 'regex:/^[A-Z]{3}$/', 'required_with:price_from'],
            'delivery_min_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'delivery_max_days' => ['nullable', 'integer', 'min:0', 'max:3650', 'gte:delivery_min_days'],
            ...$this->editorialRules(),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->childRules(
                'features',
                [
                    'kind' => ['required', Rule::enum(ServiceFeatureKind::class)],
                    'icon' => ['nullable', 'string', 'max:100'],
                    'is_visible' => ['sometimes', 'boolean'],
                ],
                ['label' => ['string', 'max:255'], 'description' => ['string', 'max:2000']],
                ['label'],
            ),
            ...$this->sluggedTranslationRules('service', [
                'summary' => ['string', 'max:1000'],
                'description' => ['string', 'max:100000'],
                'target_audience' => ['string', 'max:2000'],
                'cta_label' => ['string', 'max:100'],
            ], ['summary', 'description']),
        ];
    }
}
