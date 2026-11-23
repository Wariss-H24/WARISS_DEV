<?php

namespace App\Http\Controllers\Api\V1\Admin\Lab;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Lab\ExperimentRequest;
use App\Models\Lab\Experiment;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Expérimentations du laboratoire (D11).
 */
class ExperimentController extends AdminCrudController
{
    protected function model(): string
    {
        return Experiment::class;
    }

    protected function formRequest(): string
    {
        return ExperimentRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('experiment_category_id'),
            AllowedFilter::exact('kind'),
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
