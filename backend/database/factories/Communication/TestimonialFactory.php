<?php

namespace Database\Factories\Communication;

use App\Enums\TestimonialStatus;
use App\Models\Communication\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un témoignage reçu, en attente de validation, sans consentement enregistré.
 *
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_name' => fake()->name(),
            'author_company' => fake()->company(),
            'given_on' => '2025-10-01',
            'consent_given' => false,
            'status' => TestimonialStatus::Pending,
            'is_visible' => true,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['quote' => fake()->paragraph(), 'author_role' => 'Cheffe de projet'],
            'en' => ['quote' => fake()->paragraph(), 'author_role' => 'Project manager'],
        ];
    }

    /**
     * Témoignage approuvé, avec le consentement de son auteur.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'consent_given' => true,
            'status' => TestimonialStatus::Approved,
        ]);
    }
}
