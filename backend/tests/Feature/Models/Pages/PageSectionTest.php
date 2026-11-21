<?php

use App\Enums\PageSectionType;
use App\Models\Catalog\Skill;
use App\Models\Pages\Page;
use App\Models\Pages\PageSection;
use App\Models\Pages\PageSectionItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function featureInSection(PageSection $section, Skill $skill, int $position = 0): PageSectionItem
{
    return PageSectionItem::create([
        'page_section_id' => $section->id,
        'itemable_type' => $skill->getMorphClass(),
        'itemable_id' => $skill->id,
        'position' => $position,
    ]);
}

it('stores the section type, its presentation settings and its translated texts', function () {
    $section = PageSection::factory()->create([
        'type' => PageSectionType::Hero,
        'config' => ['variant' => 'split', 'items' => 3],
        'fr' => ['title' => 'Bienvenue', 'cta_label' => 'Me contacter'],
        'en' => ['title' => 'Welcome', 'cta_label' => 'Contact me'],
    ]);
    $section = PageSection::with('translations')->find($section->id);

    app()->setLocale('en');

    expect($section)
        ->type->toBe(PageSectionType::Hero)
        ->config->toEqual(['variant' => 'split', 'items' => 3])
        ->title->toBe('Welcome')
        ->cta_label->toBe('Contact me');
});

it('lists the active sections of a page in their display order', function () {
    $page = Page::factory()->create();
    $third = PageSection::factory()->for($page)->create(['position' => 3]);
    $first = PageSection::factory()->for($page)->create(['position' => 1]);
    PageSection::factory()->for($page)->inactive()->create(['position' => 2]);

    expect($page->sections()->active()->ordered()->pluck('id')->all())->toBe([$first->id, $third->id]);
});

it('references featured contents by morph alias without copying them, in the chosen order', function () {
    $section = PageSection::factory()->create(['type' => PageSectionType::Skills]);
    $second = Skill::factory()->create();
    $first = Skill::factory()->create();
    featureInSection($section, $second, 2);
    featureInSection($section, $first, 1);

    expect(DB::table('page_section_items')->distinct()->pluck('itemable_type')->all())->toBe(['skill'])
        ->and($section->items->map(fn (PageSectionItem $item) => $item->itemable->id)->all())
        ->toBe([$first->id, $second->id]);
});

it('rejects featuring the same content twice in a section', function () {
    $section = PageSection::factory()->create();
    $skill = Skill::factory()->create();
    featureInSection($section, $skill);

    featureInSection($section, $skill);
})->throws(QueryException::class, 'page_section_items_section_item_unique');

it('deletes the sections, their texts and their items when a page is permanently deleted', function () {
    $section = PageSection::factory()->create();
    featureInSection($section, Skill::factory()->create());

    $section->page->forceDelete();

    $this->assertDatabaseCount('page_sections', 0);
    $this->assertDatabaseCount('page_section_translations', 0);
    $this->assertDatabaseCount('page_section_items', 0);
});

it('rejects an unknown section type at the database level', function () {
    $section = PageSection::factory()->create();

    DB::table('page_sections')->where('id', $section->id)->update(['type' => 'carousel']);
})->throws(QueryException::class, 'page_sections_type_check');
