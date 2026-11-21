<?php

namespace Database\Factories\Identity;

use App\Models\Identity\Profile;
use App\Models\Identity\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $platform = fake()->randomElement(['github', 'linkedin', 'x', 'youtube']);

        return [
            'profile_id' => Profile::factory(),
            'platform' => $platform,
            'url' => "https://{$platform}.com/".fake()->userName(),
            'is_visible' => true,
            'position' => 0,
        ];
    }

    /**
     * Lien masqué sur le site public.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_visible' => false]);
    }
}
