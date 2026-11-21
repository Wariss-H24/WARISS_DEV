<?php

namespace Database\Factories\Media;

use App\Models\Media\MediaCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaCollection>
 */
class MediaCollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'position' => 0,
        ];
    }
}
