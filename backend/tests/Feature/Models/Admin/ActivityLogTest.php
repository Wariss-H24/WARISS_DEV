<?php

use App\Enums\ActivityEvent;
use App\Models\Admin\ActivityLog;
use App\Models\Portfolio\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('records an action on a content with the names of the changed fields', function () {
    $project = Project::factory()->create();

    $log = ActivityLog::factory()->create([
        'event' => ActivityEvent::Updated,
        'subject_type' => $project->getMorphClass(),
        'subject_id' => $project->id,
        'properties' => ['changed' => ['status', 'published_at']],
    ]);

    expect($log->refresh())
        ->event->toBe(ActivityEvent::Updated)
        ->properties->toBe(['changed' => ['status', 'published_at']])
        ->and($log->subject->is($project))->toBeTrue()
        ->and($log->updated_at)->toBeNull();
});

it('stores an action without details as an empty JSON object', function () {
    $log = ActivityLog::factory()->create(['properties' => []]);

    expect(DB::table('activity_logs')->where('id', $log->id)->value('properties'))->toBe('{}');
});

it('rejects an unknown event, a half-filled subject or an invalid IP address', function (array $values, string $constraint) {
    $log = ActivityLog::factory()->create();

    expect(fn () => DB::table('activity_logs')->where('id', $log->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'événement inconnu' => [['event' => 'viewed'], 'activity_logs_event_check'],
    'type de sujet sans identifiant' => [['subject_type' => 'project', 'subject_id' => null], 'activity_logs_subject_check'],
    'adresse IP invalide' => [['ip_address' => 'pas-une-ip'], 'inet'],
]);

it('keeps the log entry and clears the account when the account is deleted', function () {
    $log = ActivityLog::factory()->create();

    $log->user->delete();

    expect($log->refresh()->user_id)->toBeNull();
});

it('deletes the entries older than the retention period', function () {
    $this->travelTo('2025-01-01 12:00:00');
    ActivityLog::factory()->create();
    $this->travelTo('2025-12-01 12:00:00');
    $recent = ActivityLog::factory()->create();

    $this->travelTo('2026-02-01 12:00:00');
    $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

    expect(ActivityLog::pluck('id')->all())->toBe([$recent->id]);
});
