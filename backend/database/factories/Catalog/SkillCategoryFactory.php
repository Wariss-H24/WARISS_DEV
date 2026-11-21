<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SkillCategory>
 */
class SkillCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::ucfirst(fake()->unique()->words(2, true));

        return [
            'position' => 0,
            'is_active' => true,
            'fr' => ['name' => $name, 'slug' => Str::slug($name)],
            'en' => ['name' => $name.' EN', 'slug' => Str::slug($name.' en')],
        ];
    }

    /**
     * Catégorie masquée.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => false]);
    }
}
