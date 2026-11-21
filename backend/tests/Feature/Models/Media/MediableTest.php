<?php

use App\Enums\MediableRole;
use App\Models\Media\Media;
use App\Models\Media\Mediable;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function attachMedia(Media $media, User $owner, string $role = 'gallery'): Mediable
{
    return Mediable::create([
        'media_id' => $media->id,
        'mediable_type' => $owner->getMorphClass(),
        'mediable_id' => $owner->id,
        'role' => $role,
    ]);
}

it('stores the short morph alias instead of the class name', function () {
    $mediable = attachMedia(Media::factory()->create(), User::factory()->create());

    expect(DB::table('mediables')->value('mediable_type'))->toBe('user')
        ->and($mediable->refresh()->mediable)->toBeInstanceOf(User::class)
        ->and($mediable->role)->toBe(MediableRole::Gallery);
});

it('refuses to delete a media that is still attached to a content', function () {
    $media = Media::factory()->create();
    attachMedia($media, User::factory()->create());

    $media->delete();
})->throws(QueryException::class, 'mediables_media_id_foreign');

it('lists the usages of a media', function () {
    $media = Media::factory()->create();
    $owner = User::factory()->create();
    attachMedia($media, $owner, 'gallery');
    attachMedia($media, $owner, 'attachment');

    expect($media->usages()->pluck('role')->all())
        ->toEqualCanonicalizing([MediableRole::Gallery, MediableRole::Attachment]);
});

it('rejects attaching the same media twice with the same role to the same content', function () {
    $media = Media::factory()->create();
    $owner = User::factory()->create();
    attachMedia($media, $owner);

    attachMedia($media, $owner);
})->throws(QueryException::class, 'mediables_media_id_mediable_type_mediable_id_role_unique');

it('rejects an unknown attachment role at the database level', function () {
    $mediable = attachMedia(Media::factory()->create(), User::factory()->create());

    DB::table('mediables')->where('id', $mediable->id)->update(['role' => 'cover']);
})->throws(QueryException::class, 'mediables_role_check');

it('rejects an unknown attachment role before reaching the database', function () {
    attachMedia(Media::factory()->create(), User::factory()->create(), 'cover');
})->throws(ValueError::class);
