<?php

namespace Database\Factories\Communication;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Communication\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un nouveau message de contact, envoyé avec consentement.
 *
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MessageType::Contact,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'details' => [],
            'locale' => 'fr',
            'consent_given' => true,
            'consented_at' => now(),
            'status' => MessageStatus::New,
            'ip_hash' => hash('sha256', fake()->ipv4()),
        ];
    }

    /**
     * Demande de devis avec ses précisions (budget, délai).
     */
    public function quote(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => MessageType::Quote,
            'details' => ['budget' => '2000-5000', 'deadline' => '2026-03', 'project_type' => 'e-commerce'],
        ]);
    }
}
