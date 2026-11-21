<?php

namespace Database\Factories\Services;

use App\Enums\PricingModel;
use App\Enums\PublicationStatus;
use App\Models\Services\Service;
use App\Models\Services\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : un service en brouillon, sans tarif ni délai affichés.
 *
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->words(3, true));

        return [
            'service_category_id' => ServiceCategory::factory(),
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
            'position' => 0,
            'fr' => [
                'title' => $title,
                'slug' => Str::slug($title),
                'summary' => fake()->sentence(),
                'description' => fake()->paragraphs(2, true),
            ],
            'en' => [
                'title' => $title.' EN',
                'slug' => Str::slug($title.' en'),
                'summary' => fake()->sentence(),
                'description' => fake()->paragraphs(2, true),
            ],
        ];
    }

    /**
     * Service à prix fixe, avec délai de réalisation.
     */
    public function priced(string $price = '1500.00', string $currency = 'EUR'): static
    {
        return $this->state(fn (array $attributes): array => [
            'pricing_model' => PricingModel::Fixed,
            'price_from' => $price,
            'price_currency' => $currency,
            'delivery_min_days' => 10,
            'delivery_max_days' => 20,
        ]);
    }

    /**
     * Service publié depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }
}
