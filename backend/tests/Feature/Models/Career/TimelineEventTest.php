<?php

use App\Enums\TimelineEventType;
use App\Models\Career\Achievement;
use App\Models\Career\Certification;
use App\Models\Career\Education;
use App\Models\Career\Experience;
use App\Models\Career\TimelineEvent;
use App\Models\Portfolio\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('links an event to its source without copying it', function () {
    $experience = Experience::factory()->create();

    $event = TimelineEvent::factory()->forSource($experience)->create(['event_date' => '2023-01-01']);

    expect($event->refresh())
        ->type->toBe(TimelineEventType::Experience)
        ->isManual()->toBeFalse()
        ->and($event->eventable->is($experience))->toBeTrue()
        ->and($experience->timelineEvents()->pluck('id')->all())->toBe([$event->id]);
});

it('stores a manual event with its translated title', function () {
    $event = TimelineEvent::factory()->create();

    expect($event->refresh()->isManual())->toBeTrue()
        ->and($event->translate('fr')->title)->toStartWith('Apprentissage');
});

it('rejects an event whose type does not match its source, or a half-filled source', function (array $values) {
    $event = TimelineEvent::factory()->create();

    DB::table('timeline_events')->where('id', $event->id)->update($values);
})->with([
    'type différent de la source' => [['type' => 'experience', 'eventable_type' => 'project', 'eventable_id' => 1]],
    'événement manuel avec source' => [['type' => 'learning', 'eventable_type' => 'learning', 'eventable_id' => 1]],
    'type de source sans identifiant' => [['type' => 'project', 'eventable_type' => 'project', 'eventable_id' => null]],
    'identifiant sans type de source' => [['eventable_type' => null, 'eventable_id' => 1]],
])->throws(QueryException::class, 'timeline_events_source_check');

it('rejects an end date before the event date', function () {
    TimelineEvent::factory()->create(['event_date' => '2024-06-01', 'end_date' => '2024-01-01']);
})->throws(QueryException::class, 'timeline_events_dates_check');

it('removes the events of a source deleted permanently', function (string $model) {
    $source = $model::factory()->create();
    TimelineEvent::factory()->forSource($source)->create();
    $manual = TimelineEvent::factory()->create();

    method_exists($source, 'forceDelete') ? $source->forceDelete() : $source->delete();

    expect(TimelineEvent::pluck('id')->all())->toBe([$manual->id]);
})->with([
    'expérience' => [Experience::class],
    'formation' => [Education::class],
    'certification' => [Certification::class],
    'projet' => [Project::class],
    'réalisation' => [Achievement::class],
]);

it('keeps the events of a soft deleted source, which can be restored', function () {
    $project = Project::factory()->create();
    TimelineEvent::factory()->forSource($project)->create();

    $project->delete();

    expect(TimelineEvent::count())->toBe(1);
});

it('lists visible events from the most recent', function () {
    $old = TimelineEvent::factory()->create(['event_date' => '2020-01-01']);
    $recent = TimelineEvent::factory()->create(['event_date' => '2025-01-01']);
    TimelineEvent::factory()->create(['event_date' => '2026-01-01', 'is_visible' => false]);

    expect(TimelineEvent::visible()->chronological()->pluck('id')->all())->toBe([$recent->id, $old->id]);
});
