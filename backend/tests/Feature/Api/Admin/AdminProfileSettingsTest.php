<?php

use App\Enums\SettingType;
use App\Models\Identity\Profile;
use App\Models\Media\Media;
use App\Models\Settings\SiteSetting;
use App\Models\User;

/*
| Profil (ressource unique, D1) et réglages du site (mise à jour par lot, D2).
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function profilePayload(): array
{
    return [
        'first_name' => 'Wariss',
        'last_name' => 'Dev',
        'display_name' => 'Wariss',
        'public_email' => 'contact@example.test',
        'country_code' => 'BJ',
        'spoken_languages' => [['code' => 'fr', 'level' => 'native'], ['code' => 'en', 'level' => 'professional']],
        'translations' => ['fr' => [
            'headline' => 'Développeur full-stack',
            'short_bio' => 'Je conçois des applications web.',
            'biography' => 'Biographie complète.',
        ]],
    ];
}

it('answers 404 while the profile has not been filled', function () {
    $this->getJson('/api/v1/admin/profile')->assertNotFound();
});

it('creates the single profile on the first save, then updates it', function () {
    $this->putJson('/api/v1/admin/profile', ['first_name' => 'Wariss'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['last_name', 'public_email', 'translations.fr.headline']);

    $this->putJson('/api/v1/admin/profile', profilePayload())
        ->assertCreated()
        ->assertJsonPath('data.user_id', $this->admin->id)
        ->assertJsonPath('data.translations.fr.headline', 'Développeur full-stack')
        ->assertJsonPath('data.spoken_languages.1.level', 'professional');

    $this->patchJson('/api/v1/admin/profile', ['availability_status' => 'limited', 'translations' => ['en' => [
        'headline' => 'Full-stack developer', 'short_bio' => 'I build web apps.', 'biography' => 'Full biography.',
    ]]])->assertOk()
        ->assertJsonPath('data.availability_status', 'limited')
        ->assertJsonPath('data.translations.en.headline', 'Full-stack developer')
        ->assertJsonPath('data.first_name', 'Wariss');

    expect(Profile::count())->toBe(1);
    $this->getJson('/api/v1/admin/profile')->assertOk()->assertJsonPath('data.display_name', 'Wariss');
});

it('validates the profile fields', function (array $changes, string $field) {
    Profile::factory()->create();

    $this->patchJson('/api/v1/admin/profile', $changes)->assertJsonValidationErrors([$field]);
})->with([
    'email' => [['public_email' => 'pas-un-email'], 'public_email'],
    'country' => [['country_code' => 'bj'], 'country_code'],
    'phone' => [['public_phone' => '<script>'], 'public_phone'],
    'language level' => [['spoken_languages' => [['code' => 'fr', 'level' => 'expert']]], 'spoken_languages.0.level'],
    'duplicated language' => [['spoken_languages' => [['code' => 'fr', 'level' => 'native'], ['code' => 'fr', 'level' => 'basic']]], 'spoken_languages.1.code'],
    'unknown media' => [['avatar_media_id' => 999999], 'avatar_media_id'],
]);

it('lets the viewer read the profile but not change it', function () {
    Profile::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->getJson('/api/v1/admin/profile')->assertOk();
    $this->patchJson('/api/v1/admin/profile', ['display_name' => 'Pirate'])->assertForbidden();
});

it('lists every setting to the admin and only the public ones to the viewer', function () {
    SiteSetting::factory()->exposedToPublic()->create(['key' => 'site_name']);
    SiteSetting::factory()->create(['group' => 'internal', 'key' => 'secret_note']);

    $this->getJson('/api/v1/admin/settings')->assertOk()->assertJsonCount(2, 'data');
    $this->getJson('/api/v1/admin/settings?group=internal')->assertJsonCount(1, 'data')->assertJsonPath('data.0.key', 'secret_note');

    $this->actingAs(User::factory()->create());
    $this->getJson('/api/v1/admin/settings')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.key', 'site_name');
});

it('updates several settings at once with values of the right type', function () {
    $name = SiteSetting::factory()->create(['group' => 'general', 'key' => 'site_name']);
    $tagline = SiteSetting::factory()->translatable()->create(['group' => 'general', 'key' => 'tagline']);
    $maintenance = SiteSetting::factory()->create(['group' => 'maintenance', 'key' => 'enabled', 'type' => SettingType::Bool, 'value' => false]);
    $logo = SiteSetting::factory()->create(['group' => 'branding', 'key' => 'logo', 'type' => SettingType::Media, 'value' => null]);
    $media = Media::factory()->create();

    $this->patchJson('/api/v1/admin/settings', ['settings' => [
        ['group' => 'general', 'key' => 'site_name', 'value' => 'WARISS.DEV'],
        ['group' => 'general', 'key' => 'tagline', 'translations' => ['fr' => 'Code et design', 'en' => 'Code and design']],
        ['group' => 'maintenance', 'key' => 'enabled', 'value' => true],
        ['group' => 'branding', 'key' => 'logo', 'value' => $media->id],
    ]])->assertOk()->assertJsonCount(4, 'data');

    expect($name->refresh()->value)->toBe('WARISS.DEV')
        ->and($tagline->refresh()->resolvedValue('en'))->toBe('Code and design')
        ->and($maintenance->refresh()->value)->toBeTrue()
        ->and($logo->refresh()->value)->toBe($media->id);
});

it('refuses unknown settings and values of the wrong type, without saving anything', function (array $entry, string $field) {
    $name = SiteSetting::factory()->create(['group' => 'general', 'key' => 'site_name', 'value' => 'Avant']);
    SiteSetting::factory()->translatable()->create(['group' => 'general', 'key' => 'tagline']);
    SiteSetting::factory()->create(['group' => 'maintenance', 'key' => 'enabled', 'type' => SettingType::Bool, 'value' => false]);
    SiteSetting::factory()->create(['group' => 'contact', 'key' => 'public_email']);

    $this->patchJson('/api/v1/admin/settings', ['settings' => [
        ['group' => 'general', 'key' => 'site_name', 'value' => 'Après'],
        $entry,
    ]])->assertJsonValidationErrors([$field]);

    expect($name->refresh()->value)->toBe('Avant');
})->with([
    'unknown' => [['group' => 'general', 'key' => 'inconnu', 'value' => 'x'], 'settings.1.key'],
    'bool as text' => [['group' => 'maintenance', 'key' => 'enabled', 'value' => 'peut-être'], 'settings.1.value'],
    'single value for a translated setting' => [['group' => 'general', 'key' => 'tagline', 'value' => 'x'], 'settings.1.value'],
    'translations for a plain setting' => [['group' => 'general', 'key' => 'site_name', 'translations' => ['fr' => 'x']], 'settings.1.translations'],
    'invalid email' => [['group' => 'contact', 'key' => 'public_email', 'value' => 'pas-un-email'], 'settings.1.value'],
]);

it('never lets the viewer change a setting', function () {
    SiteSetting::factory()->create(['group' => 'general', 'key' => 'site_name']);
    $this->actingAs(User::factory()->create());

    $this->patchJson('/api/v1/admin/settings', ['settings' => [['group' => 'general', 'key' => 'site_name', 'value' => 'x']]])
        ->assertForbidden();
});
