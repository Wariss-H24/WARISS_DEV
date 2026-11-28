<?php

use App\Enums\PublicationStatus;
use App\Models\Analytics\AnalyticsEvent;
use App\Models\Analytics\DailyStat;
use App\Models\Analytics\PageView;
use App\Models\Communication\Message;
use App\Models\Portfolio\Project;
use App\Models\User;
use Carbon\CarbonImmutable;

/*
| Agrégats d'audience (D17), statistiques, tableau de bord et publication programmée.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    $this->travelTo(CarbonImmutable::parse('2026-11-20 10:00:00'));
    $this->actingAs(User::factory()->admin()->create());
});

it('aggregates a day of audience, idempotently', function () {
    $visitor = hash('sha256', 'visiteur-du-jour');
    PageView::factory()->count(2)->create(['viewed_at' => '2026-11-19 08:00:00', 'session_hash' => $visitor, 'path' => '/fr/projects']);
    PageView::factory()->fromShare()->create(['viewed_at' => '2026-11-19 09:00:00', 'path' => '/fr/blog', 'viewable_type' => 'post', 'viewable_id' => 7]);
    PageView::factory()->create(['viewed_at' => '2026-11-18 23:59:59']);
    AnalyticsEvent::factory()->share('x')->create(['occurred_at' => '2026-11-19 09:05:00', 'eventable_type' => 'post', 'eventable_id' => 7]);
    AnalyticsEvent::factory()->create(['occurred_at' => '2026-11-19 10:00:00']);

    $this->artisan('analytics:aggregate')->assertSuccessful();
    $this->artisan('analytics:aggregate', ['date' => '2026-11-19'])->assertSuccessful();

    $stat = fn (string $metric, string $type = '', string $value = ''): ?int => DailyStat::query()
        ->whereDate('stat_date', '2026-11-19')
        ->where(['metric' => $metric, 'dimension_type' => $type, 'dimension_value' => $value])
        ->value('value');

    expect($stat('page_views'))->toBe(3)
        ->and($stat('visitors'))->toBe(2)
        ->and($stat('shares'))->toBe(1)
        ->and($stat('page_views', 'path', '/fr/projects'))->toBe(2)
        ->and($stat('page_views', 'utm_medium', 'share'))->toBe(1)
        ->and($stat('page_views', 'content', 'post:7'))->toBe(1)
        ->and($stat('shares', 'network', 'x'))->toBe(1)
        ->and($stat('shares', 'content', 'post:7'))->toBe(1)
        ->and($stat('events', 'name', 'cv_download'))->toBe(1)
        ->and(DailyStat::query()->whereDate('stat_date', '2026-11-19')->where('metric', 'page_views')->where('dimension_type', '')->count())->toBe(1);
});

it('rejects an invalid aggregation date', function () {
    $this->artisan('analytics:aggregate', ['date' => '19/11/2026'])->assertFailed();
    $this->artisan('analytics:aggregate', ['--days' => 0])->assertFailed();
});

it('reports the audience of a period, with today computed live', function () {
    DailyStat::factory()->create(['stat_date' => '2026-11-19', 'metric' => 'page_views', 'value' => 40]);
    DailyStat::factory()->create(['stat_date' => '2026-11-19', 'metric' => 'visitors', 'value' => 12]);
    DailyStat::factory()->create(['stat_date' => '2026-11-19', 'metric' => 'page_views', 'dimension_type' => 'path', 'dimension_value' => '/fr', 'value' => 30]);
    DailyStat::factory()->create(['stat_date' => '2026-11-19', 'metric' => 'shares', 'dimension_type' => 'network', 'dimension_value' => 'linkedin', 'value' => 3]);
    PageView::factory()->create(['viewed_at' => '2026-11-20 09:00:00', 'path' => '/fr']);

    $this->getJson('/api/v1/admin/analytics?from=2026-11-19&to=2026-11-20')
        ->assertOk()
        ->assertJsonPath('data.totals.page_views', 41)
        ->assertJsonPath('data.totals.visitors', 13)
        ->assertJsonPath('data.daily.0.page_views', 40)
        ->assertJsonPath('data.daily.1.page_views', 1)
        ->assertJsonPath('data.top.paths.0', ['value' => '/fr', 'count' => 31])
        ->assertJsonPath('data.top.share_networks.0.value', 'linkedin');
});

it('validates the analytics period', function () {
    $this->getJson('/api/v1/admin/analytics?from=2026-11-20&to=2026-11-01')->assertJsonValidationErrors(['to']);
    $this->getJson('/api/v1/admin/analytics?from=2024-01-01&to=2026-11-20')->assertJsonValidationErrors(['from']);
    $this->getJson('/api/v1/admin/analytics')->assertOk()->assertJsonCount(30, 'data.daily');
});

it('summarises the editorial state and keeps the inbox for the admin only', function () {
    Project::factory()->count(2)->create();
    Project::factory()->published()->create();
    Message::factory()->create();

    $this->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonPath('data.contents.projects.draft', 2)
        ->assertJsonPath('data.contents.projects.published', 1)
        ->assertJsonPath('data.inbox.messages_new', 1)
        ->assertJsonCount(7, 'data.audience.daily');

    $this->actingAs(User::factory()->create());
    $this->getJson('/api/v1/admin/dashboard')
        ->assertOk()
        ->assertJsonMissingPath('data.inbox')
        ->assertJsonMissingPath('data.recent_activity');
    $this->getJson('/api/v1/admin/analytics')->assertOk();
});

it('publishes scheduled contents once their date is reached', function () {
    $due = Project::factory()->create(['status' => PublicationStatus::Scheduled, 'published_at' => now()->addMinutes(5)]);
    $later = Project::factory()->create(['status' => PublicationStatus::Scheduled, 'published_at' => now()->addDay()]);

    $this->travel(10)->minutes();
    $this->artisan('content:publish-scheduled')->assertSuccessful();

    expect($due->refresh()->status)->toBe(PublicationStatus::Published)
        ->and($later->refresh()->status)->toBe(PublicationStatus::Scheduled);
});
