<?php

namespace App\Http\Controllers\Api\V1\Admin\Catalog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Catalog\CategoryRequest;
use App\Models\Catalog\TechnologyCategory;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Catégories de technologies (D4).
 */
class TechnologyCategoryController extends AdminCrudController
{
    protected function model(): string
    {
        return TechnologyCategory::class;
    }

    protected function formRequest(): string
    {
        return CategoryRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_active'),
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
}
