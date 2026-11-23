<?php

namespace App\Http\Controllers\Api\V1\Admin\Portfolio;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Portfolio\CaseStudyRequest;
use App\Models\Portfolio\CaseStudy;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Études de cas (D7), avec leurs sections.
 */
class CaseStudyController extends AdminCrudController
{
    protected function model(): string
    {
        return CaseStudy::class;
    }

    protected function formRequest(): string
    {
        return CaseStudyRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function children(): array
    {
        return ['sections' => 'sections'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('project_id'),
            AllowedFilter::trashed(),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'published_at', 'created_at', 'updated_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }
}
