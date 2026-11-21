<?php

namespace Database\Factories\Settings;

use App\Enums\NavigationItemType;
use App\Models\Pages\Page;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un lien vers une route du frontend.
 *
 * @extends Factory<NavigationItem>
 */
class NavigationItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'navigation_menu_id' => NavigationMenu::factory(),
            'type' => NavigationItemType::Route,
            'route_key' => 'projects',
            'opens_in_new_tab' => false,
            'is_visible' => true,
            'position' => 0,
            'fr' => ['label' => 'Projets'],
            'en' => ['label' => 'Projects'],
        ];
    }

    /**
     * Lien vers une page.
     */
    public function toPage(?Page $page = null): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => NavigationItemType::Page,
            'page_id' => $page->id ?? Page::factory(),
            'route_key' => null,
        ]);
    }

    /**
     * Lien vers une URL externe, ouvert dans un nouvel onglet.
     */
    public function external(string $url = 'https://github.com/'): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => NavigationItemType::External,
            'url' => $url,
            'route_key' => null,
            'opens_in_new_tab' => true,
        ]);
    }

    /**
     * Élément masqué.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_visible' => false]);
    }
}
