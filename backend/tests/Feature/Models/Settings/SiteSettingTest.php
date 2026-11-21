<?php

use App\Enums\SettingType;
use App\Models\Settings\SiteSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('returns the stored value of a non-translatable setting, with its JSON type preserved', function (SettingType $type, mixed $value) {
    $setting = SiteSetting::factory()->create(['type' => $type, 'value' => $value]);

    expect($setting->refresh()->resolvedValue())->toBe($value);
})->with([
    'texte' => [SettingType::String, 'WARISS.DEV'],
    'booléen' => [SettingType::Bool, false],
    'entier' => [SettingType::Int, 12],
    'média' => [SettingType::Media, 42],
    'json' => [SettingType::Json, ['github' => true]],
]);

it('returns the translation of the requested locale and falls back to French', function () {
    $setting = SiteSetting::factory()->translatable()->create();
    $setting->translations()->create(['locale' => 'fr', 'value' => 'Développeur Full-Stack']);

    expect($setting->load('translations')->resolvedValue('fr'))->toBe('Développeur Full-Stack')
        ->and($setting->resolvedValue('en'))->toBe('Développeur Full-Stack');

    $setting->translations()->create(['locale' => 'en', 'value' => 'Full-Stack Developer']);

    expect($setting->load('translations')->resolvedValue('en'))->toBe('Full-Stack Developer');
});

it('rejects a value whose JSON type does not match the setting type', function (string $type, string $jsonValue) {
    $setting = SiteSetting::factory()->create(['type' => SettingType::String, 'value' => 'ok']);

    DB::table('site_settings')->where('id', $setting->id)->update([
        'type' => $type,
        'value' => DB::raw("'{$jsonValue}'::jsonb"),
    ]);
})->with([
    'booléen contenant du texte' => ['bool', '"oui"'],
    'entier contenant du texte' => ['int', '"12"'],
    'texte contenant un nombre' => ['string', '12'],
    'json contenant un nombre' => ['json', '12'],
])->throws(QueryException::class, 'site_settings_value_type_check');

it('rejects a translatable setting that also stores a value', function () {
    $setting = SiteSetting::factory()->translatable()->create();

    DB::table('site_settings')->where('id', $setting->id)->update(['value' => DB::raw("'\"texte\"'::jsonb")]);
})->throws(QueryException::class, 'site_settings_translatable_check');

it('rejects a translatable setting that is not text', function () {
    SiteSetting::factory()->create(['type' => SettingType::Bool, 'value' => null, 'is_translatable' => true]);
})->throws(QueryException::class, 'site_settings_translatable_check');

it('rejects two settings with the same key in the same group', function () {
    SiteSetting::factory()->create(['group' => 'general', 'key' => 'site_name']);

    SiteSetting::factory()->create(['group' => 'general', 'key' => 'site_name']);
})->throws(QueryException::class, 'site_settings_group_key_unique');

it('exposes only public settings', function () {
    $public = SiteSetting::factory()->exposedToPublic()->create();
    SiteSetting::factory()->create();

    expect(SiteSetting::exposedToPublic()->pluck('id')->all())->toBe([$public->id]);
});

it('creates a private setting by default', function () {
    $setting = SiteSetting::forceCreate(['group' => 'general', 'key' => 'secret', 'type' => 'string']);

    expect($setting->refresh()->is_public)->toBeFalse();
});
