<?php

use App\Enums\MediaVisibility;
use App\Models\Blog\Post;
use App\Models\Career\Education;
use App\Models\Career\Experience;
use App\Models\Career\TimelineEvent;
use App\Models\Communication\Testimonial;
use App\Models\Identity\Profile;
use App\Models\Identity\SocialLink;
use App\Models\Media\Media;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use App\Models\Pages\PageSectionItem;
use App\Models\Portfolio\CaseStudy;
use App\Models\Portfolio\Project;
use App\Models\Seo\SeoMetadata;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use App\Models\Settings\SiteSetting;

/*
| API publique : seuls les contenus publiés et visibles sortent, sans aucun champ interne
| ni média privé, dans la langue demandée.
*/

it('lists only published projects, without internal fields', function () {
    $published = Project::factory()->published()->create();
    Project::factory()->create();
    Project::factory()->create(['status' => 'scheduled', 'published_at' => now()->addDay()]);

    $response = $this->getJson('/api/v1/public/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $published->id)
        ->assertJsonPath('meta.total', 1);

    expect($response->json('data.0'))->not->toHaveKeys(['status', 'created_at', 'updated_at', 'deleted_at', 'position', 'translations']);
});

it('answers 404 for a draft content, even with its exact slug', function () {
    $draft = Project::factory()->create();

    $this->getJson('/api/v1/public/projects/'.$draft->translate('fr')->slug)->assertNotFound();
});

it('serves a content in the requested language, with the slugs of every language', function () {
    $project = Project::factory()->published()->create();
    $enSlug = $project->translate('en')->slug;

    $this->getJson("/api/v1/public/projects/{$enSlug}?locale=en")
        ->assertOk()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('data.locale', 'en')
        ->assertJsonPath('data.title', $project->translate('en')->title)
        ->assertJsonPath('data.slugs.fr', $project->translate('fr')->slug);

    $this->getJson("/api/v1/public/projects/{$enSlug}?locale=fr")
        ->assertOk()
        ->assertJsonPath('data.title', $project->translate('fr')->title);
});

it('hides the client and the links of a confidential project', function () {
    $project = Project::factory()->published()->confidential()->create(['client_name' => 'Banque secrète', 'live_url' => 'https://interne.test']);

    $this->getJson('/api/v1/public/projects/'.$project->translate('fr')->slug)
        ->assertOk()
        ->assertJsonPath('data.client_name', null)
        ->assertJsonPath('data.live_url', null)
        ->assertJsonPath('data.repository_url', null);
});

it('never exposes a private media', function () {
    $public = Media::factory()->create();
    $private = Media::factory()->privateDocument()->create();
    $shown = Project::factory()->published()->create(['cover_media_id' => $public->id]);
    $hidden = Project::factory()->published()->create(['cover_media_id' => $private->id]);

    $this->getJson('/api/v1/public/projects/'.$shown->translate('fr')->slug)
        ->assertJsonPath('data.cover.id', $public->id)
        ->assertJsonPath('data.cover.url', fn (string $url): bool => str_ends_with($url, $public->path))
        ->assertJsonMissingPath('data.cover_media_id');
    $this->getJson('/api/v1/public/projects/'.$hidden->translate('fr')->slug)
        ->assertJsonPath('data.cover', null);

    $education = Education::factory()->create(['proof_media_id' => $private->id]);
    expect($this->getJson('/api/v1/public/educations')->json('data.0'))->not->toHaveKeys(['proof', 'proof_media_id'])
        ->and($education->exists)->toBeTrue();
    expect($private->refresh()->visibility)->toBe(MediaVisibility::Private);
});

it('shows a published case study without its unpublished project', function () {
    $caseStudy = CaseStudy::factory()->published()->create(['project_id' => Project::factory()->create()->id]);

    $this->getJson('/api/v1/public/case-studies/'.$caseStudy->translate('fr')->slug)
        ->assertOk()
        ->assertJsonPath('data.project', null);
});

it('returns the SEO record with a content', function () {
    $post = Post::factory()->published()->create();
    SeoMetadata::factory()->for($post, 'seoable')->create(['robots_index' => false]);

    $this->getJson('/api/v1/public/posts/'.$post->translate('fr')->slug)
        ->assertOk()
        ->assertJsonPath('data.seo.robots_index', false)
        ->assertJsonMissingPath('data.author_id');
});

it('filters posts by category and searches them', function () {
    $post = Post::factory()->published()->create(['fr' => ['title' => 'Mesurer sans cookie', 'slug' => 'mesurer-sans-cookie', 'excerpt' => 'Résumé', 'content' => 'Audience respectueuse.']]);
    Post::factory()->published()->create();
    $categorySlug = $post->category->translate('fr')->slug;

    $this->getJson("/api/v1/public/posts?category={$categorySlug}")->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/public/posts?search=cookie&locale=fr')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $post->id);
    $this->getJson('/api/v1/public/search?q=cookie&locale=fr')->assertOk()->assertJsonPath('data.posts.0.id', $post->id);
    $this->getJson('/api/v1/public/search?q=a')->assertJsonValidationErrors(['q']);
});

it('serves a system page by key with only its public featured contents', function () {
    $home = Page::factory()->system('home')->published()->create();
    $section = PageSection::factory()->for($home)->create();
    PageSection::factory()->for($home)->inactive()->create();
    $visible = Project::factory()->published()->create();
    $draft = Project::factory()->create();
    PageSectionItem::query()->create(['page_section_id' => $section->id, 'itemable_type' => 'project', 'itemable_id' => $visible->id, 'position' => 0]);
    PageSectionItem::query()->create(['page_section_id' => $section->id, 'itemable_type' => 'project', 'itemable_id' => $draft->id, 'position' => 1]);

    $this->getJson('/api/v1/public/pages/home')
        ->assertOk()
        ->assertJsonCount(1, 'data.sections')
        ->assertJsonCount(1, 'data.sections.0.items')
        ->assertJsonPath('data.sections.0.items.0.itemable.id', $visible->id);

    Page::factory()->system('about')->create();
    $this->getJson('/api/v1/public/pages/about')->assertNotFound();
});

it('serves the site data: public settings, profile and menus', function () {
    SiteSetting::factory()->exposedToPublic()->create(['group' => 'general', 'key' => 'site_name', 'value' => 'WARISS.DEV']);
    SiteSetting::factory()->create(['group' => 'internal', 'key' => 'secret', 'value' => 'chut']);
    $profile = Profile::factory()->create();
    SocialLink::factory()->for($profile)->create(['platform' => 'github']);
    SocialLink::factory()->for($profile)->create(['platform' => 'x', 'is_visible' => false]);
    $menu = NavigationMenu::factory()->create(['location' => 'header']);
    NavigationItem::factory()->for($menu, 'menu')->toPage(Page::factory()->published()->create())->create();
    NavigationItem::factory()->for($menu, 'menu')->toPage(Page::factory()->create())->create();

    $response = $this->getJson('/api/v1/public/site')
        ->assertOk()
        ->assertJsonPath('data.settings.general.site_name', 'WARISS.DEV')
        ->assertJsonMissingPath('data.settings.internal')
        ->assertJsonPath('data.profile.display_name', $profile->display_name)
        ->assertJsonCount(1, 'data.profile.social_links')
        ->assertJsonCount(1, 'data.menus.header.items');

    expect($response->json('data.profile'))->not->toHaveKey('user_id');
});

it('shows a timeline event only when its source is public', function () {
    TimelineEvent::factory()->forSource(Experience::factory()->create())->create();
    TimelineEvent::factory()->forSource(Experience::factory()->create(['is_visible' => false]))->create();
    TimelineEvent::factory()->forSource(Project::factory()->create())->create();

    $this->getJson('/api/v1/public/timeline')->assertOk()->assertJsonCount(1, 'data');
});

it('lists only approved testimonials', function () {
    Testimonial::factory()->approved()->create();
    Testimonial::factory()->create();

    $this->getJson('/api/v1/public/testimonials')->assertOk()->assertJsonCount(1, 'data');
});

it('lists the technologies by category and the taxonomies', function () {
    Project::factory()->published()->create();

    $this->getJson('/api/v1/public/technologies')->assertOk();
    $this->getJson('/api/v1/public/taxonomies/project-categories')->assertOk()->assertJsonCount(1, 'data');
    $this->getJson('/api/v1/public/taxonomies/users')->assertNotFound();
});
