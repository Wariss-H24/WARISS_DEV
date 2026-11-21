<?php

namespace Database\Factories\Communication;

use App\Enums\SubscriberStatus;
use App\Models\Communication\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : une inscription en attente de confirmation.
 *
 * @extends Factory<NewsletterSubscriber>
 */
class NewsletterSubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'locale' => 'fr',
            'status' => SubscriberStatus::Pending,
            'consented_at' => now(),
            'consent_source' => 'footer_form',
        ];
    }

    /**
     * Abonnement confirmé.
     */
    public function subscribed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SubscriberStatus::Subscribed,
            'confirmed_at' => now(),
        ]);
    }
}
