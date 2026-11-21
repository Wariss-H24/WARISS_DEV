<?php

use App\Enums\AchievementType;
use App\Models\Career\Achievement;
use App\Models\Career\Certification;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('tells whether a certification has expired', function (?string $expiresOn, bool $expired) {
    $this->travelTo('2026-06-15 10:00:00');

    $certification = Certification::factory()->create(['issued_on' => '2025-01-01', 'expires_on' => $expiresOn]);

    expect($certification->refresh()->isExpired())->toBe($expired);
})->with([
    'sans expiration' => [null, false],
    'expirée hier' => ['2026-06-14', true],
    'expire aujourd\'hui' => ['2026-06-15', false],
    'expire plus tard' => ['2027-01-01', false],
]);

it('rejects invalid certification values at the database level', function (array $values, string $constraint) {
    $certification = Certification::factory()->create();

    expect(fn () => DB::table('certifications')->where('id', $certification->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'expiration avant obtention' => [['issued_on' => '2025-06-01', 'expires_on' => '2025-01-01'], 'certifications_dates_check'],
    'lien de vérification dangereux' => [['verification_url' => 'javascript:alert(1)'], 'certifications_verification_url_check'],
    'lien émetteur dangereux' => [['issuer_url' => 'data:text/html,x'], 'certifications_issuer_url_check'],
]);

it('links a certification to technologies and keeps it when its certificate media is deleted', function () {
    $media = Media::factory()->privateDocument()->create();
    $certification = Certification::factory()->create(['certificate_media_id' => $media->id]);
    $certification->technologies()->attach(Technology::factory()->create());

    $media->delete();

    expect($certification->refresh()->certificate_media_id)->toBeNull()
        ->and($certification->technologies()->count())->toBe(1);
});

it('stores a translated achievement of a given type', function () {
    $achievement = Achievement::factory()->create([
        'type' => AchievementType::Talk,
        'fr' => ['title' => 'Conférence sur Laravel'],
        'en' => ['title' => 'Talk about Laravel'],
    ]);
    $achievement = Achievement::with('translations')->find($achievement->id);

    app()->setLocale('en');

    expect($achievement)
        ->type->toBe(AchievementType::Talk)
        ->title->toBe('Talk about Laravel');
});

it('rejects invalid achievement values at the database level', function (array $values, string $constraint) {
    $achievement = Achievement::factory()->create();

    expect(fn () => DB::table('achievements')->where('id', $achievement->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'type inconnu' => [['type' => 'medal'], 'achievements_type_check'],
    'lien dangereux' => [['url' => 'javascript:alert(1)'], 'achievements_url_check'],
]);

it('lists only visible certifications and achievements', function () {
    $certification = Certification::factory()->create();
    Certification::factory()->create(['is_visible' => false]);
    $achievement = Achievement::factory()->create();
    Achievement::factory()->create(['is_visible' => false]);

    expect(Certification::visible()->pluck('id')->all())->toBe([$certification->id])
        ->and(Achievement::visible()->pluck('id')->all())->toBe([$achievement->id]);
});
