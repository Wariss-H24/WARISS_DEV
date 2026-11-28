<?php

use App\Enums\ActivityEvent;
use App\Enums\MessageStatus;
use App\Enums\SubscriberStatus;
use App\Models\Admin\ActivityLog;
use App\Models\Communication\Message;
use App\Models\Communication\NewsletterSubscriber;
use App\Models\User;

/*
| Données sensibles (messages, abonnés, journal, comptes) : réservées au compte admin, même en lecture.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('hides every sensitive resource from the viewer, even for reading', function (string $uri) {
    Message::factory()->create();
    NewsletterSubscriber::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->getJson("/api/v1/admin/{$uri}")->assertForbidden();
})->with(['messages', 'newsletter-subscribers', 'activity-logs', 'accounts']);

it('lists messages with filters and a search, without the IP fingerprint', function () {
    Message::factory()->create(['name' => 'Alice Martin', 'ip_hash' => str_repeat('a', 64)]);
    Message::factory()->quote()->create(['name' => 'Bob']);

    $this->getJson('/api/v1/admin/messages?filter[search]=alice')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Alice Martin')
        ->assertJsonMissingPath('data.0.ip_hash');
    $this->getJson('/api/v1/admin/messages?filter[type]=quote')->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/admin/messages?filter[search]=%25')->assertJsonCount(0, 'data');
});

it('marks a new message as read when it is opened', function () {
    $message = Message::factory()->create();

    $this->getJson("/api/v1/admin/messages/{$message->id}")
        ->assertOk()
        ->assertJsonPath('data.status', MessageStatus::Read->value);
    expect($message->refresh()->read_at)->not->toBeNull();
});

it('changes only the status and the internal note of a message', function () {
    $message = Message::factory()->create(['body' => 'Bonjour']);

    $this->patchJson("/api/v1/admin/messages/{$message->id}", [
        'status' => 'replied',
        'internal_note' => 'Répondu par e-mail.',
        'body' => 'Contenu modifié',
    ])->assertOk()
        ->assertJsonPath('data.status', 'replied')
        ->assertJsonPath('data.internal_note', 'Répondu par e-mail.')
        ->assertJsonPath('data.body', 'Bonjour');

    expect($message->refresh()->replied_at)->not->toBeNull();
    $this->postJson('/api/v1/admin/messages', ['name' => 'x'])->assertMethodNotAllowed();
});

it('moves a message to the trash and restores it', function () {
    $message = Message::factory()->create();

    $this->deleteJson("/api/v1/admin/messages/{$message->id}")->assertNoContent();
    $this->getJson('/api/v1/admin/messages')->assertJsonCount(0, 'data');
    $this->getJson('/api/v1/admin/messages?filter[trashed]=only')->assertJsonCount(1, 'data');

    $this->postJson("/api/v1/admin/messages/{$message->id}/restore")->assertOk();
    expect($message->refresh()->trashed())->toBeFalse();
});

it('unsubscribes or erases a newsletter subscriber, but never creates one', function () {
    $subscriber = NewsletterSubscriber::factory()->subscribed()->create();

    $this->getJson('/api/v1/admin/newsletter-subscribers?filter[status]=subscribed')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonMissingPath('data.0.confirmation_token_hash');

    $this->postJson("/api/v1/admin/newsletter-subscribers/{$subscriber->id}/unsubscribe")
        ->assertOk()
        ->assertJsonPath('data.status', SubscriberStatus::Unsubscribed->value);

    $this->postJson('/api/v1/admin/newsletter-subscribers', ['email' => 'x@example.test'])->assertMethodNotAllowed();
    $this->deleteJson("/api/v1/admin/newsletter-subscribers/{$subscriber->id}")->assertNoContent();
    expect(NewsletterSubscriber::count())->toBe(0);
});

it('shows the activity log with filters, read-only', function () {
    ActivityLog::factory()->create(['user_id' => $this->admin->id, 'event' => ActivityEvent::Login]);
    ActivityLog::factory()->create(['event' => ActivityEvent::Deleted]);

    $this->getJson('/api/v1/admin/activity-logs?filter[event]=login')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user.name', $this->admin->name)
        ->assertJsonMissingPath('data.0.user.email');

    $log = ActivityLog::query()->first();
    $this->deleteJson("/api/v1/admin/activity-logs/{$log->id}")->assertMethodNotAllowed();
});

it('lists the accounts without any secret', function () {
    User::factory()->create();

    $this->getJson('/api/v1/admin/accounts')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonMissingPath('data.0.password')
        ->assertJsonMissingPath('data.0.remember_token');
    $this->postJson('/api/v1/admin/accounts', ['email' => 'x@example.test'])->assertMethodNotAllowed();
});
