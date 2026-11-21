<?php

use App\Enums\CompletionStatus;
use App\Enums\DegreeLevel;
use App\Models\Career\Education;
use App\Models\Media\Media;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('presents only a completed degree as obtained', function (CompletionStatus $status, bool $obtained) {
    $education = Education::factory()->create(['completion_status' => $status]);

    expect($education->refresh()->isObtained())->toBe($obtained);
})->with([
    'terminée' => [CompletionStatus::Completed, true],
    'en cours' => [CompletionStatus::InProgress, false],
    'interrompue' => [CompletionStatus::Interrupted, false],
]);

it('stores the translated degree title', function () {
    $education = Education::factory()->create(['degree_level' => DegreeLevel::Master]);
    $education = Education::with('translations')->find($education->id);

    app()->setLocale('en');

    expect($education)
        ->degree_level->toBe(DegreeLevel::Master)
        ->degree_title->toBe('Bachelor of Computer Science');
});

it('rejects invalid values at the database level', function (array $values, string $constraint) {
    $education = Education::factory()->create();

    expect(fn () => DB::table('educations')->where('id', $education->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'niveau inconnu' => [['degree_level' => 'phd'], 'educations_degree_level_check'],
    'état inconnu' => [['completion_status' => 'failed'], 'educations_completion_status_check'],
    'fin avant le début' => [['started_on' => '2022-01-01', 'ended_on' => '2021-01-01'], 'educations_dates_check'],
    'lien dangereux' => [['institution_url' => 'data:text/html,x'], 'educations_institution_url_check'],
]);

it('lists formations in progress first, then the most recent', function () {
    $old = Education::factory()->create(['started_on' => '2010-09-01', 'ended_on' => '2013-06-30']);
    $recent = Education::factory()->create(['started_on' => '2019-09-01', 'ended_on' => '2022-06-30']);
    $inProgress = Education::factory()->inProgress()->create();

    expect(Education::mostRecentFirst()->pluck('id')->all())->toBe([$inProgress->id, $recent->id, $old->id]);
});

it('keeps the formation and clears its proof when the proof media is deleted', function () {
    $proof = Media::factory()->privateDocument()->create();
    $education = Education::factory()->create(['proof_media_id' => $proof->id]);

    $proof->delete();

    expect($education->refresh()->proof_media_id)->toBeNull();
});

it('deletes the texts of a deleted formation', function () {
    $education = Education::factory()->create();

    $education->delete();

    $this->assertDatabaseCount('education_translations', 0);
});
