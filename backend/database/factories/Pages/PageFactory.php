<?php

namespace Database\Factories\Pages;

use App\Enums\PageKind;
use App\Enums\PublicationStatus;
use App\Models\Pages\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : une page personnalisée en brouillon, avec un slug par langue.
 *
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'key' => Str::slug($title),
            'kind' => PageKind::Custom,
            'template' => 'default',
            'status' => PublicationStatus::Draft,
            'published_at' => null,
            'fr' => ['title' => $title, 'slug' => Str::slug($title), 'summary' => fake()->sentence()],
            'en' => ['title' => $title.' (EN)', 'slug' => Str::slug($title.' en'), 'summary' => fake()->sentence()],
        ];
    }

    /**
     * Page publiée depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Page programmée pour demain.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }

    /**
     * Page système : route fixe, sans slug.
     */
    public function system(string $key = 'home'): static
    {
        return $this->state(fn (array $attributes): array => [
            'key' => $key,
            'kind' => PageKind::System,
            'template' => $key,
            'fr' => ['title' => Str::ucfirst($key), 'slug' => null],
            'en' => ['title' => Str::ucfirst($key), 'slug' => null],
        ]);
    }
}
