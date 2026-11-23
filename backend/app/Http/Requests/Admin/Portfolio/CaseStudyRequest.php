<?php

namespace App\Http\Requests\Admin\Portfolio;

use App\Enums\CaseStudySectionType;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class CaseStudyRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'cover_media_id' => $this->mediaRule(),
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'reading_time_minutes' => ['nullable', 'integer', 'between:1,600'],
            ...$this->editorialRules(),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->childRules(
                'sections',
                ['type' => ['required', Rule::enum(CaseStudySectionType::class)]],
                ['title' => ['string', 'max:255'], 'body' => ['string', 'max:100000']],
                ['body'],
            ),
            ...$this->sluggedTranslationRules('case_study', ['summary' => ['string', 'max:1000']], ['summary']),
        ];
    }
}
