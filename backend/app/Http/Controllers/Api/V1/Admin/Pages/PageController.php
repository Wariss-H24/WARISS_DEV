<?php

namespace App\Http\Controllers\Api\V1\Admin\Pages;

use App\Enums\PageKind;
use App\Http\Controllers\Api\V1\Admin\AdminCrudController;
use App\Http\Requests\Admin\Pages\PageRequest;
use App\Models\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\AllowedFilter;

/**
 * Pages (D18). Seules des pages personnalisées peuvent être créées ; les pages système ne peuvent pas être supprimées.
 */
class PageController extends AdminCrudController
{
    protected function model(): string
    {
        return Page::class;
    }

    protected function formRequest(): string
    {
        return PageRequest::class;
    }

    protected function filters(): array
    {
        return [
            AllowedFilter::exact('kind'),
            AllowedFilter::exact('status'),
            AllowedFilter::trashed(),
        ];
    }

    protected function sorts(): array
    {
        return ['id', 'key', 'created_at'];
    }

    protected function defaultSort(): string
    {
        return 'key';
    }

    /**
     * Une page créée depuis l'administration est toujours personnalisée.
     *
     * @param  array<string, mixed>  $data
     */
    protected function beforeSave(Model $model, array $data, bool $isNew): void
    {
        if ($isNew) {
            $model->setAttribute('kind', PageKind::Custom);
        }
    }
}
