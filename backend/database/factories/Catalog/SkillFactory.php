<?php

namespace Database\Factories\Catalog;

use App\Models\Catalog\Skill;
use App\Models\Catalog\SkillCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::ucfirst(fake()->unique()->words(3, true));

        return [
            'skill_category_id' => SkillCategory::factory(),
            'slug' => Str::slug($name),
            'proficiency' => fake()->numberBetween(1, 5),
            'years_of_experience' => fake()->randomFloat(1, 0, 10),
            'is_visible' => true,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['name' => $name, 'description' => fake()->sentence()],
            'en' => ['name' => $name.' (EN)', 'description' => fake()->sentence()],
        ];
    }

    /**
     * Compétence masquée sur le site public.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_visible' => false]);
    }
}
