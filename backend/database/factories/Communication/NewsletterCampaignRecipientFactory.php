<?php

namespace Database\Factories\Communication;

use App\Enums\RecipientStatus;
use App\Models\Communication\NewsletterCampaign;
use App\Models\Communication\NewsletterCampaignRecipient;
use App\Models\Communication\NewsletterSubscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NewsletterCampaignRecipient>
 */
class NewsletterCampaignRecipientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'campaign_id' => NewsletterCampaign::factory(),
            'subscriber_id' => NewsletterSubscriber::factory()->subscribed(),
            'status' => RecipientStatus::Queued,
        ];
    }
}
