<?php

namespace App\Http\Requests\Admin\Lab;

use App\Enums\ExperimentKind;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class ExperimentRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'experiment_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('experiment_categories', 'id')],
            'cover_media_id' => $this->mediaRule(),
            'kind' => [...$this->requiredOnCreate(), Rule::enum(ExperimentKind::class)],
            'demo_url' => $this->safeUrl(),
            'repository_url' => $this->safeUrl(),
            'video_url' => $this->safeUrl(),
            'demo_component_key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'experimented_on' => ['nullable', 'date'],
            ...$this->editorialRules(),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->sluggedTranslationRules('experiment', [
                'summary' => ['string', 'max:1000'],
                'description' => ['string', 'max:100000'],
                'objective' => ['string', 'max:5000'],
                'technical_notes' => ['string', 'max:50000'],
            ], ['summary', 'description']),
        ];
    }
}
