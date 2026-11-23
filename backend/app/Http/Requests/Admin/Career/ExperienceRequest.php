<?php

namespace App\Http\Requests\Admin\Career;

use App\Enums\EmploymentType;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Career\Experience;
use Illuminate\Validation\Rule;

/**
 * Une expérience en cours n'a pas de date de fin ; une expérience terminée en a une
 * (contrainte experiences_current_dates_check).
 */
class ExperienceRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'organization_name' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'organization_url' => $this->safeUrl(),
            'organization_logo_media_id' => $this->mediaRule(),
            'employment_type' => [...$this->requiredOnCreate(), Rule::enum(EmploymentType::class)],
            'is_remote' => ['sometimes', 'boolean'],
            'country_code' => $this->countryCodeRule(),
            'started_on' => [...$this->requiredOnCreate(), 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'ended_on' => [
                'nullable',
                'date',
                'after_or_equal:started_on',
                Rule::requiredIf(fn (): bool => $this->isCreating() && ! $this->boolean('is_current')),
                Rule::prohibitedIf(fn (): bool => filter_var($this->finalValue(Experience::class, 'is_current'), FILTER_VALIDATE_BOOLEAN)),
            ],
            ...$this->displayRules(featurable: false),
            ...$this->idsRules('technology_ids', 'technologies'),
            ...$this->translationRules([
                'job_title' => ['string', 'max:255'],
                'location_label' => ['string', 'max:150'],
                'summary' => ['string', 'max:1000'],
                'description' => ['string', 'max:50000'],
                'achievements' => ['string', 'max:20000'],
            ], ['job_title', 'summary', 'description']),
        ];
    }
}
