<?php

namespace App\Http\Requests\Admin\Communication;

use App\Enums\TestimonialStatus;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Communication\Testimonial;
use Illuminate\Validation\Rule;

/**
 * Un témoignage ne peut être approuvé qu'avec le consentement de son auteur
 * (contrainte testimonials_approved_requires_consent_check).
 */
class TestimonialRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'author_name' => [...$this->requiredOnCreate(), 'string', 'max:255'],
            'author_company' => ['nullable', 'string', 'max:255'],
            'photo_media_id' => $this->mediaRule(),
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')],
            'given_on' => ['nullable', 'date', 'before_or_equal:today'],
            'status' => ['sometimes', Rule::enum(TestimonialStatus::class)],
            'consent_given' => [
                'boolean',
                Rule::requiredIf(fn (): bool => $this->finalValue(Testimonial::class, 'status') === TestimonialStatus::Approved->value
                    && ! filter_var($this->finalValue(Testimonial::class, 'consent_given'), FILTER_VALIDATE_BOOLEAN)),
                Rule::when(fn (): bool => $this->finalValue(Testimonial::class, 'status') === TestimonialStatus::Approved->value, ['accepted']),
            ],
            ...$this->displayRules(),
            ...$this->translationRules(['quote' => ['string', 'max:5000'], 'author_role' => ['string', 'max:255']], ['quote']),
        ];
    }
}
