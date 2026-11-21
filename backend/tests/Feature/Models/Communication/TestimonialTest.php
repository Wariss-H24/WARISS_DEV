<?php

use App\Enums\TestimonialStatus;
use App\Models\Communication\Testimonial;
use App\Models\Media\Media;
use App\Models\Portfolio\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('creates a new testimonial as pending and without consent', function () {
    $testimonial = Testimonial::forceCreate(['author_name' => 'Awa']);

    expect($testimonial->refresh())
        ->status->toBe(TestimonialStatus::Pending)
        ->consent_given->toBeFalse();
});

it('refuses to approve a testimonial without the consent of its author', function () {
    $testimonial = Testimonial::factory()->create();

    DB::table('testimonials')->where('id', $testimonial->id)->update(['status' => 'approved']);
})->throws(QueryException::class, 'testimonials_approved_requires_consent_check');

it('publishes only approved, consented and visible testimonials', function () {
    $published = Testimonial::factory()->approved()->create();
    Testimonial::factory()->create();
    Testimonial::factory()->create(['consent_given' => true, 'status' => TestimonialStatus::Rejected]);
    Testimonial::factory()->approved()->create(['is_visible' => false]);

    expect(Testimonial::publishable()->pluck('id')->all())->toBe([$published->id]);
});

it('does not allow the status nor the consent to be mass assigned', function () {
    $testimonial = new Testimonial(['author_name' => 'Intrus', 'status' => 'approved', 'consent_given' => true]);

    expect($testimonial->status)->toBeNull()
        ->and($testimonial->consent_given)->toBeNull();
});

it('stores the translated quote and author role', function () {
    $testimonial = Testimonial::factory()->create([
        'fr' => ['quote' => 'Un travail remarquable.', 'author_role' => 'Directrice'],
        'en' => ['quote' => 'Remarkable work.', 'author_role' => 'Director'],
    ]);
    $testimonial = Testimonial::with('translations')->find($testimonial->id);

    app()->setLocale('en');

    expect($testimonial->quote)->toBe('Remarkable work.')
        ->and($testimonial->author_role)->toBe('Director');
});

it('keeps the testimonial when its project or photo is deleted', function () {
    $project = Project::factory()->create();
    $photo = Media::factory()->create();
    $testimonial = Testimonial::factory()->create(['project_id' => $project->id, 'photo_media_id' => $photo->id]);

    $project->forceDelete();
    $photo->delete();

    expect($testimonial->refresh())
        ->project_id->toBeNull()
        ->photo_media_id->toBeNull();
});
