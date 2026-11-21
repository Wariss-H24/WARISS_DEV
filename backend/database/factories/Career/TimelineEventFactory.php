<?php

namespace Database\Factories\Career;

use App\Enums\TimelineEventType;
use App\Models\Career\TimelineEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Par défaut : un événement manuel d'apprentissage, avec son titre traduit.
 *
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => TimelineEventType::Learning,
            'event_date' => '2024-03-01',
            'is_visible' => true,
            'position' => 0,
            'fr' => ['title' => 'Apprentissage de '.fake()->word()],
            'en' => ['title' => 'Learning '.fake()->word()],
        ];
    }

    /**
     * Événement lié à une donnée source : son type est l'alias de la source.
     */
    public function forSource(Model $source): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => $source->getMorphClass(),
            'eventable_type' => $source->getMorphClass(),
            'eventable_id' => $source->getKey(),
            'fr' => ['title' => null],
            'en' => ['title' => null],
        ]);
    }
}
