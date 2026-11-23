<?php

namespace App\Http\Controllers\Api\V1\Admin\Blog;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Blog\TagRequest;
use App\Models\Blog\Tag;

/**
 * Tags du blog (D12).
 */
class TagController extends AdminCrudController
{
    protected function model(): string
    {
        return Tag::class;
    }

    protected function formRequest(): string
    {
        return TagRequest::class;
    }

    protected function sorts(): array
    {
        return ['id', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }
}
