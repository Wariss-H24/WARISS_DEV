<?php

namespace Database\Factories\Portfolio;

use App\Enums\PublicationStatus;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CaseStudy>
 */
class CaseStudyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->words(4, true));

        return [
            'project_id' => Project::factory(),
            'reading_time_minutes' => 6,
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['title' => $title, 'slug' => Str::slug($title), 'summary' => fake()->sentence()],
            'en' => ['title' => $title.' EN', 'slug' => Str::slug($title.' en'), 'summary' => fake()->sentence()],
        ];
    }

    /**
     * Étude publiée depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }
}
