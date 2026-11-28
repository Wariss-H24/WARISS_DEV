<?php

use App\Enums\ActivityEvent;
use App\Enums\DeviceType;
use App\Enums\ReferrerType;
use App\Models\Admin\ActivityLog;
use App\Models\Analytics\AnalyticsEvent;
use App\Models\Analytics\PageView;
use App\Models\Blog\Post;
use App\Models\Pages\Page;
use App\Models\Portfolio\Project;
use App\Models\Resume\Resume;
use App\Models\Seo\Redirect;
use App\Models\Seo\SeoMetadata;
use App\Models\User;
use App\Services\PreviewToken;
use Illuminate\Support\Facades\Storage;

/*
| API publique : CV, redirections, sitemap, aperçus signés et mesure d'audience sans cookie.
*/

const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0 Safari/537.36';
const PHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1';

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
    $this->withHeader('User-Agent', BROWSER);
});

it('downloads the current version of the public resume of the language and counts it', function () {
    $resume = Resume::factory()->published(isDefault: true)->create(['title' => 'Développeur full-stack']);
    $media = $resume->refresh()->currentVersion->media;
    Storage::disk('local')->put($media->path, '%PDF-1.4 cv');

    $response = $this->get('/api/v1/public/resume/download?locale=fr')->assertOk();

    expect($response->headers->get('Content-Disposition'))->toContain('CV-developpeur-full-stack.pdf')
        ->and($response->streamedContent())->toBe('%PDF-1.4 cv')
        ->and(AnalyticsEvent::query()->where('name', 'cv_download')->value('path'))->toBe('/fr/resume');

    $this->getJson('/api/v1/public/resume')->assertOk()->assertJsonPath('data.0.locale', 'fr')->assertJsonMissingPath('data.0.media_id');
});

it('falls back to the default language resume and answers 404 without any public resume', function () {
    $this->get('/api/v1/public/resume/download?locale=en')->assertNotFound();

    $resume = Resume::factory()->published()->create();
    Storage::disk('local')->put($resume->refresh()->currentVersion->media->path, '%PDF-1.4');
    Resume::factory()->create(['locale' => 'en']);

    $this->get('/api/v1/public/resume/download?locale=en')->assertOk();
});

it('resolves an active redirect and counts it', function () {
    $redirect = Redirect::factory()->create(['from_path' => '/fr/ancien-projet', 'to_path' => '/fr/projects/nouveau']);
    Redirect::factory()->create(['from_path' => '/fr/inactif', 'to_path' => '/fr', 'is_active' => false]);

    $this->getJson('/api/v1/public/redirect?path=/fr/ancien-projet/?utm_source=x')
        ->assertOk()
        ->assertJsonPath('data.to_path', '/fr/projects/nouveau')
        ->assertJsonPath('data.status_code', 301);
    expect($redirect->refresh()->hits)->toBe(1);

    $this->getJson('/api/v1/public/redirect?path=/fr/inactif')->assertNotFound();
    $this->getJson('/api/v1/public/redirect?path=//evil.test')->assertUnprocessable();
});

