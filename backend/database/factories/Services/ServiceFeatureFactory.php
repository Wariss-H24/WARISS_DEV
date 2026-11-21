<?php

namespace Database\Factories\Services;

use App\Enums\ServiceFeatureKind;
use App\Models\Services\Service;
use App\Models\Services\ServiceFeature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceFeature>
 */
class ServiceFeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'kind' => ServiceFeatureKind::Included,
            'is_visible' => true,
            'position' => 0,
            'fr' => ['label' => fake()->sentence(3)],
            'en' => ['label' => fake()->sentence(3)],
        ];
    }
}
