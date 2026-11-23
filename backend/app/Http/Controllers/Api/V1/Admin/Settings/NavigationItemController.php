<?php

namespace App\Http\Controllers\Api\V1\Admin\Settings;

use App\Enums\NavigationItemType;
use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Settings\NavigationItemRequest;
use App\Models\Settings\NavigationItem;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Éléments de menu (D2).
 */
class NavigationItemController extends AdminCrudController
{
    protected function model(): string
    {
        return NavigationItem::class;
    }

    protected function formRequest(): string
    {
        return NavigationItemRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('navigation_menu_id'),
            AllowedFilter::exact('parent_id'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return 'position';
    }

    /**
     * Seule la destination correspondant au type est conservée (page, route ou lien externe).
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        $type = $model->getAttribute('type');
        $keep = match ($type instanceof NavigationItemType ? $type : NavigationItemType::tryFrom((string) $type)) {
            NavigationItemType::Page => 'page_id',
            NavigationItemType::Route => 'route_key',
            NavigationItemType::External => 'url',
            null => null,
        };

        foreach (['page_id', 'route_key', 'url'] as $field) {
            if ($field !== $keep) {
                $model->setAttribute($field, null);
            }
        }
    }
}