it('generates a sitemap with language alternates, without unpublished or non-indexed contents', function () {
    config()->set('wariss.site_url', 'https://wariss.dev');
    Page::factory()->system('home')->published()->create();
    $project = Project::factory()->published()->create();
    Project::factory()->create();
    $hidden = Post::factory()->published()->create();
    SeoMetadata::factory()->for($hidden, 'seoable')->create(['robots_index' => false]);

    $xml = $this->get('/api/v1/public/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->getContent();

    expect($xml)->toContain('<loc>https://wariss.dev/fr</loc>')
        ->toContain('https://wariss.dev/fr/projects/'.$project->translate('fr')->slug)
        ->toContain('hreflang="en"')
        ->not->toContain($hidden->translate('fr')->slug)
        ->and(substr_count((string) $xml, '<url>'))->toBe(4);
});

it('previews a draft through a signed link, never cached nor indexed', function () {
    $draft = Project::factory()->create();
    $token = app(PreviewToken::class)->issue('project', $draft->id, 'en');

    $this->getJson("/api/v1/public/preview/{$token}")
        ->assertOk()
        ->assertJsonPath('data.id', $draft->id)
        ->assertJsonPath('data.title', $draft->translate('en')->title)
        ->assertJsonPath('data.is_preview', true)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Cache-Control', 'no-store, private');
});

it('refuses a tampered or expired preview link', function () {
    $draft = Project::factory()->create();
    $other = Project::factory()->create();
    $token = app(PreviewToken::class)->issue('project', $draft->id, 'fr');

    $this->getJson('/api/v1/public/preview/'.str_replace(".{$draft->id}.", ".{$other->id}.", $token))->assertNotFound();

    $this->travel(31)->minutes();
    $this->getJson("/api/v1/public/preview/{$token}")->assertNotFound();
});

it('lets both accounts generate a preview link, logged in the activity log', function () {
    $draft = Post::factory()->create();
    $this->withHeader('Referer', 'http://localhost:5174')->actingAs(User::factory()->create());

    $response = $this->getJson("/api/v1/admin/preview-link?type=post&id={$draft->id}&locale=fr")->assertOk();

    expect($response->json('data.url'))->toStartWith(config('wariss.site_url').'/fr/preview/')
        ->and(ActivityLog::query()->where('event', ActivityEvent::Updated)->where('subject_type', 'post')->value('description'))->toBe('aperçu généré');
    $this->getJson("/api/v1/public/preview/{$response->json('data.token')}")->assertOk()->assertJsonPath('data.id', $draft->id);
});

it('records a page view without cookie, IP nor query string', function () {
    $project = Project::factory()->published()->create();

    $this->withHeaders(['User-Agent' => PHONE, 'CF-IPCountry' => 'BJ'])->postJson('/api/v1/public/track/view', [
        'path' => '/fr/projects/x?email=moi@example.test#haut',
        'locale' => 'fr',
        'referrer' => 'https://www.linkedin.com/feed/',
        'utm_medium' => 'share',
        'content_type' => 'project',
        'content_id' => $project->id,
    ])->assertNoContent()->assertCookieMissing('laravel_session');

    $view = PageView::query()->sole();
    expect($view->path)->toBe('/fr/projects/x')
        ->and($view->referrer_host)->toBe('linkedin.com')
        ->and($view->referrer_type)->toBe(ReferrerType::Social)
        ->and($view->device_type)->toBe(DeviceType::Mobile)
        ->and($view->country_code)->toBe('BJ')
        ->and($view->viewable_id)->toBe($project->id)
        ->and($view->session_hash)->toMatch('/^[0-9a-f]{64}$/')
        ->and(json_encode($view->getAttributes()))->not->toContain('127.0.0.1');
});

it('gives the same visitor the same fingerprint during a day only', function () {
    $payload = ['path' => '/fr', 'locale' => 'fr'];
    $this->postJson('/api/v1/public/track/view', $payload);
    $this->postJson('/api/v1/public/track/view', $payload);
    $this->travel(1)->days();
    $this->postJson('/api/v1/public/track/view', $payload);

    expect(PageView::query()->distinct()->count('session_hash'))->toBe(2);
});

it('ignores robots and visitors who ask not to be tracked', function (array $headers) {
    $this->withHeaders($headers)->postJson('/api/v1/public/track/view', ['path' => '/fr', 'locale' => 'fr'])->assertNoContent();

    expect(PageView::count())->toBe(0);
})->with([
    'robot' => [['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)']],
    'do not track' => [['DNT' => '1']],
    'global privacy control' => [['Sec-GPC' => '1']],
]);

it('records share events with their network and refuses server-side events', function () {
    $post = Post::factory()->published()->create();

    $this->postJson('/api/v1/public/track/event', ['name' => 'share', 'path' => '/fr/blog/x'])->assertJsonValidationErrors(['network']);
    $this->postJson('/api/v1/public/track/event', ['name' => 'cv_download', 'path' => '/fr/resume'])->assertJsonValidationErrors(['name']);
    $this->postJson('/api/v1/public/track/event', ['name' => 'share', 'path' => '/fr/blog/x', 'network' => 'whatsapp', 'content_type' => 'post', 'content_id' => $post->id])
        ->assertNoContent();

    $event = AnalyticsEvent::query()->sole();
    expect($event->properties['network'] ?? null)->toBe('whatsapp')
        ->and($event->eventable_id)->toBe($post->id);
});
