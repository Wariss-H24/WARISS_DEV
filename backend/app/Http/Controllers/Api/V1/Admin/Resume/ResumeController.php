<?php

namespace App\Http\Controllers\Api\V1\Admin\Resume;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Resume\ResumeRequest;
use App\Models\Resume\Resume;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * CV (D15). Les fichiers sont ajoutés par l'envoi de versions.
 */
class ResumeController extends AdminCrudController
{
    protected function model(): string
    {
        return Resume::class;
    }

    protected function formRequest(): string
    {
        return ResumeRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('locale'),
            AllowedFilter::exact('status'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'title', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * @return list<string>
     */
    protected function detailRelations(): array
    {
        return ['versions.media', 'currentVersion'];
    }
}
