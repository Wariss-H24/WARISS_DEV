<?php

namespace Database\Seeders;

use App\Enums\NavigationItemType;
use App\Enums\NavigationLocation;
use App\Enums\SettingType;
use App\Models\Pages\Page;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use App\Models\Settings\SiteSetting;
use Illuminate\Database\Seeder;

/**
 * Réglages du site (cahier des charges §5.2) et menus (§24), sans contenu inventé :
 * seul le nom du site a une valeur ; slogan, description, logo, e-mail public… restent vides.
 *
 * Les menus pointent vers les pages système (D-15) : PageSeeder doit être exécuté avant.
 * Idempotent : un réglage ou un menu existant n'est jamais modifié.
 */
class SettingsSeeder extends Seeder
{
    /**
     * @var list<array{group: string, key: string, type: SettingType, value: mixed, translatable: bool}>
     */
    private const SETTINGS = [
        ['group' => 'general', 'key' => 'site_name', 'type' => SettingType::String, 'value' => 'WARISS.DEV', 'translatable' => false],
        ['group' => 'general', 'key' => 'tagline', 'type' => SettingType::Text, 'value' => null, 'translatable' => true],
        ['group' => 'general', 'key' => 'description', 'type' => SettingType::Text, 'value' => null, 'translatable' => true],
        ['group' => 'branding', 'key' => 'logo', 'type' => SettingType::Media, 'value' => null, 'translatable' => false],
        ['group' => 'branding', 'key' => 'favicon', 'type' => SettingType::Media, 'value' => null, 'translatable' => false],
        ['group' => 'contact', 'key' => 'public_email', 'type' => SettingType::String, 'value' => null, 'translatable' => false],
        ['group' => 'footer', 'key' => 'text', 'type' => SettingType::Text, 'value' => null, 'translatable' => true],
        ['group' => 'seo', 'key' => 'title_suffix', 'type' => SettingType::String, 'value' => 'WARISS.DEV', 'translatable' => false],
        ['group' => 'seo', 'key' => 'default_og_image', 'type' => SettingType::Media, 'value' => null, 'translatable' => false],
        ['group' => 'maintenance', 'key' => 'enabled', 'type' => SettingType::Bool, 'value' => false, 'translatable' => false],
    ];

    /**
     * Menus et clés des pages système qu'ils contiennent, dans l'ordre d'affichage.
     *
     * @var array<string, array{name: string, location: NavigationLocation, pages: list<string>}>
     */
    private const MENUS = [
        'main' => [
            'name' => 'Menu principal',
            'location' => NavigationLocation::Header,
            'pages' => ['home', 'about', 'skills', 'experience', 'projects', 'services', 'lab', 'blog', 'achievements', 'contact'],
        ],
        'footer' => [
            'name' => 'Pied de page',
            'location' => NavigationLocation::Footer,
            'pages' => ['projects', 'services', 'resume', 'contact'],
        ],
        'legal' => [
            'name' => 'Informations légales',
            'location' => NavigationLocation::Legal,
            'pages' => ['legal-notice', 'privacy'],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedSettings();
        $this->seedMenus();
    }

    private function seedSettings(): void
    {
        foreach (self::SETTINGS as $setting) {
            SiteSetting::firstOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                [
                    'type' => $setting['type'],
                    'value' => $setting['value'],
                    'is_translatable' => $setting['translatable'],
                    'is_public' => true,
                ],
            );
        }
    }

    private function seedMenus(): void
    {
        foreach (self::MENUS as $slug => $definition) {
            if (NavigationMenu::where('slug', $slug)->exists()) {
                continue;
            }

            $menu = NavigationMenu::create([
                'name' => $definition['name'],
                'slug' => $slug,
                'location' => $definition['location'],
                'is_active' => true,
            ]);

            foreach ($definition['pages'] as $position => $pageKey) {
                $this->seedPageItem($menu, $pageKey, $position);
            }
        }
    }

    private function seedPageItem(NavigationMenu $menu, string $pageKey, int $position): void
    {
        $page = Page::with('translations')->where('key', $pageKey)->first();

        if ($page === null) {
            $this->command?->warn("Page « {$pageKey} » absente : lancez PageSeeder avant SettingsSeeder.");

            return;
        }

        NavigationItem::create([
            'navigation_menu_id' => $menu->id,
            'type' => NavigationItemType::Page,
            'page_id' => $page->id,
            'position' => $position,
            'is_visible' => true,
            'fr' => ['label' => $page->translations->firstWhere('locale', 'fr')->title ?? $pageKey],
            'en' => ['label' => $page->translations->firstWhere('locale', 'en')->title ?? $pageKey],
        ]);
    }
}
