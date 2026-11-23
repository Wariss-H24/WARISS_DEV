<?php

use App\Enums\PageKind;
use App\Enums\TestimonialStatus;
use App\Models\Blog\PostCategory;
use App\Models\Career\Certification;
use App\Models\Career\Experience;
use App\Models\Communication\Testimonial;
use App\Models\Identity\Profile;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use App\Models\Portfolio\Project;
use App\Models\Resume\Resume;
use App\Models\Resume\ResumeVersion;
use App\Models\Services\Service;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use App\Models\User;

/*
| Règles propres à chaque domaine de l'administration, en plus du socle CRUD.
*/

beforeEach(function () {
    $this->withHeader('Referer', 'http://localhost:5174');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

it('sets the author to the signed-in account and estimates the reading time', function () {
    $response = $this->postJson('/api/v1/admin/posts', [
        'post_category_id' => PostCategory::factory()->create()->id,
        'author_id' => User::factory()->create()->id,
        'translations' => ['fr' => [
            'title' => 'Mesurer sans cookie',
            'slug' => 'mesurer-sans-cookie',
            'excerpt' => 'Résumé.',
            'content' => str_repeat('mot ', 450),
        ]],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.author_id', $this->admin->id)
        ->assertJsonPath('data.reading_time_minutes', 3);
});

it('approves a testimonial only with the consent of its author', function () {
    $testimonial = Testimonial::factory()->create();

    $this->patchJson("/api/v1/admin/testimonials/{$testimonial->id}", ['status' => 'approved'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['consent_given']);

    $this->patchJson("/api/v1/admin/testimonials/{$testimonial->id}", ['status' => 'approved', 'consent_given' => true])
        ->assertOk()
        ->assertJsonPath('data.status', 'approved');
    expect($testimonial->refresh()->status)->toBe(TestimonialStatus::Approved);
});

it('refuses to withdraw the consent of an approved testimonial', function () {
    $testimonial = Testimonial::factory()->approved()->create();

    $this->patchJson("/api/v1/admin/testimonials/{$testimonial->id}", ['consent_given' => false])
        ->assertJsonValidationErrors(['consent_given']);
});

it('falls back to a clean 422 when only the database can see the inconsistency', function () {
    $service = Service::factory()->priced()->create();

    $this->patchJson("/api/v1/admin/services/{$service->id}", ['pricing_model' => 'quote'])
        ->assertUnprocessable()
        ->assertJsonPath('message', 'Les données envoyées ne respectent pas les règles de ce contenu.');
    expect($service->refresh()->price_from)->not->toBeNull();
});

it('creates only custom pages and never deletes a system page', function () {
    $this->postJson('/api/v1/admin/pages', [
        'key' => 'mentions-legales',
        'kind' => 'system',
        'translations' => ['fr' => ['title' => 'Mentions légales', 'slug' => 'mentions-legales']],
    ])->assertCreated()->assertJsonPath('data.kind', PageKind::Custom->value);

    $home = Page::factory()->system('home')->create();

    $this->deleteJson("/api/v1/admin/pages/{$home->id}")->assertConflict();
    $this->patchJson("/api/v1/admin/pages/{$home->id}", ['key' => 'accueil'])->assertJsonValidationErrors(['key']);
    $this->patchJson("/api/v1/admin/pages/{$home->id}", ['translations' => ['fr' => ['slug' => 'accueil']]])
        ->assertJsonValidationErrors(['translations.fr.slug']);
    expect($home->refresh()->exists)->toBeTrue();
});

it('keeps only the destination matching the type of a menu item', function () {
    $item = NavigationItem::factory()->create();
    $page = Page::factory()->create();

    $this->patchJson("/api/v1/admin/navigation-items/{$item->id}", ['type' => 'page'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['page_id']);

    $this->patchJson("/api/v1/admin/navigation-items/{$item->id}", ['type' => 'page', 'page_id' => $page->id])
        ->assertOk()
        ->assertJsonPath('data.page_id', $page->id)
        ->assertJsonPath('data.route_key', null);
});

it('takes the parent of a menu item from the same menu', function () {
    $item = NavigationItem::factory()->create();
    $otherMenuItem = NavigationItem::factory()->for(NavigationMenu::factory(), 'menu')->create();

    $this->patchJson("/api/v1/admin/navigation-items/{$item->id}", ['parent_id' => $otherMenuItem->id])
        ->assertJsonValidationErrors(['parent_id']);
    $this->patchJson("/api/v1/admin/navigation-items/{$item->id}", ['parent_id' => $item->id])
        ->assertJsonValidationErrors(['parent_id']);
});

it('links a timeline event only to an existing source of the same type', function () {
    $certification = Certification::factory()->create();
    $payload = ['event_date' => '2025-05-01', 'translations' => ['fr' => ['title' => 'Certifié']]];

    $this->postJson('/api/v1/admin/timeline-events', $payload + ['type' => 'project', 'eventable_type' => 'certification', 'eventable_id' => $certification->id])
        ->assertJsonValidationErrors(['eventable_type']);
    $this->postJson('/api/v1/admin/timeline-events', $payload + ['type' => 'certification', 'eventable_type' => 'certification', 'eventable_id' => 999999])
        ->assertJsonValidationErrors(['eventable_id']);
    $this->postJson('/api/v1/admin/timeline-events', $payload + ['type' => 'certification', 'eventable_type' => 'certification', 'eventable_id' => $certification->id])
        ->assertCreated();
});

it('clears the end date of an experience marked as current', function () {
    $experience = Experience::factory()->create();

    $this->patchJson("/api/v1/admin/experiences/{$experience->id}", ['is_current' => true])
        ->assertOk()
        ->assertJsonPath('data.ended_on', null);

    $this->patchJson("/api/v1/admin/experiences/{$experience->id}", ['ended_on' => '2026-01-01'])
        ->assertUnprocessable();
});

it('features existing contents in a page section, without duplicates', function () {
    $section = PageSection::factory()->create();
    $project = Project::factory()->create();

    $this->patchJson("/api/v1/admin/page-sections/{$section->id}", ['items' => [
        ['itemable_type' => 'project', 'itemable_id' => $project->id],
        ['itemable_type' => 'project', 'itemable_id' => $project->id],
    ]])->assertJsonValidationErrors(['items.0.itemable_id']);

    $this->patchJson("/api/v1/admin/page-sections/{$section->id}", ['items' => [['itemable_type' => 'page', 'itemable_id' => $section->page_id]]])
        ->assertJsonValidationErrors(['items.0.itemable_type']);

    $this->patchJson("/api/v1/admin/page-sections/{$section->id}", ['items' => [['itemable_type' => 'project', 'itemable_id' => $project->id]]])
        ->assertOk()
        ->assertJsonPath('data.items.0.itemable_type', 'project')
        ->assertJsonPath('data.items.0.position', 0);
});

it('attaches a social link to the owner profile', function () {
    $this->postJson('/api/v1/admin/social-links', ['platform' => 'github', 'url' => 'https://github.com/'])
        ->assertUnprocessable();

    $profile = Profile::factory()->create();

    $this->postJson('/api/v1/admin/social-links', ['platform' => 'github', 'url' => 'https://github.com/'])
        ->assertCreated()
        ->assertJsonPath('data.profile_id', $profile->id);
});

it('validates manual redirects', function (array $payload, string $field) {
    $this->postJson('/api/v1/admin/redirects', $payload)->assertJsonValidationErrors([$field]);
})->with([
    'external source' => [['from_path' => 'https://evil.test/', 'to_path' => '/fr'], 'from_path'],
    'protocol-relative target' => [['from_path' => '/ancien', 'to_path' => '//evil.test'], 'to_path'],
    'loop' => [['from_path' => '/ancien', 'to_path' => '/ancien'], 'to_path'],
    'unknown status' => [['from_path' => '/ancien', 'to_path' => '/fr', 'status_code' => 300], 'status_code'],
]);

it('records the creator of a redirect', function () {
    $this->postJson('/api/v1/admin/redirects', ['from_path' => '/ancien', 'to_path' => '/fr'])
        ->assertCreated()
        ->assertJsonPath('data.created_by', $this->admin->id)
        ->assertJsonPath('data.status_code', 301);
});

it('picks the current version of a resume among its own versions only', function () {
    $resume = Resume::factory()->create();
    $ownVersion = ResumeVersion::factory()->for($resume)->create();
    $otherVersion = ResumeVersion::factory()->create();

    $this->patchJson("/api/v1/admin/resumes/{$resume->id}", ['current_version_id' => $otherVersion->id])
        ->assertJsonValidationErrors(['current_version_id']);
    $this->patchJson("/api/v1/admin/resumes/{$resume->id}", ['current_version_id' => $ownVersion->id])
        ->assertOk()
        ->assertJsonPath('data.current_version_id', $ownVersion->id);
});
