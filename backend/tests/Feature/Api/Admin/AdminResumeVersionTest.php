<?php

use App\Enums\MediaVisibility;
use App\Models\Media\Media;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;

/*
| Versions d'un CV (D15) : envoi d'un PDF privé, numérotation, version courante, suppression protégée.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    Storage::fake('public');
    Storage::fake('local');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function resumePdf(string $name = 'cv.pdf', string $marker = ''): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'cv');
    file_put_contents($path, "%PDF-1.4\n% {$marker}\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n");

    return new UploadedFile($path, $name, null, null, true);
}

function uploadVersion(int $resumeId, array $payload = []): TestResponse
{
    return test()->post("/api/v1/admin/resumes/{$resumeId}/versions", ['file' => resumePdf(), ...$payload], ['Accept' => 'application/json']);
}

it('stores each upload as a numbered private version that becomes the current one', function () {
    $resume = Resume::factory()->create();

    uploadVersion($resume->id, ['change_note' => 'Première version'])
        ->assertCreated()
        ->assertJsonPath('data.version_number', 1)
        ->assertJsonPath('data.change_note', 'Première version')
        ->assertJsonPath('data.created_by', $this->admin->id)
        ->assertJsonPath('data.media.visibility', 'private')
        ->assertJsonPath('data.media.kind', 'document');

    $second = uploadVersion($resume->id)->assertCreated()->assertJsonPath('data.version_number', 2);

    expect($resume->refresh()->current_version_id)->toBe($second->json('data.id'));
    $media = Media::query()->findOrFail($second->json('data.media_id'));
    expect($media->visibility)->toBe(MediaVisibility::Private);
    Storage::disk('local')->assertExists($media->path);
});

it('can keep the current version while uploading a draft', function () {
    $resume = Resume::factory()->published()->create();
    $current = $resume->current_version_id;

    uploadVersion($resume->id, ['make_current' => '0'])->assertCreated();

    expect($resume->refresh()->current_version_id)->toBe($current);
});

it('accepts only PDF files for a resume', function () {
    $resume = Resume::factory()->create();

    $this->post("/api/v1/admin/resumes/{$resume->id}/versions", ['file' => UploadedFile::fake()->image('cv.png')], ['Accept' => 'application/json'])
        ->assertJsonValidationErrors(['file']);
    $this->postJson("/api/v1/admin/resumes/{$resume->id}/versions", [])->assertJsonValidationErrors(['file']);
    expect(ResumeVersion::count())->toBe(0);
});

it('answers 404 for an unknown resume or a version of another resume', function () {
    $version = ResumeVersion::factory()->create();
    $other = Resume::factory()->create();

    uploadVersion(999999)->assertNotFound();
    $this->patchJson("/api/v1/admin/resumes/{$other->id}/versions/{$version->id}", ['change_note' => 'x'])->assertNotFound();
    $this->deleteJson("/api/v1/admin/resumes/{$other->id}/versions/{$version->id}")->assertNotFound();
    expect(Media::count())->toBe(1);
});

it('updates only the note of a version', function () {
    $version = ResumeVersion::factory()->create();

    $this->patchJson("/api/v1/admin/resumes/{$version->resume_id}/versions/{$version->id}", ['change_note' => 'Expériences 2026'])
        ->assertOk()
        ->assertJsonPath('data.change_note', 'Expériences 2026');
    $this->patchJson("/api/v1/admin/resumes/{$version->resume_id}/versions/{$version->id}", ['file' => 'x'])
        ->assertJsonValidationErrors(['file']);
});

it('never deletes the version downloaded by the public', function () {
    $resume = Resume::factory()->published()->create();

    $this->deleteJson("/api/v1/admin/resumes/{$resume->id}/versions/{$resume->current_version_id}")->assertConflict();
    expect(ResumeVersion::query()->whereKey($resume->current_version_id)->exists())->toBeTrue();
});

it('deletes a version with its file and clears it as current version of a private resume', function () {
    $resume = Resume::factory()->create();
    $versionId = uploadVersion($resume->id)->assertCreated()->json('data.id');
    $media = ResumeVersion::query()->findOrFail($versionId)->media;

    $this->deleteJson("/api/v1/admin/resumes/{$resume->id}/versions/{$versionId}")->assertNoContent();

    expect($resume->refresh()->current_version_id)->toBeNull()
        ->and(Media::query()->whereKey($media->id)->exists())->toBeFalse();
    Storage::disk('local')->assertMissing($media->path);
});

it('keeps the file of a deleted version when it is used elsewhere', function () {
    $version = ResumeVersion::factory()->create();
    ResumeVersion::factory()->create(['media_id' => $version->media_id]);

    $this->deleteJson("/api/v1/admin/resumes/{$version->resume_id}/versions/{$version->id}")->assertNoContent();

    expect(Media::query()->whereKey($version->media_id)->exists())->toBeTrue();
});

it('lets the viewer read resumes but never upload a version', function () {
    $resume = Resume::factory()->create();
    $this->actingAs(User::factory()->create());

    uploadVersion($resume->id)->assertForbidden();
    expect(Media::count())->toBe(0);
});
