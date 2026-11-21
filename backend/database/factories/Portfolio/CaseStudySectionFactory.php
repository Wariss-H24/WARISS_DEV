<?php

namespace Database\Factories\Portfolio;

use App\Enums\CaseStudySectionType;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\CaseStudySection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseStudySection>
 */
class CaseStudySectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'case_study_id' => CaseStudy::factory(),
            'type' => CaseStudySectionType::Context,
            'position' => 0,
            'fr' => ['body' => fake()->paragraph()],
            'en' => ['body' => fake()->paragraph()],
        ];
    }
}
