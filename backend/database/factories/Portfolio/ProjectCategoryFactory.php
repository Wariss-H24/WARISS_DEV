<?php

namespace Database\Factories\Portfolio;

use App\Models\Portfolio\ProjectCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectCategory>
 */
class ProjectCategoryFactory extends Factory
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
}
