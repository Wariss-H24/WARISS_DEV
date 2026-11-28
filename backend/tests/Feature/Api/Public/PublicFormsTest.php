<?php

use App\Enums\SubscriberStatus;
use App\Models\Analytics\AnalyticsEvent;
use App\Models\Communication\Message;
use App\Models\Communication\NewsletterSubscriber;
use App\Models\Services\Service;
use App\Models\User;
use App\Notifications\ConfirmNewsletterSubscription;
use App\Notifications\NewContactMessage;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

/*
| Formulaires publics : contact et newsletter (double opt-in), protégés par Turnstile,
| un champ piège et une limite de fréquence.
*/

beforeEach(function () {
    Notification::fake();
    $this->withHeader('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36');
});

function contactPayload(array $overrides = []): array
{
    return [
        'name' => 'Alice Martin',
        'email' => 'Alice@Example.test',
        'subject' => 'Projet de site',
        'body' => 'Bonjour, je souhaite échanger sur un projet.',
        'consent' => true,
        ...$overrides,
    ];
}

it('stores a contact message with an IP fingerprint and notifies the admin', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->create();
    $service = Service::factory()->published()->create();

    $this->postJson('/api/v1/public/contact?locale=en', contactPayload([
        'type' => 'quote',
        'service' => $service->translate('fr')->slug,
        'details' => ['budget' => '5000 €', 'deadline' => ''],
    ]))->assertAccepted()->assertJsonMissingPath('id');

    $message = Message::query()->sole();
    expect($message->email)->toBe('alice@example.test')
        ->and($message->locale)->toBe('en')
        ->and($message->service_id)->toBe($service->id)
        ->and($message->details)->toBe(['budget' => '5000 €'])
        ->and($message->consent_given)->toBeTrue()
        ->and($message->ip_hash)->toMatch('/^[0-9a-f]{64}$/')
        ->and(AnalyticsEvent::query()->where('name', 'contact_submit')->exists())->toBeTrue();

    Notification::assertSentTo($admin, NewContactMessage::class);
    Notification::assertCount(1);
});

it('requires the consent and valid fields', function () {
    $this->postJson('/api/v1/public/contact', contactPayload(['consent' => false, 'email' => 'pas-un-email', 'body' => 'Court']))
        ->assertJsonValidationErrors(['consent', 'email', 'body']);
    expect(Message::count())->toBe(0);
});

it('silently drops a message caught by the honeypot', function () {
    $this->postJson('/api/v1/public/contact', contactPayload(['website' => 'http://spam.test']))->assertAccepted();

    expect(Message::count())->toBe(0);
    Notification::assertNothingSent();
});

it('verifies the Turnstile token on the server when a secret is configured', function () {
    config()->set('services.turnstile.secret_key', 'secret-de-test');
    Http::fake(['challenges.cloudflare.com/*' => Http::sequence()->push(['success' => false])->push(['success' => true])]);

    $this->postJson('/api/v1/public/contact', contactPayload(['turnstile_token' => 'mauvais']))->assertJsonValidationErrors(['turnstile_token']);
    $this->postJson('/api/v1/public/contact', contactPayload(['turnstile_token' => 'bon']))->assertAccepted();

    expect(Message::count())->toBe(1);
    Http::assertSent(fn ($request): bool => $request['secret'] === 'secret-de-test' && $request['response'] === 'bon');
});

it('refuses public forms in production without a Turnstile secret', function () {
    app()->detectEnvironment(fn (): string => 'production');

    $this->postJson('/api/v1/public/contact', contactPayload())->assertJsonValidationErrors(['turnstile_token']);
});

it('limits the number of submissions per minute', function () {
    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/public/contact', contactPayload())->assertAccepted();
    }

    $this->postJson('/api/v1/public/contact', contactPayload())->assertTooManyRequests();
});

