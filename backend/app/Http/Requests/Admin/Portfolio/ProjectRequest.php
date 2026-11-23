<?php

namespace App\Http\Requests\Admin\Portfolio;

use App\Enums\ProjectKind;
use App\Enums\ProjectStage;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'project_category_id' => [...$this->requiredOnCreate(), 'integer', Rule::exists('project_categories', 'id')],
            'cover_media_id' => $this->mediaRule(),
            'kind' => [...$this->requiredOnCreate(), Rule::enum(ProjectKind::class)],
            'development_stage' => [...$this->requiredOnCreate(), Rule::enum(ProjectStage::class)],
            'client_name' => ['nullable', 'string', 'max:255'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'live_url' => $this->safeUrl(),
            'repository_url' => $this->safeUrl(),
            'demo_url' => $this->safeUrl(),
            'is_confidential' => ['sometimes', 'boolean'],
            ...$this->editorialRules(),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->idsRules('skill_ids', 'skills'),
            ...$this->childRules(
                'features',
                ['is_visible' => ['sometimes', 'boolean']],
                ['title' => ['string', 'max:255'], 'description' => ['string', 'max:5000']],
                ['title'],
            ),
            ...$this->sluggedTranslationRules('project', [
                'summary' => ['string', 'max:1000'],
                'description' => ['string', 'max:100000'],
                'context' => ['string', 'max:20000'],
                'role' => ['string', 'max:255'],
                'responsibilities' => ['string', 'max:20000'],
            ], ['summary', 'description', 'role']),
        ];
    }
}
