<?php

namespace App\Http\Controllers\Api\V1\Admin\Services;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Services\ServiceRequest;
use App\Models\Services\Service;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Services professionnels (D3), avec leurs caractéristiques.
 */
class ServiceController extends AdminCrudController
{
    protected function model(): string
    {
        return Service::class;
    }

    protected function formRequest(): string
    {
        return ServiceRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function children(): array
    {
        return ['features' => 'features'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('service_category_id'),
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
