<?php

namespace Database\Factories\Seo;

use App\Models\Pages\Page;
use App\Models\Seo\SeoMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * Par défaut : la fiche SEO d'une page, indexable.
 *
 * @extends Factory<SeoMetadata>
 */
class SeoMetadataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seoable_type' => 'page',
            'seoable_id' => Page::factory(),
            'robots_index' => true,
            'robots_follow' => true,
            'fr' => ['meta_title' => 'Titre SEO', 'meta_description' => fake()->sentence()],
            'en' => ['meta_title' => 'SEO title', 'meta_description' => fake()->sentence()],
        ];
    }

    /**
     * Fiche SEO d'un contenu donné.
     */
    public function forContent(Model $content): static
    {
        return $this->state(fn (array $attributes): array => [
            'seoable_type' => $content->getMorphClass(),
            'seoable_id' => $content->getKey(),
        ]);
    }
}
