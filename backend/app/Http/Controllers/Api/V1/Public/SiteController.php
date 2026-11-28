<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Enums\NavigationItemType;
use App\Enums\SettingType;
use App\Http\Resources\Public\PublicVisibility;
use App\Models\Identity\Profile;
use App\Models\Media\Media;
use App\Models\Settings\NavigationItem;
use App\Models\Settings\NavigationMenu;
use App\Models\Settings\SiteSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

/**
 * Données communes à toutes les pages du site public, chargées une fois par le site :
 * réglages publics, profil du propriétaire avec ses réseaux visibles, menus actifs.
 */
class SiteController extends PublicController
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => [
            'locale' => app()->getLocale(),
            'locales' => config('translatable.locales'),
            'settings' => $this->settings(),
            'profile' => $this->profile(),
            'menus' => $this->menus(),
        ]]);
    }

    /**
     * Réglages publics, par groupe puis par clé ; un réglage « media » renvoie le média public.
     *
     * @return array<string, array<string, mixed>>
     */
    private function settings(): array
    {
        $settings = SiteSetting::query()->exposedToPublic()->with('translations')->get();
        $mediaIds = $settings->where('type', SettingType::Media)->pluck('value')->filter(fn (mixed $id): bool => is_int($id))->all();
        $media = Media::query()->with('translations')->whereKey($mediaIds)->get()->keyBy('id');
        $grouped = [];

        foreach ($settings as $setting) {
            $value = $setting->resolvedValue();

            if ($setting->type === SettingType::Media) {
                $value = is_int($value) ? $this->presenter->media($media->get($value)) : null;
            }

            $grouped[$setting->group][$setting->key] = $value;
        }

        return $grouped;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function profile(): ?array
    {
        $profile = Profile::query()
            ->with(['translations', 'socialLinks' => fn ($query) => $query->where('is_visible', true)->orderBy('position')->orderBy('id')])
            ->oldest('id')
            ->first();

        return $profile === null ? null : $this->presenter->one($profile);
    }

    /**
     * Menus actifs, indexés par emplacement (header, footer…) ou, à défaut, par slug.
     *
     * @return array<string, array<string, mixed>>
     */
    private function menus(): array
    {
        $visibleItems = fn ($query) => $query->where('is_visible', true)->with(['translations', 'page.translations']);

        $menus = NavigationMenu::query()
            ->where('is_active', true)
            ->with(['rootItems' => $visibleItems, 'rootItems.children' => $visibleItems])
            ->get();

        return $menus->mapWithKeys(fn (NavigationMenu $menu): array => [
            (string) ($menu->location->value ?? $menu->slug) => [
                'name' => $menu->name,
                'slug' => $menu->slug,
                'items' => $this->items($menu->rootItems),
            ],
        ])->all();
    }

    /**
     * @param  Collection<int, NavigationItem>  $items
     * @return list<array<string, mixed>>
     */
    private function items(Collection $items): array
    {
        $result = [];

        foreach ($items as $item) {
            if ($item->type === NavigationItemType::Page && ! PublicVisibility::allows($item->page)) {
                continue;
            }

            $page = $item->page;
            $pageTranslation = $page?->translate(app()->getLocale(), true);

            $result[] = [
                'label' => $item->translate(app()->getLocale(), true)?->getAttribute('label'),
                'type' => $item->type->value,
                'route_key' => $item->route_key,
                'url' => $item->url,
                'page' => $page === null ? null : ['key' => $page->key, 'slug' => $pageTranslation?->getAttribute('slug')],
                'icon' => $item->icon,
                'opens_in_new_tab' => $item->opens_in_new_tab,
                'children' => $item->relationLoaded('children') ? $this->items($item->children) : [],
            ];
        }

        return $result;
    }
}
