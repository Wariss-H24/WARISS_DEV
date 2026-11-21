<?php

namespace Database\Factories\Communication;

use App\Enums\CampaignStatus;
use App\Models\Communication\NewsletterCampaign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterCampaign>
 */
class NewsletterCampaignFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale' => 'fr',
            'subject' => fake()->sentence(5),
            'body' => fake()->paragraphs(3, true),
            'status' => CampaignStatus::Draft,
            'recipients_count' => 0,
        ];
    }
}
