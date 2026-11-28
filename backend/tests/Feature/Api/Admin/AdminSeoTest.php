<?php

use App\Models\Blog\Post;
use App\Models\Portfolio\Project;
use App\Models\Seo\SeoMetadata;
use App\Models\User;

/*
| Fiche SEO de chaque contenu public (D16).
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    $this->actingAs(User::factory()->admin()->create());
});

it('returns no SEO data until it is saved, then creates and updates a single record', function () {
    $project = Project::factory()->create();

    $this->getJson("/api/v1/admin/seo/project/{$project->id}")->assertOk()->assertJsonPath('data', null);

    $this->putJson("/api/v1/admin/seo/project/{$project->id}", [
        'robots_index' => false,
        'schema_type' => 'CreativeWork',
        'structured_data' => ['@context' => 'https://schema.org', 'name' => 'Projet'],
        'translations' => ['fr' => ['meta_title' => 'Projet phare', 'meta_description' => 'Description courte.']],
    ])->assertCreated()
        ->assertJsonPath('data.seoable_type', 'project')
        ->assertJsonPath('data.seoable_id', $project->id)
        ->assertJsonPath('data.robots_index', false)
        ->assertJsonPath('data.structured_data.name', 'Projet')
        ->assertJsonPath('data.translations.fr.meta_title', 'Projet phare');

    $this->putJson("/api/v1/admin/seo/project/{$project->id}", ['translations' => ['en' => ['meta_title' => 'Flagship project']]])
        ->assertOk()
        ->assertJsonPath('data.robots_index', false)
        ->assertJsonPath('data.translations.en.meta_title', 'Flagship project');

    expect(SeoMetadata::count())->toBe(1);
});

it('validates the SEO fields', function (array $payload, string $field) {
    $post = Post::factory()->create();

    $this->putJson("/api/v1/admin/seo/post/{$post->id}", $payload)->assertJsonValidationErrors([$field]);
})->with([
    'title too long' => [['translations' => ['fr' => ['meta_title' => str_repeat('a', 71)]]], 'translations.fr.meta_title'],
    'unsafe canonical' => [['canonical_url' => 'javascript:alert(1)'], 'canonical_url'],
    'unknown schema type' => [['schema_type' => 'Weapon'], 'schema_type'],
    'structured data list' => [['structured_data' => ['a', 'b']], 'structured_data'],
]);

it('answers 404 for a content type without SEO or an unknown content', function () {
    $this->getJson('/api/v1/admin/seo/tag/1')->assertNotFound();
    $this->getJson('/api/v1/admin/seo/project/999999')->assertNotFound();
});

it('keeps the SEO record of a trashed content', function () {
    $project = Project::factory()->create();
    SeoMetadata::factory()->for($project, 'seoable')->create();
    $project->delete();

    $this->getJson("/api/v1/admin/seo/project/{$project->id}")->assertOk()->assertJsonPath('data.seoable_id', $project->id);
});

it('lets the viewer read SEO data but not change it', function () {
    $project = Project::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->getJson("/api/v1/admin/seo/project/{$project->id}")->assertOk();
    $this->putJson("/api/v1/admin/seo/project/{$project->id}", ['robots_index' => false])->assertForbidden();
});
