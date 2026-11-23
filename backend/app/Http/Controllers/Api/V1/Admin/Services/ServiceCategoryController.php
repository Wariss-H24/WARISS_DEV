<?php

namespace App\Http\Controllers\Api\V1\Admin\Services;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Services\ServiceCategoryRequest;
use App\Models\Services\ServiceCategory;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Catégories de services (D3).
 */
class ServiceCategoryController extends AdminCrudController
{
    protected function model(): string
    {
        return ServiceCategory::class;
    }

    protected function formRequest(): string
    {
        return ServiceCategoryRequest::class;
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
