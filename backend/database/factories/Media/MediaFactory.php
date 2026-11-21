<?php

namespace Database\Factories\Media;

use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Models\Media\Media;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : une image publique stockée sur le disque « public ».
 *
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $storedName = Str::uuid()->toString().'.jpg';

        return [
            'disk' => 'public',
            'path' => 'media/'.$storedName,
            'original_name' => fake()->word().'.jpg',
            'stored_name' => $storedName,
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => fake()->numberBetween(20_000, 2_000_000),
            'checksum_sha256' => hash('sha256', $storedName),
            'width' => 1600,
            'height' => 900,
            'kind' => MediaKind::Image,
            'visibility' => MediaVisibility::Public,
            'metadata' => [],
        ];
    }

    /**
     * Document privé (justificatif, ancienne version de CV…), servi par URL signée.
     */
    public function privateDocument(): static
    {
        return $this->state(function (array $attributes): array {
            $storedName = Str::uuid()->toString().'.pdf';

            return [
                'disk' => 'local',
                'path' => 'media/'.$storedName,
                'original_name' => 'justificatif.pdf',
                'stored_name' => $storedName,
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'checksum_sha256' => hash('sha256', $storedName),
                'width' => null,
                'height' => null,
                'kind' => MediaKind::Document,
                'visibility' => MediaVisibility::Private,
            ];
        });
    }

    /**
     * Vidéo hébergée ailleurs : aucun fichier stocké.
     */
    public function externalVideo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'disk' => null,
            'path' => null,
            'original_name' => null,
            'stored_name' => null,
            'mime_type' => null,
            'extension' => null,
            'size_bytes' => null,
            'checksum_sha256' => null,
            'external_url' => 'https://www.youtube.com/watch?v='.Str::random(11),
            'width' => null,
            'height' => null,
            'duration_seconds' => fake()->numberBetween(30, 1200),
            'kind' => MediaKind::Video,
        ]);
    }
}
