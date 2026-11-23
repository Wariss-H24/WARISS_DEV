<?php

namespace App\Http\Requests\Admin\Settings;

use App\Enums\NavigationLocation;
use App\Http\Requests\Admin\AdminFormRequest;
use Illuminate\Validation\Rule;

class NavigationMenuRequest extends AdminFormRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => [...$this->requiredOnCreate(), 'string', 'max:100'],
            'slug' => [...$this->requiredOnCreate(), 'string', 'max:100', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('navigation_menus', 'slug')->ignore($this->routeId())],
            'location' => ['nullable', Rule::enum(NavigationLocation::class), Rule::unique('navigation_menus', 'location')->ignore($this->routeId())],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
