<?php

namespace App\Http\Controllers\Api\V1\Admin\Career;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Career\EducationRequest;
use App\Models\Career\Education;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Formations (D8).
 */
class EducationController extends AdminCrudController
{
    protected function model(): string
    {
        return Education::class;
    }

    protected function formRequest(): string
    {
        return EducationRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('completion_status'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'started_on', 'ended_on'];
    }

    protected function defaultSort(): string
    {
        return '-started_on';
    }
}
