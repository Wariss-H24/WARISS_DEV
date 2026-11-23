<?php

namespace App\Http\Requests\Admin\Career;

use App\Enums\CompletionStatus;
use App\Enums\DegreeLevel;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class EducationRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'institution_name' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'institution_url' => $this->safeUrl(),
            'institution_logo_media_id' => $this->mediaRule(),
            'degree_level' => [...$this->requiredOnCreate(), Rule::enum(DegreeLevel::class)],
            'completion_status' => [...$this->requiredOnCreate(), Rule::enum(CompletionStatus::class)],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'country_code' => $this->countryCodeRule(),
            'proof_media_id' => $this->mediaRule(),
            ...$this->displayRules(featurable: false),
            ...$this->translationRules([
                'degree_title' => ['string', 'max:255'],
                'field_of_study' => ['string', 'max:255'],
                'location_label' => ['string', 'max:150'],
                'description' => ['string', 'max:20000'],
            ], ['degree_title']),
        ];
    }
}
