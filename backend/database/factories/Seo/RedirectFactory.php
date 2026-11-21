<?php

namespace Database\Factories\Seo;

use App\Models\Seo\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slug = fake()->unique()->slug(3);

        return [
            'from_path' => "/fr/projects/ancien-{$slug}",
            'to_path' => "/fr/projects/{$slug}",
            'status_code' => 301,
            'is_active' => true,
        ];
    }
}