it('subscribes in double opt-in and confirms with the emailed token', function () {
    $this->postJson('/api/v1/public/newsletter/subscribe?locale=en', ['email' => 'Lecteur@Example.test', 'consent' => true])->assertAccepted();

    $subscriber = NewsletterSubscriber::query()->sole();
    expect($subscriber->status)->toBe(SubscriberStatus::Pending)
        ->and($subscriber->email)->toBe('lecteur@example.test')
        ->and($subscriber->locale)->toBe('en');

    $token = null;
    Notification::assertSentTo(new AnonymousNotifiable, ConfirmNewsletterSubscription::class, function (ConfirmNewsletterSubscription $notification, array $channels, AnonymousNotifiable $notifiable) use (&$token): bool {
        $url = $notification->toMail($notifiable)->actionUrl;
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $token = $query['token'] ?? null;

        return str_contains($url, '/en/newsletter/confirm?token=') && $notifiable->routes['mail'] === 'lecteur@example.test';
    });

    $this->postJson('/api/v1/public/newsletter/confirm', ['token' => str_repeat('a', 64)])->assertNotFound();
    $this->postJson('/api/v1/public/newsletter/confirm', ['token' => $token])->assertOk();
    expect($subscriber->refresh()->status)->toBe(SubscriberStatus::Subscribed);
    $this->postJson('/api/v1/public/newsletter/confirm', ['token' => $token])->assertNotFound();
});

it('answers the same way for an address already subscribed, without sending anything', function () {
    NewsletterSubscriber::factory()->subscribed()->create(['email' => 'deja@example.test']);

    $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'DEJA@example.test', 'consent' => true])->assertAccepted();

    expect(NewsletterSubscriber::count())->toBe(1);
    Notification::assertNothingSent();
});

it('expires the confirmation link after 7 days', function () {
    $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'lent@example.test', 'consent' => true]);
    $subscriber = NewsletterSubscriber::query()->sole();
    $token = 'jeton-connu-'.str_repeat('x', 52);
    $subscriber->forceFill(['confirmation_token_hash' => hash('sha256', $token)])->save();

    $this->travel(8)->days();
    $this->postJson('/api/v1/public/newsletter/confirm', ['token' => $token])->assertNotFound();
});

it('unsubscribes with the signed link only', function () {
    $subscriber = NewsletterSubscriber::factory()->subscribed()->create();

    $this->postJson('/api/v1/public/newsletter/unsubscribe', ['id' => $subscriber->id, 'token' => str_repeat('0', 64)])->assertNotFound();
    $this->postJson('/api/v1/public/newsletter/unsubscribe', ['id' => $subscriber->id, 'token' => $subscriber->unsubscribeToken()])->assertOk();

    expect($subscriber->refresh()->status)->toBe(SubscriberStatus::Unsubscribed)
        ->and($subscriber->unsubscribeUrl())->toContain("id={$subscriber->id}&token=");
});

it('lets a former subscriber subscribe again with a new confirmation', function () {
    $subscriber = NewsletterSubscriber::factory()->subscribed()->create(['email' => 'retour@example.test']);
    $subscriber->unsubscribe();

    $this->postJson('/api/v1/public/newsletter/subscribe', ['email' => 'retour@example.test', 'consent' => true])->assertAccepted();

    expect($subscriber->refresh()->status)->toBe(SubscriberStatus::Pending)
        ->and($subscriber->unsubscribed_at)->toBeNull();
    Notification::assertCount(1);
});

it('answers validation errors in French by default and in English on request', function () {
    $this->postJson('/api/v1/public/contact', contactPayload(['consent' => false]))
        ->assertJsonPath('errors.consent.0', 'Le champ consentement doit être accepté.');

    $this->postJson('/api/v1/public/contact?locale=en', contactPayload(['consent' => false]))
        ->assertJsonPath('errors.consent.0', 'The consent field must be accepted.');

    $this->postJson('/api/v1/public/newsletter/confirm?locale=en', ['token' => str_repeat('a', 64)])
        ->assertNotFound()
        ->assertJsonPath('message', 'This confirmation link is invalid or has expired.');
});
