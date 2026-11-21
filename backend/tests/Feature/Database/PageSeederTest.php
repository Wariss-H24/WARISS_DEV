<?php

use App\Enums\PageKind;
use App\Enums\PageSectionType;
use App\Enums\PublicationStatus;
use App\Models\Pages\Page;
use Database\Seeders\PageSeeder;

it('creates every system page of the specification as an unpublished draft without slug', function () {
    $this->seed(PageSeeder::class);

    $pages = Page::with('translations')->get();

    expect($pages)->toHaveCount(17)
        ->and($pages->pluck('key')->all())->toContain('home', 'contact', 'now', 'uses', 'legal-notice', 'privacy')
        ->and($pages->every(fn (Page $page) => $page->kind === PageKind::System))->toBeTrue()
        ->and($pages->every(fn (Page $page) => $page->status === PublicationStatus::Draft))->toBeTrue()
        ->and($pages->flatMap->translations->whereNotNull('slug'))->toBeEmpty()
        ->and(Page::published()->count())->toBe(0);
});

it('translates the page titles in French and English', function () {
    $this->seed(PageSeeder::class);

    $about = Page::where('key', 'about')->sole();

    expect($about->translate('fr')->title)->toBe('À propos')
        ->and($about->translate('en')->title)->toBe('About');
});

it('creates the home sections in the order of the specification, without invented text', function () {
    $this->seed(PageSeeder::class);

    $sections = Page::where('key', 'home')->sole()->sections()->ordered()->get();

    expect($sections->pluck('type')->all())->toBe([
        PageSectionType::Hero, PageSectionType::About, PageSectionType::Skills,
        PageSectionType::FeaturedProjects, PageSectionType::Experiences, PageSectionType::Services,
        PageSectionType::LatestPosts, PageSectionType::Cta, PageSectionType::Contact,
    ])
        ->and($sections->firstWhere('type', PageSectionType::LatestPosts)->is_active)->toBeFalse()
        ->and($sections->where('is_active', true))->toHaveCount(8);
    $this->assertDatabaseCount('page_section_translations', 0);
});

it('does not duplicate pages nor sections when it runs again', function () {
    $this->seed(PageSeeder::class);

    $this->seed(PageSeeder::class);

    $this->assertDatabaseCount('pages', 17);
    $this->assertDatabaseCount('page_sections', 9);
});
