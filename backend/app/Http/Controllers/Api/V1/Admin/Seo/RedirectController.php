<?php

namespace App\Http\Controllers\Api\V1\Admin\Seo;

use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Seo\RedirectRequest;
use App\Models\Seo\Redirect;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Redirections (D16).
 */
class RedirectController extends AdminCrudController
{
    protected function model(): string
    {
        return Redirect::class;
    }

    protected function formRequest(): string
    {
        return RedirectRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('is_active'),
            AllowedFilter::partial('from_path'),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'from_path', 'hits', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return '-id';
    }

    /**
     * Le créateur est le compte connecté.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if ($isNew) {
            $model->setAttribute('created_by', request()->user()?->getKey());
        }
    }
}
