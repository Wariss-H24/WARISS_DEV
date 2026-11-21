<?php

namespace Database\Factories\Analytics;

use App\Enums\DeviceType;
use App\Enums\ReferrerType;
use App\Models\Analytics\PageView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageView>
 */
class PageViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'viewed_at' => now(),
            'path' => '/fr/projects',
            'locale' => 'fr',
            'session_hash' => hash('sha256', fake()->uuid()),
            'referrer_type' => ReferrerType::Direct,
            'device_type' => DeviceType::Desktop,
            'country_code' => 'FR',
        ];
    }

    /**
     * Visite amenée par un lien partagé sur un réseau (D-16).
     */
    public function fromShare(string $network = 'linkedin'): static
    {
        return $this->state(fn (array $attributes): array => [
            'referrer_type' => ReferrerType::Social,
            'utm_source' => $network,
            'utm_medium' => 'share',
        ]);
    }
}
