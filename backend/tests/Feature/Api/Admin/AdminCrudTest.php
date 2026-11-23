<?php

use App\Enums\ActivityEvent;
use App\Enums\PublicationStatus;
use App\Models\Admin\ActivityLog;
use App\Models\Catalog\Technology;
use App\Models\Catalog\TechnologyCategory;
use App\Models\Portfolio\Project;
use App\Models\Portfolio\ProjectCategory;
use App\Models\Seo\Redirect;
use App\Models\User;

/*
| Socle CRUD de l'administration (AdminCrudController, AdminFormRequest, SaveModel, AdminResource),
| éprouvé sur les catégories de technologies et les projets.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
});

function actingAsAdmin(): User
{
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);

    return $admin;
}

function categoryPayload(string $slug = 'frontend'): array
{
    return [
        'position' => 2,
        'translations' => [
            'fr' => ['name' => 'Interface', 'slug' => $slug],
            'en' => ['name' => 'Frontend', 'slug' => $slug.'-en'],
        ],
    ];
}

it('rejects guests', function () {
    $this->getJson('/api/v1/admin/technology-categories')->assertUnauthorized();
});

it('lets the viewer read but never write', function () {
    $category = TechnologyCategory::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->getJson('/api/v1/admin/technology-categories')->assertOk()->assertJsonCount(1, 'data');
    $this->postJson('/api/v1/admin/technology-categories', categoryPayload())->assertForbidden();
    $this->patchJson("/api/v1/admin/technology-categories/{$category->id}", ['position' => 9])->assertForbidden();
    $this->deleteJson("/api/v1/admin/technology-categories/{$category->id}")->assertForbidden();
    expect(TechnologyCategory::count())->toBe(1);
});

it('creates a translated content, returns it in every language and logs it', function () {
    $admin = actingAsAdmin();

    $response = $this->postJson('/api/v1/admin/technology-categories', categoryPayload());

    $response->assertCreated()
        ->assertJsonPath('data.position', 2)
        ->assertJsonPath('data.translations.fr.slug', 'frontend')
        ->assertJsonPath('data.translations.en.name', 'Frontend')
        ->assertJsonMissingPath('data.translations.fr.technology_category_id');
    expect(ActivityLog::sole())
        ->event->toBe(ActivityEvent::Created)
        ->user_id->toBe($admin->id)
        ->subject_id->toBe($response->json('data.id'));
});

it('requires the default language on creation only', function () {
    actingAsAdmin();
    $category = TechnologyCategory::factory()->create();

    $this->postJson('/api/v1/admin/technology-categories', ['translations' => ['en' => ['name' => 'Frontend', 'slug' => 'frontend']]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['translations.fr']);

    $this->patchJson("/api/v1/admin/technology-categories/{$category->id}", ['translations' => ['en' => ['name' => 'Renamed']]])
        ->assertOk()
        ->assertJsonPath('data.translations.en.name', 'Renamed')
        ->assertJsonPath('data.translations.fr.name', $category->translate('fr')->name);
});

it('keeps slugs unique per language, ignoring the edited content', function () {
    actingAsAdmin();
    $existing = TechnologyCategory::factory()->create(['fr' => ['name' => 'Base', 'slug' => 'base-de-donnees']]);

    $this->postJson('/api/v1/admin/technology-categories', categoryPayload('base-de-donnees'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['translations.fr.slug']);

    $this->patchJson("/api/v1/admin/technology-categories/{$existing->id}", ['translations' => ['fr' => ['slug' => 'base-de-donnees']]])
        ->assertOk();
});

it('rejects a malformed slug and unknown languages', function () {
    actingAsAdmin();
    $payload = categoryPayload('Pas Un Slug');
    $payload['translations']['de'] = ['name' => 'Oberfläche', 'slug' => 'oberflache'];

    $this->postJson('/api/v1/admin/technology-categories', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['translations.fr.slug', 'translations']);
});

it('paginates, filters and sorts the list', function () {
    actingAsAdmin();
    TechnologyCategory::factory()->count(3)->sequence(['position' => 3], ['position' => 1], ['position' => 2])->create();
    TechnologyCategory::factory()->inactive()->create(['position' => 0]);

    $this->getJson('/api/v1/admin/technology-categories?filter[is_active]=1&sort=-position&per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.position', 3)
        ->assertJsonPath('meta.total', 3);

    $this->getJson('/api/v1/admin/technology-categories?sort=password')->assertBadRequest();
});

it('answers 404 for an unknown content', function () {
    actingAsAdmin();

    $this->getJson('/api/v1/admin/technology-categories/999999')->assertNotFound();
});

it('refuses to delete a content still in use, with 409', function () {
    actingAsAdmin();
    $technology = Technology::factory()->create();

    $this->deleteJson("/api/v1/admin/technology-categories/{$technology->technology_category_id}")
        ->assertConflict()
        ->assertJsonPath('message', 'Ce contenu est encore utilisé ailleurs et ne peut pas être supprimé.');
    expect(TechnologyCategory::count())->toBe(1);
});

it('deletes a content and logs it', function () {
    actingAsAdmin();
    $category = TechnologyCategory::factory()->create();

    $this->deleteJson("/api/v1/admin/technology-categories/{$category->id}")->assertNoContent();

    expect(TechnologyCategory::count())->toBe(0)
        ->and(ActivityLog::sole()->event)->toBe(ActivityEvent::Deleted);
});

it('creates a project with its technologies, skills and features', function () {
    actingAsAdmin();
    $technologies = Technology::factory()->count(2)->create();

    $response = $this->postJson('/api/v1/admin/projects', [
        'project_category_id' => ProjectCategory::factory()->create()->id,
        'kind' => 'personal',
        'development_stage' => 'completed',
        'technology_ids' => $technologies->pluck('id')->all(),
        'features' => [
            ['translations' => ['fr' => ['title' => 'Recherche plein texte']]],
            ['is_visible' => false, 'translations' => ['fr' => ['title' => 'Mode sombre'], 'en' => ['title' => 'Dark mode']]],
        ],
        'translations' => ['fr' => ['title' => 'WARISS.DEV', 'slug' => 'wariss-dev', 'summary' => 'Portfolio et CMS.', 'description' => 'Plateforme personnelle.', 'role' => 'Développeur']],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.technology_ids', $technologies->pluck('id')->all())
        ->assertJsonPath('data.features.1.position', 1)
        ->assertJsonPath('data.features.1.translations.en.title', 'Dark mode');
});

it('replaces the features entirely on update', function () {
    actingAsAdmin();
    $project = Project::factory()->hasFeatures(3)->create();

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['features' => [['translations' => ['fr' => ['title' => 'Seule']]]]])
        ->assertOk()
        ->assertJsonCount(1, 'data.features');
});

it('requires a future date to schedule a content', function () {
    actingAsAdmin();
    $project = Project::factory()->create();

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['status' => 'scheduled'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['published_at']);

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['status' => 'scheduled', 'published_at' => now()->subDay()->toIso8601String()])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['published_at']);
});

it('logs a publication as such', function () {
    actingAsAdmin();
    $project = Project::factory()->create();

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['status' => 'published'])->assertOk();

    expect($project->refresh())
        ->status->toBe(PublicationStatus::Published)
        ->published_at->not->toBeNull()
        ->and(ActivityLog::sole()->event)->toBe(ActivityEvent::Published);
});

it('redirects the old address when the slug of a published content changes (D-06)', function () {
    actingAsAdmin();
    $project = Project::factory()->published()->create(['fr' => ['title' => 'Ancien', 'slug' => 'ancien', 'summary' => 'Résumé', 'description' => 'Description', 'role' => 'Développeur']]);
    Redirect::create(['from_path' => '/fr/projects/tres-ancien', 'to_path' => '/fr/projects/ancien', 'status_code' => 301]);

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['translations' => ['fr' => ['slug' => 'nouveau']]])->assertOk();

    expect(Redirect::where('from_path', '/fr/projects/ancien')->sole()->to_path)->toBe('/fr/projects/nouveau')
        ->and(Redirect::where('from_path', '/fr/projects/tres-ancien')->sole()->to_path)->toBe('/fr/projects/nouveau');
});

it('does not redirect a draft whose slug changes', function () {
    actingAsAdmin();
    $project = Project::factory()->create();

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['translations' => ['fr' => ['slug' => 'brouillon-renomme']]])->assertOk();

    expect(Redirect::count())->toBe(0);
});

it('moves a content to the trash, restores it and deletes it for good', function () {
    actingAsAdmin();
    $project = Project::factory()->create();

    $this->deleteJson("/api/v1/admin/projects/{$project->id}")->assertNoContent();
    expect(Project::count())->toBe(0);
    $this->getJson('/api/v1/admin/projects?filter[trashed]=only')->assertJsonCount(1, 'data');

    $this->postJson("/api/v1/admin/projects/{$project->id}/restore")->assertOk()->assertJsonPath('data.deleted_at', null);
    expect(Project::count())->toBe(1);

    $this->deleteJson("/api/v1/admin/projects/{$project->id}/force")->assertNoContent();
    expect(Project::withTrashed()->count())->toBe(0);
});

it('rejects an unsafe url scheme', function () {
    actingAsAdmin();
    $project = Project::factory()->create();

    $this->patchJson("/api/v1/admin/projects/{$project->id}", ['live_url' => 'javascript:alert(1)'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['live_url']);
});

it('lists every administration resource', function (string $uri) {
    actingAsAdmin();

    $this->getJson("/api/v1/admin/{$uri}?per_page=5")->assertOk()->assertJsonStructure(['data', 'links', 'meta']);
})->with([
    'technology-categories', 'technologies', 'skill-categories', 'skills',
    'project-categories', 'projects', 'case-studies',
    'service-categories', 'services', 'experiment-categories', 'experiments',
    'post-categories', 'tags', 'posts',
    'experiences', 'educations', 'certifications', 'achievements', 'timeline-events',
    'testimonials', 'newsletter-campaigns',
    'pages', 'page-sections', 'navigation-menus', 'navigation-items', 'social-links',
    'media-collections', 'redirects', 'resumes',
]);
