<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\NavigationItemType;
use App\Http\Requests\Admin\AdminFormRequest;
use App\Models\Settings\NavigationItem;
use Illuminate\Validation\Rule;

/**
 * Élément de menu : une seule destination selon son type (page, route du frontend ou lien externe),
 * parent pris dans le même menu (contrainte navigation_items_destination_check).
 */
class NavigationItemRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $type = $this->input('type', $this->existingItem()?->type?->value);

        return [
            'navigation_menu_id' => $this->isCreating() ? ['required', 'integer', Rule::exists('navigation_menus', 'id')] : ['prohibited'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('navigation_items', 'id')->where('navigation_menu_id', $this->menuId()),
                Rule::notIn(array_filter([$this->routeId()])),
            ],
            'type' => [...$this->requiredOnCreate(), Rule::enum(NavigationItemType::class)],
            'page_id' => ['nullable', 'integer', Rule::exists('pages', 'id'), Rule::requiredIf($type === 'page' && $this->has('type'))],
            'route_key' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9]+(?:[-_.][a-z0-9]+)*$/', Rule::requiredIf($type === 'route' && $this->has('type'))],
            'url' => [...$this->safeLink(), 'regex:#^(https?://|mailto:)#i', Rule::requiredIf($type === 'external' && $this->has('type'))],
            'icon' => ['nullable', 'string', 'max:100'],
            'opens_in_new_tab' => ['sometimes', 'boolean'],
            ...$this->displayRules(featurable: false),
            ...$this->translationRules(['label' => ['string', 'max:100']], ['label']),
        ];
    }

    private function existingItem(): ?NavigationItem
    {
        $id = $this->routeId();

        return $id === null ? null : NavigationItem::find($id);
    }

    private function menuId(): int
    {
        return (int) ($this->input('navigation_menu_id') ?? $this->existingItem()?->navigation_menu_id);
    }
}
