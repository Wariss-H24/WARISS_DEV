<?php

namespace App\Http\Controllers\Api\V1\Admin\Catalog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Catalog\TechnologyRequest;
use App\Models\Catalog\Technology;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Technologies du catalogue central (D4).
 */
class TechnologyController extends AdminCrudController
{
    protected function model(): string
    {
        return Technology::class;
    }

    protected function formRequest(): string
    {
        return TechnologyRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('technology_category_id'),
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('is_featured'),
            AllowedFilter::partial('name'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'name', 'position', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'position';
    }
}
