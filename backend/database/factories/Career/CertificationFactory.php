<?php

namespace Database\Factories\Career;

use App\Models\Career\Certification;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Certification>
 */
class CertificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'issuer_name' => fake()->company(),
            'issuer_url' => fake()->url(),
            'credential_id' => Str::upper(Str::random(10)),
            'verification_url' => fake()->url(),
            'issued_on' => '2025-03-15',
            'expires_on' => null,
            'is_visible' => true,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['title' => 'Certification '.fake()->word()],
            'en' => ['title' => fake()->word().' certification'],
        ];
    }
}
