<?php

namespace Database\Factories\Settings;

use App\Enums\SettingType;
use App\Models\Settings\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un réglage texte non traduisible et non public.
 *
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => 'general',
            'key' => fake()->unique()->slug(2),
            'type' => SettingType::String,
            'value' => fake()->words(2, true),
            'is_translatable' => false,
            'is_public' => false,
        ];
    }

    /**
     * Réglage traduisible : la valeur vit dans les traductions.
     */
    public function translatable(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SettingType::Text,
            'value' => null,
            'is_translatable' => true,
        ]);
    }

    /**
     * Réglage exposé par l'API publique.
     */
    public function exposedToPublic(): static
    {
        return $this->state(fn (array $attributes): array => ['is_public' => true]);
    }
}
