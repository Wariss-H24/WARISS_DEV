<?php

namespace App\Http\Controllers\Api\V1\Admin\Career;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Career\CertificationRequest;
use App\Models\Career\Certification;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Certifications (D9).
 */
class CertificationController extends AdminCrudController
{
    protected function model(): string
    {
        return Certification::class;
    }

    protected function formRequest(): string
    {
        return CertificationRequest::class;
    }

    protected function relations(): array
    {
        return ['technology_ids' => 'technologies'];
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_visible'),
            AllowedFilter::exact('is_featured'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'position', 'issued_on'];
    }

    protected function defaultSort(): string
    {
        return '-issued_on';
    }
}
