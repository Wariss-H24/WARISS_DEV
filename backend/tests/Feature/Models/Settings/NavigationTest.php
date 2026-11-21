<?php

use App\Enums\NavigationItemType;
use App\Enums\NavigationLocation;
use App\Models\Pages\Page;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('builds a menu with ordered root items and nested children', function () {
    $menu = NavigationMenu::factory()->create(['location' => NavigationLocation::Header]);
    $second = NavigationItem::factory()->for($menu, 'menu')->create(['position' => 2]);
    $first = NavigationItem::factory()->for($menu, 'menu')->toPage()->create(['position' => 1]);
    $child = NavigationItem::factory()->for($menu, 'menu')->external()->create(['parent_id' => $first->id]);

    expect($menu->rootItems->pluck('id')->all())->toBe([$first->id, $second->id])
        ->and($first->children->pluck('id')->all())->toBe([$child->id])
        ->and($first->page)->toBeInstanceOf(Page::class)
        ->and($child->type)->toBe(NavigationItemType::External);
});

it('translates the item labels', function () {
    $item = NavigationItem::factory()->create(['fr' => ['label' => 'Projets'], 'en' => ['label' => 'Projects']]);
    $item = NavigationItem::with('translations')->find($item->id);

    app()->setLocale('en');

    expect($item->label)->toBe('Projects');
});

it('rejects an item whose destination does not match its type', function (array $attributes) {
    $page = Page::factory()->create();
    $attributes = array_map(fn ($value) => $value === 'PAGE_ID' ? $page->id : $value, $attributes);

    NavigationItem::factory()->create($attributes);
})->with([
    'page sans page_id' => [['type' => 'page', 'page_id' => null, 'route_key' => null]],
    'route sans route_key' => [['type' => 'route', 'route_key' => null]],
    'externe sans url' => [['type' => 'external', 'route_key' => null, 'url' => null]],
    'deux destinations' => [['type' => 'route', 'route_key' => 'blog', 'page_id' => 'PAGE_ID']],
])->throws(QueryException::class, 'navigation_items_destination_check');

it('rejects a dangerous external URL', function () {
    NavigationItem::factory()->external('javascript:alert(1)')->create();
})->throws(QueryException::class, 'navigation_items_url_check');

it('rejects an item that is its own parent', function () {
    $item = NavigationItem::factory()->create();

    DB::table('navigation_items')->where('id', $item->id)->update(['parent_id' => $item->id]);
})->throws(QueryException::class, 'navigation_items_not_own_parent_check');

it('rejects two menus at the same location', function () {
    NavigationMenu::factory()->create(['location' => NavigationLocation::Footer]);

    NavigationMenu::factory()->create(['location' => NavigationLocation::Footer]);
})->throws(QueryException::class, 'navigation_menus_location_unique');

it('deletes the items, their children and their labels with the menu', function () {
    $parent = NavigationItem::factory()->create();
    NavigationItem::factory()->for($parent->menu, 'menu')->create(['parent_id' => $parent->id]);

    $parent->menu->delete();

    $this->assertDatabaseCount('navigation_items', 0);
    $this->assertDatabaseCount('navigation_item_translations', 0);
});

it('removes the item when its page is permanently deleted, and keeps it when the page is soft deleted', function () {
    $item = NavigationItem::factory()->toPage()->create();

    $item->page->delete();
    expect(NavigationItem::find($item->id))->not->toBeNull();

    $item->page()->withTrashed()->first()->forceDelete();
    expect(NavigationItem::find($item->id))->toBeNull();
});
