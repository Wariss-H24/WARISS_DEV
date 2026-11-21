<?php

namespace Database\Factories\Career;

use App\Enums\AchievementType;
use App\Models\Career\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Achievement>
 */
class AchievementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => AchievementType::Hackathon,
            'achieved_on' => '2025-05-20',
            'organization_name' => fake()->company(),
            'is_visible' => true,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['title' => 'Réalisation '.fake()->word()],
            'en' => ['title' => fake()->word().' achievement'],
        ];
    }
}
