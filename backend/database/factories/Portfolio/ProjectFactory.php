<?php

namespace Database\Factories\Portfolio;

use App\Enums\ProjectKind;
use App\Enums\ProjectStage;
use App\Enums\PublicationStatus;
use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : un projet personnel terminé, en brouillon.
 *
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
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
            'project_category_id' => ProjectCategory::factory(),
            'kind' => ProjectKind::Personal,
            'development_stage' => ProjectStage::Completed,
            'started_on' => '2025-01-15',
            'ended_on' => '2025-06-30',
            'repository_url' => 'https://github.com/example/'.Str::slug($title),
            'is_confidential' => false,
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
            'position' => 0,
            'fr' => [
                'title' => $title,
                'slug' => Str::slug($title),
                'summary' => fake()->sentence(),
                'description' => fake()->paragraphs(2, true),
                'role' => 'Développeur Full-Stack',
            ],
            'en' => [
                'title' => $title.' EN',
                'slug' => Str::slug($title.' en'),
                'summary' => fake()->sentence(),
                'description' => fake()->paragraphs(2, true),
                'role' => 'Full-Stack Developer',
            ],
        ];
    }

    /**
     * Projet publié depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Projet confidentiel : client et liens à ne jamais exposer.
     */
    public function confidential(): static
    {
        return $this->state(fn (array $attributes): array => [
            'kind' => ProjectKind::Professional,
            'client_name' => fake()->company(),
            'live_url' => 'https://client.example.com',
            'is_confidential' => true,
        ]);
    }
}
