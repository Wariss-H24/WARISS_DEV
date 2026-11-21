<?php

namespace Database\Factories\Identity;

use App\Enums\AvailabilityStatus;
use App\Models\Identity\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'user_id' => User::factory(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => "{$firstName} {$lastName}",
            'public_email' => fake()->safeEmail(),
            'country_code' => fake()->countryCode(),
            'availability_status' => AvailabilityStatus::Available,
            'spoken_languages' => [['code' => 'fr', 'level' => 'native'], ['code' => 'en', 'level' => 'professional']],
            'fr' => [
                'headline' => 'Développeur Full-Stack',
                'short_bio' => fake()->sentence(),
                'biography' => fake()->paragraphs(2, true),
            ],
            'en' => [
                'headline' => 'Full-Stack Developer',
                'short_bio' => fake()->sentence(),
                'biography' => fake()->paragraphs(2, true),
            ],
        ];
    }
}
