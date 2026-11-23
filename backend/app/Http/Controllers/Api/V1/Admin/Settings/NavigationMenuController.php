<?php

namespace App\Http\Controllers\Api\V1\Admin\Settings;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Settings\NavigationMenuRequest;
use App\Models\Settings\NavigationMenu;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Menus du site (D2).
 */
class NavigationMenuController extends AdminCrudController
{
    protected function model(): string
    {
        return NavigationMenu::class;
    }

    protected function formRequest(): string
    {
        return NavigationMenuRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('location'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'name'];
    }

    protected function defaultSort(): string
    {
        return 'name';
    }
}
