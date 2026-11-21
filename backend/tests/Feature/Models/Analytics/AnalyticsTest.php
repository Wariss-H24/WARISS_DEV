<?php

use App\Enums\AnalyticsEventName;
use App\Enums\ReferrerType;
use App\Models\Analytics\AnalyticsEvent;
use App\Models\Analytics\DailyStat;
use App\Models\Analytics\PageView;
use App\Models\Portfolio\Project;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('stores a page view without any IP address nor personal data column', function () {
    $project = Project::factory()->create();

    $view = PageView::factory()->fromShare('whatsapp')->create([
        'path' => '/fr/projects/mon-projet',
        'viewable_type' => $project->getMorphClass(),
        'viewable_id' => $project->id,
    ]);

    expect($view->refresh())
        ->referrer_type->toBe(ReferrerType::Social)
        ->utm_medium->toBe('share')
        ->utm_source->toBe('whatsapp')
        ->and($view->viewable->is($project))->toBeTrue()
        ->and(Schema::getColumnListing('page_views'))->not->toContain('ip_address', 'user_agent', 'email');
});

it('records a share with its network', function (string $network) {
    $event = AnalyticsEvent::factory()->share($network)->create();

    expect($event->refresh())
        ->name->toBe(AnalyticsEventName::Share)
        ->properties->toBe(['network' => $network]);
})->with(['linkedin', 'x', 'facebook', 'whatsapp', 'email', 'copy_link']);

it('rejects a share without network or with an unknown network', function (array $properties) {
    AnalyticsEvent::factory()->create(['name' => AnalyticsEventName::Share, 'properties' => $properties]);
})->with([
    'sans réseau' => [[]],
    'réseau inconnu' => [['network' => 'myspace']],
])->throws(QueryException::class, 'events_share_network_check');

it('rejects invalid page views at the database level', function (array $values, string $constraint) {
    $view = PageView::factory()->create();

    expect(fn () => DB::table('page_views')->where('id', $view->id)->update($values))
        ->toThrow(QueryException::class, $constraint);
})->with([
    'empreinte en clair' => [['session_hash' => '192.168.1.10'], 'page_views_session_hash_check'],
    'chemin externe' => [['path' => 'https://ailleurs.example'], 'page_views_path_check'],
    'appareil inconnu' => [['device_type' => 'tv'], 'page_views_device_type_check'],
    'provenance inconnue' => [['referrer_type' => 'email'], 'page_views_referrer_type_check'],
    'contenu sans identifiant' => [['viewable_type' => 'project', 'viewable_id' => null], 'page_views_viewable_check'],
]);

it('rejects an unknown event name at the database level', function () {
    $event = AnalyticsEvent::factory()->create();

    DB::table('events')->where('id', $event->id)->update(['name' => 'page_scroll']);
})->throws(QueryException::class, 'events_name_check');

it('deletes raw views and events older than 90 days and keeps the daily aggregates', function () {
    $this->travelTo('2026-01-01 12:00:00');
    PageView::factory()->create(['viewed_at' => now()]);
    AnalyticsEvent::factory()->create(['occurred_at' => now()]);
    DailyStat::factory()->create(['stat_date' => '2026-01-01']);

    $this->travelTo('2026-03-15 12:00:00');
    $recentView = PageView::factory()->create(['viewed_at' => now()]);
    $recentEvent = AnalyticsEvent::factory()->create(['occurred_at' => now()]);

    $this->travelTo('2026-04-10 12:00:00');
    $this->artisan('model:prune', ['--model' => [PageView::class, AnalyticsEvent::class]])->assertSuccessful();

    expect(PageView::pluck('id')->all())->toBe([$recentView->id])
        ->and(AnalyticsEvent::pluck('id')->all())->toBe([$recentEvent->id])
        ->and(DailyStat::count())->toBe(1);
});

it('keeps a single aggregate per day, metric and dimension', function () {
    DailyStat::factory()->create(['metric' => 'page_views', 'dimension_type' => 'referrer_type', 'dimension_value' => 'social']);

    DailyStat::factory()->create(['metric' => 'page_views', 'dimension_type' => 'referrer_type', 'dimension_value' => 'social']);
})->throws(QueryException::class, 'analytics_daily_stats_dimension_unique');

it('rejects a negative aggregate', function () {
    DailyStat::factory()->create(['value' => -1]);
})->throws(QueryException::class, 'analytics_daily_stats_value_check');
