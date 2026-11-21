<?php

namespace Database\Factories\Career;

use App\Enums\CompletionStatus;
use App\Enums\DegreeLevel;
use App\Models\Career\Education;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un diplôme obtenu.
 *
 * @extends Factory<Education>
 */
class EducationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'institution_name' => fake()->company(),
            'degree_level' => DegreeLevel::Bachelor,
            'completion_status' => CompletionStatus::Completed,
            'started_on' => '2019-09-01',
            'ended_on' => '2022-06-30',
            'is_visible' => true,
            'position' => 0,
            'fr' => ['degree_title' => 'Licence en informatique', 'field_of_study' => 'Informatique'],
            'en' => ['degree_title' => 'Bachelor of Computer Science', 'field_of_study' => 'Computer Science'],
        ];
    }

    /**
     * Formation en cours.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes): array => [
            'completion_status' => CompletionStatus::InProgress,
            'started_on' => '2025-09-01',
            'ended_on' => null,
        ]);
    }
}
