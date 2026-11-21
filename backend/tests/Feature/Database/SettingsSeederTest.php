<?php

use App\Enums\NavigationItemType;
use App\Enums\NavigationLocation;
use App\Models\Settings\NavigationMenu;
use App\Models\Settings\SiteSetting;
use Database\Seeders\PageSeeder;
use Database\Seeders\SettingsSeeder;

beforeEach(function () {
    $this->seed(PageSeeder::class);
});

it('creates the site settings with only the site name filled in', function () {
    $this->seed(SettingsSeeder::class);

    $filled = SiteSetting::whereNotNull('value')->get()->mapWithKeys(fn (SiteSetting $setting) => ["{$setting->group}.{$setting->key}" => $setting->value]);

    expect(SiteSetting::count())->toBe(10)
        ->and($filled->all())->toBe([
            'general.site_name' => 'WARISS.DEV',
            'seo.title_suffix' => 'WARISS.DEV',
            'maintenance.enabled' => false,
        ])
        ->and(SiteSetting::where('is_translatable', true)->pluck('key')->sort()->values()->all())
        ->toBe(['description', 'tagline', 'text']);
    $this->assertDatabaseCount('site_setting_translations', 0);
});

it('creates the main, footer and legal menus pointing to the system pages', function () {
    $this->seed(SettingsSeeder::class);

    $main = NavigationMenu::where('location', NavigationLocation::Header)->sole();
    $items = $main->rootItems()->with(['page', 'translations'])->get();

    expect(NavigationMenu::count())->toBe(3)
        ->and($items->map(fn ($item) => $item->page->key)->all())
        ->toBe(['home', 'about', 'skills', 'experience', 'projects', 'services', 'lab', 'blog', 'achievements', 'contact'])
        ->and($items->every(fn ($item) => $item->type === NavigationItemType::Page))->toBeTrue()
        ->and($items->firstWhere('page.key', 'about')->translate('fr')->label)->toBe('À propos')
        ->and($items->firstWhere('page.key', 'about')->translate('en')->label)->toBe('About')
        ->and(NavigationMenu::where('slug', 'legal')->sole()->items()->count())->toBe(2);
});

it('does not duplicate nor overwrite settings and menus when it runs again', function () {
    $this->seed(SettingsSeeder::class);
    SiteSetting::where('key', 'site_name')->update(['value' => json_encode('Mon site')]);

    $this->seed(SettingsSeeder::class);

    expect(SiteSetting::count())->toBe(10)
        ->and(SiteSetting::where('key', 'site_name')->sole()->value)->toBe('Mon site')
        ->and(NavigationMenu::count())->toBe(3);
    $this->assertDatabaseCount('navigation_items', 16);
});
