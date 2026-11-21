<?php

use App\Enums\AvailabilityStatus;
use App\Models\Identity\Profile;
use App\Models\Identity\SocialLink;
use App\Models\Media\Media;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('attaches one public profile to an account, with translated texts', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create([
        'availability_status' => AvailabilityStatus::Limited,
        'fr' => ['headline' => 'Développeur Full-Stack', 'short_bio' => 'Présentation', 'biography' => 'Parcours'],
        'en' => ['headline' => 'Full-Stack Developer', 'short_bio' => 'Introduction', 'biography' => 'Journey'],
    ]);

    app()->setLocale('en');
    $profile = $user->profile()->with('translations')->sole();

    expect($profile->headline)->toBe('Full-Stack Developer')
        ->and($profile->availability_status)->toBe(AvailabilityStatus::Limited)
        ->and($profile->spoken_languages)->toBe([['code' => 'fr', 'level' => 'native'], ['code' => 'en', 'level' => 'professional']]);
});

it('rejects a second profile for the same account', function () {
    $user = User::factory()->create();
    Profile::factory()->for($user)->create();

    Profile::factory()->for($user)->create();
})->throws(QueryException::class, 'profiles_user_id_unique');

it('does not allow the linked account to be mass assigned', function () {
    $profile = new Profile(['user_id' => 999, 'display_name' => 'Intrus']);

    expect($profile->user_id)->toBeNull();
});

it('does not expose any authentication data in the serialized profile', function () {
    $profile = Profile::factory()->create();

    expect(array_keys($profile->toArray()))->not->toContain('password', 'role', 'remember_token', 'email');
});

it('deletes the profile, its texts and its social links when the account is deleted', function () {
    $link = SocialLink::factory()->create();

    $link->profile->user->delete();

    $this->assertDatabaseCount('profiles', 0);
    $this->assertDatabaseCount('profile_translations', 0);
    $this->assertDatabaseCount('social_links', 0);
});

it('keeps the profile and clears the picture when the avatar media is deleted', function () {
    $avatar = Media::factory()->create();
    $profile = Profile::factory()->create(['avatar_media_id' => $avatar->id]);

    $avatar->delete();

    expect($profile->refresh()->avatar_media_id)->toBeNull();
});

it('rejects invalid values at the database level', function (string $column, string $value, string $constraint) {
    $profile = Profile::factory()->create();

    expect(fn () => DB::table('profiles')->where('id', $profile->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'code pays en minuscules' => ['country_code', 'fr', 'profiles_country_code_check'],
    'disponibilité inconnue' => ['availability_status', 'busy', 'profiles_availability_status_check'],
    'langues sous forme d\'objet' => ['spoken_languages', '{"fr": "native"}', 'profiles_spoken_languages_check'],
]);

it('rejects a dangerous social link and accepts web and e-mail links', function () {
    SocialLink::factory()->create(['url' => 'https://github.com/wariss']);
    SocialLink::factory()->create(['url' => 'mailto:contact@example.com']);

    expect(fn () => SocialLink::factory()->create(['url' => 'javascript:alert(1)']))
        ->toThrow(QueryException::class, 'social_links_url_check');
});

it('lists only the visible social links, in their display order', function () {
    $profile = Profile::factory()->create();
    $second = SocialLink::factory()->for($profile)->create(['position' => 2]);
    $first = SocialLink::factory()->for($profile)->create(['position' => 1]);
    SocialLink::factory()->for($profile)->hidden()->create(['position' => 0]);

    expect($profile->socialLinks()->visible()->ordered()->pluck('id')->all())->toBe([$first->id, $second->id]);
});
