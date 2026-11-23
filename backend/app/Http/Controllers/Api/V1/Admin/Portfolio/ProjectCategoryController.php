<?php

namespace App\Http\Controllers\Api\V1\Admin\Portfolio;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Catalog\CategoryRequest;
use App\Models\Portfolio\ProjectCategory;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Catégories de projets (D6).
 */
class ProjectCategoryController extends AdminCrudController
{
    protected function model(): string
    {
        return ProjectCategory::class;
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
