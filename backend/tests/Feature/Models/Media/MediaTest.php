<?php

use App\Enums\MediaKind;
use App\Enums\MediaVisibility;
use App\Models\Media\Media;
use App\Models\Media\MediaCollection;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

it('stores a file media with its technical attributes cast to enums', function () {
    $media = Media::factory()->create();

    expect($media->refresh())
        ->kind->toBe(MediaKind::Image)
        ->visibility->toBe(MediaVisibility::Public)
        ->isExternal()->toBeFalse();
});

it('stores an external video without any stored file', function () {
    $media = Media::factory()->externalVideo()->create();

    expect($media->refresh())
        ->kind->toBe(MediaKind::Video)
        ->path->toBeNull()
        ->isExternal()->toBeTrue();
});

it('makes a media private when no visibility is given', function () {
    $media = Media::forceCreate(Arr::except(Media::factory()->raw(), 'visibility'));

    expect($media->refresh()->visibility)->toBe(MediaVisibility::Private);
});

it('rejects a media with neither a stored file nor an external URL', function () {
    Media::factory()->externalVideo()->create(['external_url' => null]);
})->throws(QueryException::class, 'media_source_check');

it('rejects a media that mixes a stored file and an external URL', function () {
    Media::factory()->create(['external_url' => 'https://www.youtube.com/watch?v=abc']);
})->throws(QueryException::class, 'media_source_check');

it('rejects a stored file whose checksum is missing', function () {
    Media::factory()->create(['checksum_sha256' => null]);
})->throws(QueryException::class, 'media_source_check');

it('rejects incomplete or invalid dimensions', function (?int $width, ?int $height) {
    Media::factory()->create(['width' => $width, 'height' => $height]);
})->with([
    'largeur sans hauteur' => [1600, null],
    'hauteur sans largeur' => [null, 900],
    'largeur nulle' => [0, 900],
])->throws(QueryException::class, 'media_dimensions_check');

it('rejects values outside the allowed lists at the database level', function (string $column, string $value, string $constraint) {
    $media = Media::factory()->create();

    expect(fn () => DB::table('media')->where('id', $media->id)->update([$column => $value]))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'disque inconnu' => ['disk', 'ftp', 'media_disk_check'],
    'nature inconnue' => ['kind', 'audio', 'media_kind_check'],
    'visibilité inconnue' => ['visibility', 'unlisted', 'media_visibility_check'],
]);

it('rejects an unknown kind or visibility before reaching the database', function (string $column, string $value) {
    Media::factory()->make([$column => $value]);
})->with([
    'nature inconnue' => ['kind', 'audio'],
    'visibilité inconnue' => ['visibility', 'unlisted'],
])->throws(ValueError::class);

it('rejects two media stored at the same path on the same disk', function () {
    Media::factory()->create(['disk' => 'public', 'path' => 'media/photo.jpg']);

    Media::factory()->create(['disk' => 'public', 'path' => 'media/photo.jpg']);
})->throws(QueryException::class, 'media_disk_path_unique');

it('returns the translation of the current locale and falls back to French when it is missing', function () {
    $media = Media::factory()->create();
    $media->fill([
        'fr' => ['alt_text' => 'Portrait de Wariss', 'caption' => 'Légende'],
        'en' => ['alt_text' => 'Portrait of Wariss'],
    ])->save();
    $media = Media::with('translations')->find($media->id);

    app()->setLocale('en');

    expect($media->alt_text)->toBe('Portrait of Wariss')
        ->and($media->caption)->toBe('Légende');
});

it('deletes the translations of a deleted media', function () {
    $media = Media::factory()->create();
    $media->fill(['fr' => ['alt_text' => 'Portrait']])->save();

    $media->delete();

    $this->assertDatabaseMissing('media_translations', ['media_id' => $media->id]);
});

it('keeps the media and clears its collection when the collection is deleted', function () {
    $collection = MediaCollection::factory()->create();
    $media = Media::factory()->for($collection, 'collection')->create();

    $collection->delete();

    expect($media->refresh()->media_collection_id)->toBeNull();
});

it('keeps the media and clears its uploader when the account is deleted', function () {
    $user = User::factory()->create();
    $media = Media::factory()->create();
    $media->uploaded_by = $user->id;
    $media->save();

    $user->delete();

    expect($media->refresh()->uploaded_by)->toBeNull();
});
