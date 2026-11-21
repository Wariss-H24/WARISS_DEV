<?php

use App\Enums\SubscriberStatus;
use App\Models\Communication\NewsletterCampaign;
use App\Models\Communication\NewsletterCampaignRecipient;
use App\Models\Communication\NewsletterSubscriber;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores only the hash of the confirmation token and confirms with the clear token', function () {
    $subscriber = NewsletterSubscriber::factory()->create();

    $token = $subscriber->issueConfirmationToken();

    expect(DB::table('newsletter_subscribers')->value('confirmation_token_hash'))
        ->toBe(hash('sha256', $token))
        ->not->toBe($token)
        ->and(NewsletterSubscriber::withConfirmationToken($token)->sole()->is($subscriber))->toBeTrue()
        ->and(NewsletterSubscriber::withConfirmationToken('mauvais-jeton')->exists())->toBeFalse();
});

it('confirms the subscription and invalidates the token', function () {
    $subscriber = NewsletterSubscriber::factory()->create();
    $token = $subscriber->issueConfirmationToken();

    $subscriber->confirm();

    expect($subscriber->refresh())
        ->status->toBe(SubscriberStatus::Subscribed)
        ->confirmed_at->not->toBeNull()
        ->and(NewsletterSubscriber::withConfirmationToken($token)->exists())->toBeFalse()
        ->and(NewsletterSubscriber::subscribed()->count())->toBe(1);
});

it('unsubscribes an address so it no longer receives campaigns', function () {
    $subscriber = NewsletterSubscriber::factory()->subscribed()->create();

    $subscriber->unsubscribe();

    expect($subscriber->refresh()->status)->toBe(SubscriberStatus::Unsubscribed)
        ->and(NewsletterSubscriber::subscribed()->count())->toBe(0);
});

it('rejects the same address twice, whatever its case', function () {
    NewsletterSubscriber::factory()->create(['email' => 'lecteur@example.com']);

    NewsletterSubscriber::factory()->create(['email' => 'LECTEUR@example.com']);
})->throws(QueryException::class, 'newsletter_subscribers_email_lower_unique');

it('never serializes the token hash nor the IP hash', function () {
    $subscriber = NewsletterSubscriber::factory()->create(['email' => 'a@example.com']);
    $subscriber->issueConfirmationToken();

    expect(array_keys($subscriber->toArray()))->not->toContain('confirmation_token_hash', 'ip_hash');
});

it('rejects inconsistent subscriber states at the database level', function (array $values, string $constraint) {
    $subscriber = NewsletterSubscriber::factory()->create();

    expect(fn () => DB::table('newsletter_subscribers')->where('id', $subscriber->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'abonné sans confirmation' => [['status' => 'subscribed', 'confirmed_at' => null], 'newsletter_subscribers_subscribed_check'],
    'désabonné sans date' => [['status' => 'unsubscribed', 'unsubscribed_at' => null], 'newsletter_subscribers_unsubscribed_check'],
    'jeton en clair' => [['confirmation_token_hash' => 'jeton-en-clair'], 'newsletter_subscribers_token_hash_check'],
]);

it('sends a campaign only once to each subscriber', function () {
    $recipient = NewsletterCampaignRecipient::factory()->create();

    NewsletterCampaignRecipient::factory()->create([
        'campaign_id' => $recipient->campaign_id,
        'subscriber_id' => $recipient->subscriber_id,
    ]);
})->throws(QueryException::class, 'newsletter_campaign_recipients_campaign_id_subscriber_id_unique');

it('erases the delivery history when a subscriber asks to be deleted', function () {
    $recipient = NewsletterCampaignRecipient::factory()->create();

    $recipient->subscriber->delete();

    $this->assertDatabaseCount('newsletter_campaign_recipients', 0);
    expect(NewsletterCampaign::count())->toBe(1);
});

it('rejects inconsistent campaign states at the database level', function (array $values, string $constraint) {
    $campaign = NewsletterCampaign::factory()->create();

    expect(fn () => DB::table('newsletter_campaigns')->where('id', $campaign->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'programmée sans date' => [['status' => 'scheduled', 'scheduled_at' => null], 'newsletter_campaigns_scheduled_check'],
    'envoyée sans date' => [['status' => 'sent', 'sent_at' => null], 'newsletter_campaigns_sent_check'],
    'nombre négatif' => [['recipients_count' => -1], 'newsletter_campaigns_recipients_count_check'],
]);
