<?php

namespace Database\Factories\Lab;

use App\Enums\ExperimentKind;
use App\Enums\PublicationStatus;
use App\Models\Lab\Experiment;
use App\Models\Lab\ExperimentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : une démonstration intégrée (composant de la liste blanche), en brouillon.
 *
 * @extends Factory<Experiment>
 */
class ExperimentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->words(3, true));

        return [
            'experiment_category_id' => ExperimentCategory::factory(),
            'kind' => ExperimentKind::Demo,
            'demo_component_key' => 'color-palette-generator',
            'experimented_on' => '2025-09-01',
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['title' => $title, 'slug' => Str::slug($title), 'summary' => fake()->sentence(), 'description' => fake()->paragraph()],
            'en' => ['title' => $title.' EN', 'slug' => Str::slug($title.' en'), 'summary' => fake()->sentence(), 'description' => fake()->paragraph()],
        ];
    }

    /**
     * Expérimentation publiée depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }
}
