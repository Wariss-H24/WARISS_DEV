<?php

namespace Database\Factories\Analytics;

use App\Enums\AnalyticsEventName;
use App\Models\Analytics\AnalyticsEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsEvent>
 */
class AnalyticsEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occurred_at' => now(),
            'name' => AnalyticsEventName::CvDownload,
            'path' => '/fr/resume',
            'session_hash' => hash('sha256', fake()->uuid()),
            'properties' => [],
        ];
    }

    /**
     * Partage d'un contenu sur un réseau (D-16).
     */
    public function share(string $network = 'linkedin'): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => AnalyticsEventName::Share,
            'path' => '/fr/projects/mon-projet',
            'properties' => ['network' => $network],
        ]);
    }
}
