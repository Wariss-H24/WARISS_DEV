<?php

use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('publishes a resume with its current version, downloadable by the public', function () {
    $resume = Resume::factory()->published()->create();
    Resume::factory()->create();

    expect(Resume::downloadable()->pluck('id')->all())->toBe([$resume->id])
        ->and($resume->refresh()->currentVersion->version_number)->toBe(1);
});

it('refuses a current version that belongs to another resume', function () {
    $resume = Resume::factory()->create();
    $otherResumeVersion = ResumeVersion::factory()->create();

    $resume->forceFill(['current_version_id' => $otherResumeVersion->id])->save();
})->throws(QueryException::class, 'resumes_current_version_foreign');

it('refuses to make public a resume that is not active or has no version', function (array $values) {
    Resume::factory()->create($values);
})->with([
    'brouillon public' => [['is_public' => true, 'status' => 'draft']],
    'actif sans version' => [['is_public' => true, 'status' => 'active']],
])->throws(QueryException::class, 'resumes_public_requires_version_check');

it('refuses a default resume that is not public', function () {
    Resume::factory()->create(['is_default' => true]);
})->throws(QueryException::class, 'resumes_default_requires_public_check');

it('allows only one public default resume per locale', function () {
    Resume::factory()->published(isDefault: true)->create();
    Resume::factory()->english()->published(isDefault: true)->create();

    expect(fn () => Resume::factory()->published(isDefault: true)->create())
        ->toThrow(QueryException::class, 'resumes_one_public_default_per_locale');
});

it('numbers versions once per resume and lists them from the most recent', function () {
    $resume = Resume::factory()->create();
    ResumeVersion::factory()->for($resume)->create(['version_number' => 1]);
    ResumeVersion::factory()->for($resume)->create(['version_number' => 2]);

    expect($resume->versions()->pluck('version_number')->all())->toBe([2, 1])
        ->and(fn () => ResumeVersion::factory()->for($resume)->create(['version_number' => 2]))
        ->toThrow(QueryException::class, 'resume_versions_resume_id_version_number_unique');
});

it('refuses to delete the current version of a public resume', function () {
    $resume = Resume::factory()->published()->create();

    $resume->refresh()->currentVersion->delete();
})->throws(QueryException::class, 'resumes_public_requires_version_check');

it('clears the current version of a private resume when that version is deleted', function () {
    $resume = Resume::factory()->create();
    $version = ResumeVersion::factory()->for($resume)->create();
    $resume->forceFill(['current_version_id' => $version->id])->save();

    $version->delete();

    expect($resume->refresh()->current_version_id)->toBeNull();
});

it('refuses to delete the PDF file of an existing version', function () {
    $version = ResumeVersion::factory()->create();

    $version->media->delete();
})->throws(QueryException::class, 'resume_versions_media_id_foreign');

it('deletes the versions with their resume', function () {
    $resume = Resume::factory()->published()->create();

    $resume->delete();

    $this->assertDatabaseCount('resume_versions', 0);
});

it('rejects an unknown target or status at the database level', function (array $values, string $constraint) {
    $resume = Resume::factory()->create();

    expect(fn () => DB::table('resumes')->where('id', $resume->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'public inconnu' => [['target' => 'internship'], 'resumes_target_check'],
    'statut inconnu' => [['status' => 'published'], 'resumes_status_check'],
]);
