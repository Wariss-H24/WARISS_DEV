<?php

namespace Database\Factories\Analytics;

use App\Models\Analytics\DailyStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyStat>
 */
class DailyStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stat_date' => '2026-11-20',
            'metric' => 'page_views',
            'dimension_type' => '',
            'dimension_value' => '',
            'value' => fake()->numberBetween(0, 500),
        ];
    }
}
