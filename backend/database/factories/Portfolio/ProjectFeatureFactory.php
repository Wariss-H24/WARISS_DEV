<?php

namespace Database\Factories\Portfolio;

use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectFeature>
 */
class ProjectFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'is_visible' => true,
            'position' => 0,
            'fr' => ['title' => fake()->sentence(3), 'description' => fake()->sentence()],
            'en' => ['title' => fake()->sentence(3), 'description' => fake()->sentence()],
        ];
    }

    /**
     * Fonctionnalité masquée.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_visible' => false]);
    }
}
