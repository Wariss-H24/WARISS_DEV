<?php

namespace Database\Factories\Catalog;

use App\Enums\TechnologyProficiency;
use App\Models\Catalog\Technology;
use App\Models\Catalog\TechnologyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Technology>
 */
class TechnologyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::ucfirst(fake()->unique()->word()).' '.fake()->unique()->numberBetween(1, 99_999);

        return [
            'technology_category_id' => TechnologyCategory::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'brand_color' => fake()->hexColor(),
            'official_url' => fake()->url(),
            'proficiency' => fake()->randomElement(TechnologyProficiency::cases()),
            'is_visible' => true,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['description' => fake()->sentence()],
            'en' => ['description' => fake()->sentence()],
        ];
    }

    /**
     * Technologie masquée sur le site public.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_visible' => false]);
    }
}
