<?php

namespace App\Http\Controllers\Api\V1\Admin\Blog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Blog\PostCategoryRequest;
use App\Models\Blog\PostCategory;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Catégories du blog (D12).
 */
class PostCategoryController extends AdminCrudController
{
    protected function model(): string
    {
        return PostCategory::class;
    }

    protected function formRequest(): string
    {
        return PostCategoryRequest::class;
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
