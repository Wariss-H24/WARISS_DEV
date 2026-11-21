<?php

namespace Database\Factories\Pages;

use App\Enums\PageSectionType;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageSection>
 */
class PageSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => PageSectionType::Richtext,
            'position' => 0,
            'is_active' => true,
            'config' => [],
            'fr' => ['title' => fake()->sentence(3), 'body' => fake()->paragraph()],
            'en' => ['title' => fake()->sentence(3), 'body' => fake()->paragraph()],
        ];
    }

    /**
     * Section désactivée.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
