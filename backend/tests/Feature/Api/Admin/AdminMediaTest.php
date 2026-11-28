<?php

use App\Enums\ActivityEvent;
use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Models\Admin\ActivityLog;
use App\Models\Communication\Testimonial;
use App\Models\Media\Media;
use App\Models\Media\Mediable;
use App\Models\Portfolio\Project;
use App\Models\Resume\ResumeVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/*
| Bibliothèque de médias (D14) : téléversement contrôlé, doublons, visibilité, usages, fichiers privés.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    Storage::fake('public');
    Storage::fake('local');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

/**
 * Fichier téléversé construit à partir d'un contenu brut (le type MIME est lu dans le contenu).
 */
function uploadFromBytes(string $bytes, string $name): UploadedFile
{
    $path = tempnam(sys_get_temp_dir(), 'media');
    file_put_contents($path, $bytes);

    return new UploadedFile($path, $name, null, null, true);
}

/**
 * JPEG valide contenant un bloc EXIF (APP1) avec une donnée sensible reconnaissable.
 */
function jpegWithExif(): string
{
    $image = imagecreatetruecolor(40, 30);
    ob_start();
    imagejpeg($image);
    $jpeg = (string) ob_get_clean();

    $exif = "Exif\0\0".'II*'."\0".pack('V', 8)."\0\0".pack('V', 0).'GPS-48.8584-2.2945-SECRET';

    return substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
}

function pdfBytes(): string
{
    return "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n";
}

