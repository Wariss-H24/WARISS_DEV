<?php

namespace App\Http\Controllers\Api\V1\Admin\Pages;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Pages\PageSectionRequest;
use App\Models\Pages\PageSection;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Sections de page (D18) et contenus mis en avant.
 */
class PageSectionController extends AdminCrudController
{
    protected function model(): string
    {
        return PageSection::class;
    }

    protected function formRequest(): string
    {
        return PageSectionRequest::class;
    }

    protected function children(): array
    {
        return ['items' => 'items'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('page_id'),
            AllowedFilter::exact('type'),
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

    /**
     * @return list<string>
     */
    protected function detailRelations(): array
    {
        return ['items'];
    }
}
