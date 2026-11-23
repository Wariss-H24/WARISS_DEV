<?php

use App\Enums\ActivityEvent;
use App\Models\Admin\ActivityLog;
use App\Models\User;

/*
| L'administration est une SPA : Sanctum n'ouvre une session par cookie que pour les requêtes
| venant d'un domaine déclaré (SANCTUM_STATEFUL_DOMAINS), d'où l'en-tête Referer.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
});

function adminAccount(array $attributes = []): User
{
    return User::factory()->admin()->create(['email' => 'admin@wariss.test', 'password' => 'Wariss-Dev-2026!'] + $attributes);
}

it('opens a session for an active account, whatever the email case, and logs it', function () {
    $admin = adminAccount();

    $response = $this->postJson('/api/v1/auth/session', ['email' => 'ADMIN@wariss.test', 'password' => 'Wariss-Dev-2026!']);

    $response->assertOk()
        ->assertJsonPath('data.email', 'admin@wariss.test')
        ->assertJsonPath('data.role', 'admin')
        ->assertJsonPath('data.abilities.can_write', true)
        ->assertJsonMissingPath('data.password');
    $this->assertAuthenticatedAs($admin);
    expect($admin->refresh()->last_login_at)->not->toBeNull()
        ->and(ActivityLog::sole())
        ->event->toBe(ActivityEvent::Login)
        ->user_id->toBe($admin->id);
});

it('returns the same 422 error whatever the reason of the failure', function (array $credentials, array $accountAttributes) {
    adminAccount($accountAttributes);

    $response = $this->postJson('/api/v1/auth/session', $credentials);

    $response->assertUnprocessable()->assertJsonValidationErrors(['email' => __('auth.failed')]);
    $this->assertGuest();
    expect(ActivityLog::count())->toBe(0);
})->with([
    'mauvais mot de passe' => [['email' => 'admin@wariss.test', 'password' => 'mauvais'], []],
    'adresse inconnue' => [['email' => 'inconnu@wariss.test', 'password' => 'Wariss-Dev-2026!'], []],
    'compte désactivé' => [['email' => 'admin@wariss.test', 'password' => 'Wariss-Dev-2026!'], ['is_active' => false]],
]);

it('rejects incomplete credentials', function () {
    $this->postJson('/api/v1/auth/session', ['email' => 'pas-un-email'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('blocks an email after five failed attempts, even with the right password', function () {
    adminAccount();

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/session', ['email' => 'admin@wariss.test', 'password' => 'mauvais'])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/session', ['email' => 'admin@wariss.test', 'password' => 'Wariss-Dev-2026!'])
        ->assertTooManyRequests();
    $this->assertGuest();
});

it('returns the current account with its read-only abilities for the viewer', function () {
    $viewer = User::factory()->create();

    $this->actingAs($viewer)->getJson('/api/v1/auth/me')
        ->assertOk()
        ->assertJsonPath('data.role', 'viewer')
        ->assertJsonPath('data.abilities.can_write', false)
        ->assertJsonPath('data.abilities.can_view_sensitive_data', false);
});

it('returns 401 to a guest asking for the current account', function () {
    $this->getJson('/api/v1/auth/me')->assertUnauthorized();
});

it('closes the session, logs it, and then refuses the current account', function () {
    $admin = adminAccount();
    $this->postJson('/api/v1/auth/session', ['email' => 'admin@wariss.test', 'password' => 'Wariss-Dev-2026!'])->assertOk();

    $this->deleteJson('/api/v1/auth/session')->assertNoContent();

    $this->assertGuest('web');
    expect(ActivityLog::where('event', ActivityEvent::Logout)->where('user_id', $admin->id)->exists())->toBeTrue();
});

it('answers in the requested locale and falls back to French', function (array $headers, string $query, string $expected) {
    $this->actingAs(User::factory()->create())
        ->withHeaders($headers)->getJson('/api/v1/auth/me'.$query)
        ->assertOk()
        ->assertHeader('Content-Language', $expected);
})->with([
    'paramètre locale' => [[], '?locale=en', 'en'],
    'en-tête Accept-Language' => [['Accept-Language' => 'en-US,en;q=0.9'], '', 'en'],
    'langue non prise en charge' => [['Accept-Language' => 'de-DE'], '?locale=de', 'fr'],
]);
