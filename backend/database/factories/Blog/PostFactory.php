<?php

namespace Database\Factories\Blog;

use App\Enums\PostType;
use App\Enums\PublicationStatus;
use App\Models\Blog\Post;
use App\Models\Blog\PostCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : un article en brouillon, écrit par un administrateur.
 *
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::ucfirst(fake()->unique()->words(4, true));

        return [
            'author_id' => User::factory()->admin(),
            'post_category_id' => PostCategory::factory(),
            'type' => PostType::Article,
            'reading_time_minutes' => 5,
            'status' => PublicationStatus::Draft,
            'is_featured' => false,
            'position' => 0,
            'fr' => ['title' => $title, 'slug' => Str::slug($title), 'excerpt' => fake()->sentence(), 'content' => "## Introduction\n\n".fake()->paragraph()],
            'en' => ['title' => $title.' EN', 'slug' => Str::slug($title.' en'), 'excerpt' => fake()->sentence(), 'content' => "## Introduction\n\n".fake()->paragraph()],
        ];
    }

    /**
     * Article publié depuis hier.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Published,
            'published_at' => now()->subDay(),
        ]);
    }

    /**
     * Article programmé pour demain.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => PublicationStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }
}
