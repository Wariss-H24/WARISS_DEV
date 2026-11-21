<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

const STRONG_PASSWORD = 'Wariss-Dev-2026!';

it('sets the password of the account matching the email, whatever its case', function () {
    $user = User::factory()->create(['email' => 'admin@wariss.test']);

    $this->artisan('app:set-password', ['email' => 'ADMIN@wariss.test'])
        ->expectsQuestion('Nouveau mot de passe', STRONG_PASSWORD)
        ->expectsQuestion('Confirmez le mot de passe', STRONG_PASSWORD)
        ->assertSuccessful();

    expect(Hash::check(STRONG_PASSWORD, $user->refresh()->password))->toBeTrue();
});

it('closes the open sessions and renews the remember token of the account', function () {
    $user = User::factory()->create(['email' => 'admin@wariss.test']);
    $previousRememberToken = $user->remember_token;
    DB::table('sessions')->insert([
        'id' => 'session-ouverte',
        'user_id' => $user->id,
        'payload' => '',
        'last_activity' => now()->timestamp,
    ]);

    $this->artisan('app:set-password', ['email' => 'admin@wariss.test'])
        ->expectsQuestion('Nouveau mot de passe', STRONG_PASSWORD)
        ->expectsQuestion('Confirmez le mot de passe', STRONG_PASSWORD)
        ->assertSuccessful();

    $this->assertDatabaseMissing('sessions', ['user_id' => $user->id]);
    expect($user->refresh()->remember_token)->not->toBe($previousRememberToken);
});

it('keeps the current password when the confirmation or the strength check fails', function (string $password, string $confirmation) {
    $user = User::factory()->create(['email' => 'admin@wariss.test']);

    $this->artisan('app:set-password', ['email' => 'admin@wariss.test'])
        ->expectsQuestion('Nouveau mot de passe', $password)
        ->expectsQuestion('Confirmez le mot de passe', $confirmation)
        ->assertFailed();

    expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
})->with([
    'confirmation différente' => [STRONG_PASSWORD, 'Autre-Mot-2026!'],
    'trop court' => ['Court-1!', 'Court-1!'],
    'sans symbole' => ['WarissDev2026abc', 'WarissDev2026abc'],
    'sans majuscule' => ['wariss-dev-2026!', 'wariss-dev-2026!'],
    'vide' => ['', ''],
]);

it('fails without asking for a password when no account matches the email', function () {
    $this->artisan('app:set-password', ['email' => 'inconnu@wariss.test'])
        ->expectsOutputToContain('Aucun compte')
        ->assertFailed();
});
