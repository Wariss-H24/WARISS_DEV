<?php

use App\Enums\EmploymentType;
use App\Models\Career\Experience;
use App\Models\Catalog\Technology;
use App\Models\Media\Media;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores a finished experience with its translated job title', function () {
    $experience = Experience::factory()->create(['employment_type' => EmploymentType::Freelance]);
    $experience = Experience::with('translations')->find($experience->id);

    app()->setLocale('en');

    expect($experience)
        ->employment_type->toBe(EmploymentType::Freelance)
        ->job_title->toBe('Web developer')
        ->ended_on->toDateString()->toBe('2024-06-30');
});

it('stores a current experience without end date', function () {
    $experience = Experience::factory()->current()->create();

    expect($experience->refresh())
        ->is_current->toBeTrue()
        ->ended_on->toBeNull();
});

it('rejects inconsistent dates for the current flag', function (array $values) {
    Experience::factory()->create($values);
})->with([
    'en cours avec date de fin' => [['is_current' => true, 'ended_on' => '2025-01-01']],
    'terminée sans date de fin' => [['is_current' => false, 'ended_on' => null]],
    'fin avant le début' => [['started_on' => '2024-01-01', 'ended_on' => '2023-01-01']],
])->throws(QueryException::class, 'experiences_current_dates_check');

it('rejects invalid values at the database level', function (array $values, string $constraint) {
    $experience = Experience::factory()->create();

    expect(fn () => DB::table('experiences')->where('id', $experience->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'type inconnu' => [['employment_type' => 'gig'], 'experiences_employment_type_check'],
    'code pays invalide' => [['country_code' => 'fr'], 'experiences_country_code_check'],
    'lien dangereux' => [['organization_url' => 'javascript:alert(1)'], 'experiences_organization_url_check'],
]);

it('lists visible experiences with the current one first, then the most recent', function () {
    $old = Experience::factory()->create(['started_on' => '2019-01-01', 'ended_on' => '2020-01-01']);
    $recent = Experience::factory()->create(['started_on' => '2022-01-01', 'ended_on' => '2023-01-01']);
    $current = Experience::factory()->current('2018-01-01')->create();
    Experience::factory()->create(['is_visible' => false]);

    expect(Experience::visible()->mostRecentFirst()->pluck('id')->all())->toBe([$current->id, $recent->id, $old->id]);
});

it('links the technologies really used and keeps the experience when its logo is deleted', function () {
    $logo = Media::factory()->create();
    $experience = Experience::factory()->create(['organization_logo_media_id' => $logo->id]);
    $technology = Technology::factory()->create();
    $experience->technologies()->attach($technology);

    $logo->delete();

    expect($experience->refresh()->organization_logo_media_id)->toBeNull()
        ->and($experience->technologies()->pluck('technologies.id')->all())->toBe([$technology->id]);
});
