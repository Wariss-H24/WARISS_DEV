<?php

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Communication\Message;
use App\Models\Services\Service;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a quote request with its details and a normalized email', function () {
    $message = Message::factory()->quote()->create(['email' => '  Client@Example.COM ']);

    expect($message->refresh())
        ->type->toBe(MessageType::Quote)
        ->email->toBe('client@example.com')
        ->details->toEqual(['budget' => '2000-5000', 'deadline' => '2026-03', 'project_type' => 'e-commerce']);
});

it('stores a simple contact message without details as an empty JSON object', function () {
    $message = Message::factory()->create(['details' => []]);

    expect(DB::table('messages')->where('id', $message->id)->value('details'))->toBe('{}')
        ->and($message->refresh()->details)->toBe([]);
});

it('only lets the public form fill the visitor fields', function () {
    $message = new Message([
        'name' => 'Visiteur', 'email' => 'v@example.com', 'body' => 'Bonjour', 'locale' => 'fr',
        'status' => 'replied', 'internal_note' => 'piège', 'ip_hash' => str_repeat('a', 64), 'consent_given' => true,
    ]);

    expect($message->status)->toBeNull()
        ->and($message->internal_note)->toBeNull()
        ->and($message->ip_hash)->toBeNull()
        ->and($message->consent_given)->toBeNull();
});

it('marks a new message as read only once', function () {
    $message = Message::factory()->create();

    $message->markAsRead();
    $firstReadAt = $message->refresh()->read_at;
    $this->travel(1)->hour();
    $message->markAsRead();

    expect($message->refresh()->status)->toBe(MessageStatus::Read)
        ->and($message->read_at->equalTo($firstReadAt))->toBeTrue();
});

it('rejects invalid values at the database level', function (array $values, string $constraint) {
    $message = Message::factory()->create();

    expect(fn () => DB::table('messages')->where('id', $message->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'consentement sans date' => [['consent_given' => true, 'consented_at' => null], 'messages_consent_check'],
    'IP en clair' => [['ip_hash' => '192.168.1.10'], 'messages_ip_hash_check'],
    'e-mail invalide' => [['email' => 'pas-un-email'], 'messages_email_check'],
    'détails en liste' => [fn () => ['details' => DB::raw("'[1,2]'::jsonb")], 'messages_details_check'],
    'répondu sans date' => [['status' => 'replied', 'replied_at' => null], 'messages_replied_at_check'],
    'statut inconnu' => [['status' => 'deleted'], 'messages_status_check'],
    'type inconnu' => [['type' => 'job'], 'messages_type_check'],
]);

it('keeps the message and clears the service when the service is permanently deleted', function () {
    $service = Service::factory()->create();
    $message = Message::factory()->create(['service_id' => $service->id]);

    $service->forceDelete();

    expect($message->refresh()->service_id)->toBeNull();
});

it('permanently deletes messages older than the retention period, including trashed ones', function () {
    $this->travelTo('2024-01-01 12:00:00');
    $expired = Message::factory()->create();
    $expiredTrashed = Message::factory()->create();
    $expiredTrashed->delete();

    $this->travelTo('2025-12-15 12:00:00');
    $recent = Message::factory()->create();

    $this->travelTo('2026-02-01 12:00:00');
    $this->artisan('model:prune', ['--model' => [Message::class]])->assertSuccessful();

    expect(Message::withTrashed()->pluck('id')->all())->toBe([$recent->id]);
});
