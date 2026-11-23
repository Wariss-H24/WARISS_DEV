<?php

namespace App\Http\Controllers\Api\V1\Admin\Portfolio;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Portfolio\ProjectRequest;
use App\Models\Portfolio\Project;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Projets du portfolio (D6), avec leurs fonctionnalités, technologies et compétences.
 */
class ProjectController extends AdminCrudController
{
    protected function model(): string
    {
        return Project::class;
    }

    protected function formRequest(): string
    {
        return ProjectRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies', 'skill_ids' => 'skills'];
    }

    protected function children(): array
    {
        return ['features' => 'features'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('project_category_id'),
            AllowedFilter::exact('kind'),
            AllowedFilter::exact('is_featured'),
            AllowedFilter::trashed(),
            AllowedFilter::scope('search'),
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