it('uploads an image, re-encodes it without its EXIF data and logs it', function () {
    $response = $this->post('/api/v1/admin/media', [
        'file' => uploadFromBytes(jpegWithExif(), 'Vacances à Paris.JPG'),
        'visibility' => 'public',
        'translations' => ['fr' => ['alt_text' => 'Tour Eiffel']],
    ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.kind', 'image')
        ->assertJsonPath('data.visibility', 'public')
        ->assertJsonPath('data.disk', 'public')
        ->assertJsonPath('data.original_name', 'Vacances à Paris.JPG')
        ->assertJsonPath('data.width', 40)
        ->assertJsonPath('data.height', 30)
        ->assertJsonPath('data.translations.fr.alt_text', 'Tour Eiffel')
        ->assertJsonPath('data.uploaded_by', $this->admin->id);

    $media = Media::query()->sole();
    expect($media->path)->toMatch('#^media/\d{4}/\d{2}/[0-9a-f-]{36}\.jpg$#')
        ->and($response->json('data.url'))->toEndWith($media->path)
        ->and(Storage::disk('public')->get($media->path))->not->toContain('GPS-48.8584')
        ->and($media->checksum_sha256)->toBe(hash('sha256', jpegWithExif()))
        ->and(ActivityLog::query()->where('event', ActivityEvent::Created)->where('subject_type', 'media')->exists())->toBeTrue();
    Storage::disk('local')->assertMissing($media->path);
});

it('stores a private document on the private disk and serves it only to the administration', function () {
    $response = $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'diplome.pdf')], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.kind', 'document')
        ->assertJsonPath('data.visibility', 'private');

    $media = Media::query()->sole();
    Storage::disk('local')->assertExists($media->path);
    Storage::disk('public')->assertMissing($media->path);
    expect($response->json('data.url'))->toBe(route('admin.media.file', ['id' => $media->id]));

    $this->actingAs(User::factory()->create());
    $file = $this->get("/api/v1/admin/media/{$media->id}/file")->assertOk();
    expect($file->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($file->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($file->headers->get('Content-Disposition'))->toContain('diplome.pdf')
        ->and($file->streamedContent())->toBe(pdfBytes());

    auth()->guard('web')->logout();
    $this->getJson("/api/v1/admin/media/{$media->id}/file")->assertUnauthorized();
});

it('refuses files outside the whitelist or whose content does not match the extension', function (Closure $file) {
    $this->post('/api/v1/admin/media', ['file' => $file()], ['Accept' => 'application/json'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
    expect(Media::count())->toBe(0);
})->with([
    'svg' => fn () => uploadFromBytes('<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', 'logo.svg'),
    'php disguised as png' => fn () => uploadFromBytes('<?php echo "pwned";', 'photo.png'),
    'pdf disguised as jpg' => fn () => uploadFromBytes(pdfBytes(), 'photo.jpg'),
    'executable' => fn () => uploadFromBytes('MZ binary', 'setup.exe'),
]);

it('refuses an image above the size limit', function () {
    config()->set('wariss.uploads.types.png.max_kb', 1);

    $this->post('/api/v1/admin/media', ['file' => UploadedFile::fake()->image('grande.png', 600, 600)], ['Accept' => 'application/json'])
        ->assertJsonValidationErrors(['file']);
});

it('refuses an image with too many pixels', function () {
    config()->set('wariss.uploads.max_image_pixels', 100);

    $this->post('/api/v1/admin/media', ['file' => UploadedFile::fake()->image('photo.png', 20, 20)], ['Accept' => 'application/json'])
        ->assertJsonValidationErrors(['file']);
});

it('detects a duplicate upload unless it is explicitly allowed', function () {
    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv.pdf')], ['Accept' => 'application/json'])->assertCreated();
    $existing = Media::query()->sole();

    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv-copie.pdf')], ['Accept' => 'application/json'])
        ->assertConflict()
        ->assertJsonPath('existing_id', $existing->id);

    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv-copie.pdf'), 'allow_duplicate' => '1'], ['Accept' => 'application/json'])
        ->assertCreated();
    expect(Media::count())->toBe(2);
});

it('keeps the kind consistent with the file', function () {
    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv.pdf'), 'kind' => 'image'], ['Accept' => 'application/json'])
        ->assertJsonValidationErrors(['kind']);

    $this->post('/api/v1/admin/media', ['file' => UploadedFile::fake()->image('icone.png', 64, 64), 'kind' => 'icon'], ['Accept' => 'application/json'])
        ->assertCreated()
        ->assertJsonPath('data.kind', 'icon');
});

it('registers an external media without any file', function () {
    $this->postJson('/api/v1/admin/media', ['external_url' => 'javascript:alert(1)', 'kind' => 'video'])
        ->assertJsonValidationErrors(['external_url']);
    $this->postJson('/api/v1/admin/media', ['external_url' => 'https://www.youtube.com/watch?v=abc'])
        ->assertJsonValidationErrors(['kind']);

    $this->postJson('/api/v1/admin/media', [
        'external_url' => 'https://www.youtube.com/watch?v=abc',
        'kind' => 'video',
        'duration_seconds' => 95,
        'visibility' => 'public',
    ])->assertCreated()
        ->assertJsonPath('data.disk', null)
        ->assertJsonPath('data.url', 'https://www.youtube.com/watch?v=abc')
        ->assertJsonPath('data.uploaded_by', $this->admin->id);
});

it('moves the file when the visibility changes', function () {
    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv.pdf')], ['Accept' => 'application/json'])->assertCreated();
    $media = Media::query()->sole();

    $this->patchJson("/api/v1/admin/media/{$media->id}", [
        'visibility' => 'public',
        'credit' => 'Wariss',
        'translations' => ['en' => ['caption' => 'Resume']],
    ])->assertOk()
        ->assertJsonPath('data.disk', 'public')
        ->assertJsonPath('data.credit', 'Wariss')
        ->assertJsonPath('data.translations.en.caption', 'Resume');

    Storage::disk('public')->assertExists($media->path);
    Storage::disk('local')->assertMissing($media->path);
    expect($media->refresh()->visibility)->toBe(MediaVisibility::Public);
});

it('never replaces the file or the kind of a stored media through an update', function () {
    $media = Media::factory()->create();

    $this->patch("/api/v1/admin/media/{$media->id}", ['file' => UploadedFile::fake()->image('autre.jpg')], ['Accept' => 'application/json'])
        ->assertJsonValidationErrors(['file']);
    $this->patchJson("/api/v1/admin/media/{$media->id}", ['external_url' => 'https://example.test/video'])
        ->assertJsonValidationErrors(['external_url']);
    $this->patchJson("/api/v1/admin/media/{$media->id}", ['kind' => 'video'])
        ->assertJsonValidationErrors(['kind']);
    $this->patchJson("/api/v1/admin/media/{$media->id}", ['kind' => 'icon'])
        ->assertOk()
        ->assertJsonPath('data.kind', MediaKind::Icon->value);
});

it('lists media with filters', function () {
    Media::factory()->count(2)->create();
    Media::factory()->privateDocument()->create();

    $this->getJson('/api/v1/admin/media?filter[kind]=document')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.visibility', 'private');
    $this->getJson('/api/v1/admin/media?filter[visibility]=public&sort=-size_bytes')->assertJsonCount(2, 'data');
});

it('shows where a media is used', function () {
    $media = Media::factory()->create();
    $project = Project::factory()->create(['cover_media_id' => $media->id]);

    $this->getJson("/api/v1/admin/media/{$media->id}")
        ->assertOk()
        ->assertJsonPath('data.usages.0.table', 'projects')
        ->assertJsonPath('data.usages.0.column', 'cover_media_id')
        ->assertJsonPath('data.usages.0.ids', [$project->id])
        ->assertJsonPath('data.usages.0.blocking', false);
});

it('asks for a confirmation before deleting a media still in use, then empties its slots', function () {
    $this->post('/api/v1/admin/media', ['file' => UploadedFile::fake()->image('portrait.jpg', 30, 30), 'visibility' => 'public'], ['Accept' => 'application/json'])->assertCreated();
    $media = Media::query()->sole();
    $testimonial = Testimonial::factory()->create(['photo_media_id' => $media->id]);

    $this->deleteJson("/api/v1/admin/media/{$media->id}")
        ->assertConflict()
        ->assertJsonPath('requires_confirmation', true)
        ->assertJsonPath('usages.0.table', 'testimonials');
    expect($media->exists())->toBeTrue();

    $this->deleteJson("/api/v1/admin/media/{$media->id}?confirm=1")->assertNoContent();

    expect(Media::count())->toBe(0)
        ->and($testimonial->refresh()->photo_media_id)->toBeNull();
    Storage::disk('public')->assertMissing($media->path);
});

it('never deletes a media that belongs to a gallery or a resume version', function (Closure $attach) {
    $media = $attach();

    $this->deleteJson("/api/v1/admin/media/{$media->id}?confirm=1")
        ->assertConflict()
        ->assertJsonPath('usages.0.blocking', true);
    expect(Media::query()->whereKey($media->id)->exists())->toBeTrue();
})->with([
    'gallery' => fn () => tap(Media::factory()->create(), fn (Media $media) => Mediable::query()->create([
        'media_id' => $media->id,
        'mediable_type' => 'project',
        'mediable_id' => Project::factory()->create()->id,
        'role' => 'gallery',
    ])),
    'resume version' => fn () => ResumeVersion::factory()->create()->media,
]);

it('deletes an unused media and its file', function () {
    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'brouillon.pdf')], ['Accept' => 'application/json'])->assertCreated();
    $media = Media::query()->sole();

    $this->deleteJson("/api/v1/admin/media/{$media->id}")->assertNoContent();

    Storage::disk('local')->assertMissing($media->path);
    expect(ActivityLog::query()->where('event', ActivityEvent::Deleted)->where('subject_type', 'media')->exists())->toBeTrue();
});

it('lets the viewer browse the library but never change it', function () {
    $media = Media::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->getJson('/api/v1/admin/media')->assertOk()->assertJsonCount(1, 'data');
    $this->post('/api/v1/admin/media', ['file' => uploadFromBytes(pdfBytes(), 'cv.pdf')], ['Accept' => 'application/json'])->assertForbidden();
    $this->patchJson("/api/v1/admin/media/{$media->id}", ['credit' => 'x'])->assertForbidden();
    $this->deleteJson("/api/v1/admin/media/{$media->id}?confirm=1")->assertForbidden();
});
