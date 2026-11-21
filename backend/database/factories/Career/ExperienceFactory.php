<?php

namespace Database\Factories\Career;

use App\Enums\EmploymentType;
use App\Models\Career\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : une expérience terminée.
 *
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_name' => fake()->company(),
            'organization_url' => fake()->url(),
            'employment_type' => EmploymentType::FullTime,
            'is_remote' => false,
            'country_code' => 'FR',
            'started_on' => '2023-01-01',
            'ended_on' => '2024-06-30',
            'is_current' => false,
            'is_visible' => true,
            'position' => 0,
            'fr' => ['job_title' => 'Développeur web', 'summary' => fake()->sentence(), 'description' => fake()->paragraph()],
            'en' => ['job_title' => 'Web developer', 'summary' => fake()->sentence(), 'description' => fake()->paragraph()],
        ];
    }

    /**
     * Expérience en cours : sans date de fin.
     */
    public function current(string $startedOn = '2025-01-01'): static
    {
        return $this->state(fn (array $attributes): array => [
            'started_on' => $startedOn,
            'ended_on' => null,
            'is_current' => true,
        ]);
    }
}
